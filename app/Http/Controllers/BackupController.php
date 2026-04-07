<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * List all stored backups
     */
    public function index()
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = array_filter(scandir($backupDir), function($file) use ($backupDir) {
            return is_file($backupDir . '/' . $file) && str_ends_with($file, '.zip');
        });

        $backups = array_map(function($file) use ($backupDir) {
            return [
                'name' => $file,
                'size' => filesize($backupDir . '/' . $file),
                'created_at' => filemtime($backupDir . '/' . $file),
            ];
        }, array_values($files));

        // Sort by newest first
        usort($backups, fn($a, $b) => $b['created_at'] - $a['created_at']);

        return response()->json([
            'success' => true,
            'data' => $backups
        ]);
    }

    /**
     * Download a specific backup file
     */
    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }
        return response()->download($path);
    }

    /**
     * Delete a specific backup file
     */
    public function destroy($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        if (file_exists($path)) {
            unlink($path);
            return response()->json(['success' => true, 'message' => 'Backup deleted']);
        }
        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }

    /**
     * Generate a new backup and store it
     */
    public function export(Request $request)
    {
        $includeDb = $request->query('db', 1);
        $includeMedia = $request->query('media', 1);

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $tag = ($includeDb && $includeMedia) ? 'full' : ($includeDb ? 'db' : ($includeMedia ? 'media' : 'partial'));
        $backupName = 'realtorone_' . $tag . '_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $tempDir = storage_path('app/temp_backup');
        $backupDir = storage_path('app/backups');
        
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);
        if (!file_exists($backupDir)) mkdir($backupDir, 0755, true);

        $sqlFile = $tempDir . '/database.sql';
        $zipFile = $backupDir . '/' . $backupName;

        try {
            set_time_limit(300); // 5 minutes for large assets
            if ($includeDb) {
                $command = sprintf(
                    'mysqldump --ssl=0 --no-tablespaces --host=%s --user=%s --password=%s %s > %s',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($sqlFile)
                );
                exec($command, $output, $returnVar);
                if ($returnVar !== 0) throw new \Exception('Database dump failed.');
            }

            $zipArgs = [];
            if ($includeMedia) $zipArgs[] = 'zip -r ' . escapeshellarg($zipFile) . ' public';
            if ($includeDb) $zipArgs[] = 'zip -j ' . escapeshellarg($zipFile) . ' ' . escapeshellarg($sqlFile);

            if (empty($zipArgs)) throw new \Exception('Nothing selected for backup.');

            $zipCommand = 'cd ' . escapeshellarg(storage_path('app')) . ' && ' . implode(' && ', $zipArgs);
            exec($zipCommand, $output, $returnVar);
            if ($returnVar !== 0) throw new \Exception('Zipping failed.');

            if (file_exists($sqlFile)) unlink($sqlFile);
            if (is_dir($tempDir)) rmdir($tempDir);

            // Return success with file info, frontend will initiate download if needed
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'data' => [
                    'name' => $backupName,
                    'size' => filesize($zipFile),
                    'created_at' => filemtime($zipFile)
                ]
            ]);

        } catch (\Exception $e) {
            if (file_exists($sqlFile)) unlink($sqlFile);
            if (is_dir($tempDir)) rmdir($tempDir);
            if (file_exists($zipFile)) unlink($zipFile);
            
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore from an uploaded backup file
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file'
        ]);

        $file = $request->file('backup_file');
        $tempDir = storage_path('app/temp_restore_' . time());
        mkdir($tempDir, 0755, true);

        try {
            // 1. Extract the ZIP
            $zip = new \ZipArchive;
            if ($zip->open($file->getRealPath()) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new \Exception('Could not extract ZIP file');
            }

            // 2. Find the SQL file
            $sqlFile = $tempDir . '/database.sql';
            if (!file_exists($sqlFile)) {
                throw new \Exception('No database.sql found in backup');
            }

            // 3. Import Database
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $command = sprintf(
                'mysql --ssl=0 --host=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            exec($command, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \Exception('Database restoration failed');
            }

            // 4. Copy public files back
            if (is_dir($tempDir . '/public')) {
                $this->recurseCopy($tempDir . '/public', storage_path('app/public'));
            }

            // 5. Clean up
            $this->deleteDirectory($tempDir);

            return response()->json([
                'success' => true,
                'message' => 'Restoration complete'
            ]);

        } catch (\Exception $e) {
            $this->deleteDirectory($tempDir);
            return response()->json([
                'success' => false,
                'message' => 'Restoration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recurseCopy($src, $dst)
    {
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}

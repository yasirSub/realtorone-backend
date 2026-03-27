<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Generate and download a full backup (Database + Course Assets)
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
        
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sqlFile = $tempDir . '/database.sql';
        $zipFile = storage_path('app/' . $backupName);

        try {
            // 1. Export Database if requested
            if ($includeDb) {
                $command = sprintf(
                    'mysqldump --ssl-mode=DISABLED --no-tablespaces --host=%s --user=%s --password=%s %s > %s',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($sqlFile)
                );
                
                exec($command, $output, $returnVar);
                if ($returnVar !== 0) {
                    throw new \Exception('Database dump failed. Make sure mysql-client is installed.');
                }
            }

            // 2. Create ZIP
            // Start with an empty zip command
            $zipArgs = [];
            if ($includeMedia) {
                // Add the public directory (media)
                $zipArgs[] = 'zip -r ' . escapeshellarg($zipFile) . ' public';
            }
            if ($includeDb) {
                // Add the SQL file
                $zipArgs[] = 'zip -j ' . escapeshellarg($zipFile) . ' ' . escapeshellarg($sqlFile);
            }

            if (empty($zipArgs)) {
                throw new \Exception('Nothing selected for backup.');
            }

            $zipCommand = 'cd ' . escapeshellarg(storage_path('app')) . ' && ' . implode(' && ', $zipArgs);

            exec($zipCommand, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \Exception('Zipping failed. Make sure zip is installed.');
            }

            // 3. Clean up SQL file and temp dir
            if (file_exists($sqlFile)) unlink($sqlFile);
            if (is_dir($tempDir)) rmdir($tempDir);

            // 4. Return the file for download
            return response()->download($zipFile)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            if (file_exists($sqlFile)) unlink($sqlFile);
            if (is_dir($tempDir)) rmdir($tempDir);
            if (file_exists($zipFile)) unlink($zipFile);
            
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
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
                'mysql --ssl-mode=DISABLED --host=%s --user=%s --password=%s %s < %s',
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

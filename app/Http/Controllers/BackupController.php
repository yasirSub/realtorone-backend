<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupController extends Controller
{
    private function mediaExtensionMap(): array
    {
        return [
            'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v'],
            'pdf' => ['pdf'],
        ];
    }

    private function addFilteredMediaToZip(ZipArchive $zip, string $storagePublicPath, array $mediaTypes): int
    {
        if (!is_dir($storagePublicPath)) {
            return 0;
        }

        $extMap = $this->mediaExtensionMap();
        $selectedExts = [];
        foreach ($mediaTypes as $type) {
            $key = strtolower(trim((string) $type));
            if (isset($extMap[$key])) {
                $selectedExts = array_merge($selectedExts, $extMap[$key]);
            }
        }
        $selectedExts = array_values(array_unique($selectedExts));

        $added = 0;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($storagePublicPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $ext = strtolower((string) $fileInfo->getExtension());
            if (!empty($selectedExts) && !in_array($ext, $selectedExts, true)) {
                continue;
            }

            $absolutePath = $fileInfo->getPathname();
            $relativePath = ltrim(str_replace($storagePublicPath, '', $absolutePath), DIRECTORY_SEPARATOR);
            $zipPath = 'public/' . str_replace('\\', '/', $relativePath);
            if ($zip->addFile($absolutePath, $zipPath)) {
                $added++;
            }
        }

        return $added;
    }

    private function backupDirectory(): string
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        return $backupDir;
    }

    private function isValidBackupFilename(string $filename): bool
    {
        return preg_match('/^[a-zA-Z0-9._-]+\.zip$/', $filename) === 1;
    }

    private function resolveBackupPath(string $filename): ?string
    {
        if (!$this->isValidBackupFilename($filename)) {
            return null;
        }
        $backupDir = $this->backupDirectory();
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;
        $dirReal = realpath($backupDir);
        $pathDirReal = realpath(dirname($path));
        if (!$dirReal || !$pathDirReal || $dirReal !== $pathDirReal) {
            return null;
        }
        return $path;
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return (int) $value === 1;
        $v = strtolower((string) $value);
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }

    private function listAllTables(string $database): array
    {
        $rows = DB::select('SHOW TABLES');
        $tables = [];
        foreach ($rows as $row) {
            $arr = (array) $row;
            if (!empty($arr)) {
                $tables[] = (string) array_values($arr)[0];
            }
        }
        return $tables;
    }

    private function getModuleCatalog(array $allTables): array
    {
        $catalog = [
            'courses' => [
                'label' => 'Courses',
                'patterns' => ['/^(courses|course_.*)$/'],
                'tables' => [],
            ],
            'subscriptions' => [
                'label' => 'Subscriptions',
                'patterns' => ['/^(subscription_packages|coupons)$/'],
                'tables' => [],
            ],
            'diagnosis' => [
                'label' => 'Diagnosis',
                'patterns' => ['/^diagnosis_/'],
                'tables' => [],
            ],
            'legal' => [
                'label' => 'Legal Documents',
                'patterns' => ['/^(legal_documents)$/'],
                'tables' => [],
            ],
            'activity_config' => [
                'label' => 'Activity Configuration',
                'patterns' => ['/^(activity_types)$/'],
                'tables' => [],
            ],
            'badges' => [
                'label' => 'Badges & Leaderboard',
                'patterns' => ['/^(badges|badge_.*|leaderboard_.*)$/'],
                'tables' => [],
            ],
        ];

        foreach ($allTables as $table) {
            foreach ($catalog as $key => $config) {
                foreach ($config['patterns'] as $pattern) {
                    if (preg_match($pattern, $table) === 1) {
                        $catalog[$key]['tables'][] = $table;
                        break 2;
                    }
                }
            }
        }

        foreach ($catalog as $key => $config) {
            $catalog[$key]['tables'] = array_values(array_unique($config['tables']));
        }

        return $catalog;
    }

    private function getUserDataTables(array $allTables): array
    {
        $userPatterns = [
            '/^users$/',
            '/^user_/',
            '/^activities$/',
            '/^activity_logs$/',
            '/^performance_metrics$/',
            '/^results$/',
            '/^results_/',
            '/^chat_/',
            '/^notifications$/',
            '/^notification_/',
            '/^rewards$/',
            '/^reward_/',
            '/^user_subscriptions$/',
            '/^subscriptions$/',
            '/^password_reset_tokens$/',
            '/^personal_access_tokens$/',
            '/^sessions$/',
        ];

        $userTables = [];
        foreach ($allTables as $table) {
            foreach ($userPatterns as $pattern) {
                if (preg_match($pattern, $table) === 1) {
                    $userTables[] = $table;
                    break;
                }
            }
        }
        return array_values(array_unique($userTables));
    }

    public function modules()
    {
        Log::info('Backup modules catalog requested');
        $database = config('database.connections.mysql.database');
        $allTables = $this->listAllTables($database);
        $catalog = $this->getModuleCatalog($allTables);

        $modules = [];
        foreach ($catalog as $key => $config) {
            $modules[] = [
                'key' => $key,
                'label' => $config['label'],
                'count' => count($config['tables']),
                'tables' => $config['tables'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * List all stored backups
     */
    public function index()
    {
        Log::info('Backup list requested');
        $backupDir = $this->backupDirectory();

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
        $path = $this->resolveBackupPath((string) $filename);
        if (!$path) {
            Log::warning('Backup download denied due to invalid filename', ['filename' => $filename]);
            return response()->json(['success' => false, 'message' => 'Invalid filename'], 422);
        }
        if (!file_exists($path)) {
            Log::warning('Backup download failed: file not found', ['filename' => $filename]);
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }
        Log::info('Backup download started', ['filename' => $filename]);
        return response()->download($path);
    }

    /**
     * Delete a specific backup file
     */
    public function destroy($filename)
    {
        $path = $this->resolveBackupPath((string) $filename);
        if (!$path) {
            Log::warning('Backup delete denied due to invalid filename', ['filename' => $filename]);
            return response()->json(['success' => false, 'message' => 'Invalid filename'], 422);
        }
        if (file_exists($path)) {
            unlink($path);
            Log::info('Backup deleted', ['filename' => $filename]);
            return response()->json(['success' => true, 'message' => 'Backup deleted']);
        }
        Log::warning('Backup delete failed: file not found', ['filename' => $filename]);
        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }

    /**
     * Generate a new backup and store it
     */
    public function export(Request $request)
    {
        Log::info('Backup export requested');
        $includeDb = $this->toBool($request->query('db', 1));
        $includeMedia = $this->toBool($request->query('media', 1));
        $includeModuleData = $this->toBool($request->query('module_data', 1));
        $includeUserData = $this->toBool($request->query('user_data', 1));
        $mediaTypes = $request->query('media_types', []);
        if (is_string($mediaTypes)) {
            $mediaTypes = array_filter(array_map('trim', explode(',', $mediaTypes)));
        }
        if (!is_array($mediaTypes)) {
            $mediaTypes = [];
        }
        if (empty($mediaTypes)) {
            $mediaTypes = ['video', 'pdf'];
        }
        $requestedModules = $request->query('modules', []);
        if (is_string($requestedModules)) {
            $requestedModules = array_filter(array_map('trim', explode(',', $requestedModules)));
        }
        if (!is_array($requestedModules)) {
            $requestedModules = [];
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $scopeTag = 'all';
        if ($includeDb) {
            if ($includeModuleData && ! $includeUserData) $scopeTag = 'module';
            if (! $includeModuleData && $includeUserData) $scopeTag = 'user';
        }
        $tag = ($includeDb && $includeMedia) ? "full_{$scopeTag}" : ($includeDb ? "db_{$scopeTag}" : ($includeMedia ? 'media' : 'partial'));
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
                $tableArgs = '';
                $selectedTables = [];

                if ($includeModuleData || $includeUserData) {
                    $allTables = $this->listAllTables($database);
                    $moduleCatalog = $this->getModuleCatalog($allTables);
                    if ($includeModuleData) {
                        $moduleTables = [];
                        if (!empty($requestedModules)) {
                            foreach ($requestedModules as $moduleKey) {
                                if (isset($moduleCatalog[$moduleKey])) {
                                    $moduleTables = array_merge($moduleTables, $moduleCatalog[$moduleKey]['tables']);
                                }
                            }
                        } else {
                            foreach ($moduleCatalog as $module) {
                                $moduleTables = array_merge($moduleTables, $module['tables']);
                            }
                        }
                        $selectedTables = array_merge($selectedTables, $moduleTables);

                        if (!empty($requestedModules) && empty($moduleTables)) {
                            Log::warning('Backup export rejected due to empty selected modules', ['modules' => $requestedModules]);
                            return response()->json([
                                'success' => false,
                                'message' => 'Selected module set is invalid or empty.',
                            ], 422);
                        }
                    }
                    if ($includeUserData) {
                        $selectedTables = array_merge($selectedTables, $this->getUserDataTables($allTables));
                    }
                    $selectedTables = array_values(array_unique($selectedTables));
                    if (! empty($selectedTables)) {
                        $tableArgs = ' ' . implode(' ', array_map('escapeshellarg', $selectedTables));
                    }
                }

                $command = sprintf(
                    'mysqldump --ssl=0 --no-tablespaces --host=%s --user=%s --password=%s %s%s > %s',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    $tableArgs,
                    escapeshellarg($sqlFile)
                );
                exec($command, $output, $returnVar);
                if ($returnVar !== 0) throw new \Exception('Database dump failed.');
            }

            if (!$includeDb && !$includeMedia) {
                throw new \Exception('Nothing selected for backup.');
            }

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Could not create backup archive.');
            }

            if ($includeDb && file_exists($sqlFile)) {
                $zip->addFile($sqlFile, 'database.sql');
            }

            $mediaAdded = 0;
            if ($includeMedia) {
                $mediaAdded = $this->addFilteredMediaToZip($zip, storage_path('app/public'), $mediaTypes);
            }
            $zip->close();

            Log::info('Backup archive created', [
                'include_db' => $includeDb,
                'include_media' => $includeMedia,
                'media_types' => $mediaTypes,
                'media_files_added' => $mediaAdded,
            ]);

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
            Log::error('Backup export failed', ['message' => $e->getMessage()]);
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
        if (!cache('app_config_maintenance_enabled', false)) {
            Log::warning('Restore blocked: maintenance mode disabled');
            return response()->json([
                'success' => false,
                'message' => 'Enable maintenance mode before running restore.',
            ], 423);
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000',
            'confirm_restore' => 'required|accepted',
        ]);

        $file = $request->file('backup_file');
        $tempDir = storage_path('app/temp_restore_' . time());
        mkdir($tempDir, 0755, true);

        try {
            Log::warning('Restore started');
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
            Log::error('Restore failed', ['message' => $e->getMessage()]);
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

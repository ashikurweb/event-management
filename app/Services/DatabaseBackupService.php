<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Exception;

class DatabaseBackupService
{
    /**
     * Create a database backup
     *
     * @param bool $download Whether to return download response
     * @return array|string
     */
    public function createBackup($download = false)
    {
        try {
            $connection = Config::get('database.default');
            $config = Config::get("database.connections.{$connection}");
            
            if ($config['driver'] !== 'mysql') {
                throw new Exception('Database backup is only supported for MySQL databases.');
            }

            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'];
            $host = $config['host'];
            $port = $config['port'] ?? 3306;

            // Generate backup filename
            $filename = 'backup_' . $database . '_' . Carbon::now()->format('Y-m-d_His') . '.sql';
            $backupPath = 'backups/' . $filename;

            // Create backups directory if it doesn't exist
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            $fullPath = Storage::disk('local')->path($backupPath);

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );

            // Execute backup command
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($fullPath)) {
                throw new Exception('Failed to create database backup. Please check database credentials and mysqldump availability.');
            }

            // Check if file is empty or too small (less than 100 bytes)
            if (filesize($fullPath) < 100) {
                Storage::disk('local')->delete($backupPath);
                throw new Exception('Backup file is empty. Please check database connection and permissions.');
            }

            if ($download) {
                return [
                    'path' => $fullPath,
                    'filename' => $filename,
                ];
            }

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $backupPath,
                'size' => $this->formatBytes(filesize($fullPath)),
                'created_at' => Carbon::now()->toDateTimeString(),
            ];
        } catch (Exception $e) {
            throw new Exception('Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Get list of backups
     *
     * @return array
     */
    public function getBackups()
    {
        $backups = [];
        
        if (Storage::disk('local')->exists('backups')) {
            $files = Storage::disk('local')->files('backups');
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $backups[] = [
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes(Storage::disk('local')->size($file)),
                        'created_at' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file))->toDateTimeString(),
                    ];
                }
            }
        }

        // Sort by created_at descending
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Delete a backup file
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup($filename)
    {
        $path = 'backups/' . basename($filename);
        
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->delete($path);
        }

        return false;
    }

    /**
     * Get backup configuration
     *
     * @return array
     */
    public function getBackupConfig()
    {
        return [
            'enabled' => config('backup.enabled', false),
            'frequency' => config('backup.frequency', 'daily'), // daily, weekly, monthly
            'time' => config('backup.time', '02:00'), // HH:mm format
        ];
    }

    /**
     * Save backup configuration
     *
     * @param array $config
     * @return bool
     */
    public function saveBackupConfig(array $config)
    {
        // In a real application, you might want to store this in database
        // For now, we'll use config file
        $configPath = config_path('backup.php');
        
        $content = "<?php\n\nreturn [\n";
        $content .= "    'enabled' => " . ($config['enabled'] ? 'true' : 'false') . ",\n";
        $content .= "    'frequency' => '" . addslashes($config['frequency']) . "',\n";
        $content .= "    'time' => '" . addslashes($config['time']) . "',\n";
        $content .= "];\n";

        return file_put_contents($configPath, $content) !== false;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}


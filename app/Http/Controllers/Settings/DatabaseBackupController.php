<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use App\Services\MailConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DatabaseBackupController extends Controller
{
    public function __construct(
        protected DatabaseBackupService $backupService,
        protected MailConfigurationService $mailConfigService
    ) {}

    /**
     * Show database backup page
     */
    public function index(): Response
    {
        $backups = $this->backupService->getBackups();
        $mailConfig = $this->mailConfigService->getMailConfiguration();

        return Inertia::render('Dashboard/Settings/GeneralSettings', [
            'activeTab' => 'database-backup',
            'backups' => $backups,
            'mailConfig' => $mailConfig,
        ]);
    }

    /**
     * Create a manual backup
     */
    public function create(Request $request)
    {
        try {
            $result = $this->backupService->createBackup();
            
            // Use the created_at from the result (actual backup creation time)
            // Don't rely on getBackups() as file timestamp might not be immediately updated
            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully!',
                'backup' => [
                    'filename' => $result['filename'],
                    'path' => $result['path'] ?? null,
                    'size' => $result['size'] ?? '0 B',
                    'created_at' => $result['created_at'], // Use the actual creation time from backup service
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function download(Request $request, $filename)
    {
        try {
            $path = 'backups/' . basename($filename);
            $fullPath = Storage::disk('local')->path($path);
            
            if (!Storage::disk('local')->exists($path)) {
                abort(404, 'Backup file not found');
            }

            return response()->download($fullPath, $filename);
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Failed to download backup: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a backup file
     */
    public function delete(Request $request, $filename)
    {
        try {
            $deleted = $this->backupService->deleteBackup($filename);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup deleted successfully!',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Backup file not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save backup configuration
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'enabled' => 'boolean',
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => ['required', 'date_format:Y-m-d H:i:s'],
        ]);

        try {
            $this->backupService->saveBackupConfig($request->only([
                'enabled',
                'frequency',
                'time',
                'keep_backups',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Backup configuration saved successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}


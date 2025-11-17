<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Save multiple files to storage
     *
     * @param array $files Array of uploaded files
     * @param string $folder Folder name (e.g., 'speakers', 'events')
     * @param string $disk Storage disk (default: 'public')
     * @return array Array of file paths/URLs
     */
    public static function saveMultipleFiles(array $files, string $folder, string $disk = 'public'): array
    {
        $savedFiles = [];
        
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $fileName = self::generateFileName($file);
                $filePath = $file->storeAs($folder, $fileName, $disk);
                
                if ($filePath) {
                    $savedFiles[] = $filePath;
                }
            }
        }
        
        return $savedFiles;
    }

    /**
     * Save a single file to storage
     *
     * @param \Illuminate\Http\UploadedFile $file Uploaded file
     * @param string $folder Folder name
     * @param string $disk Storage disk (default: 'public')
     * @return string|null File path or null if failed
     */
    public static function saveFile($file, string $folder, string $disk = 'public'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $fileName = self::generateFileName($file);
        $filePath = $file->storeAs($folder, $fileName, $disk);
        
        return $filePath ?: null;
    }

    /**
     * Delete multiple files from storage
     *
     * @param array $filePaths Array of file paths
     * @param string $disk Storage disk (default: 'public')
     * @return bool True if all files deleted successfully
     */
    public static function deleteMultipleFiles(array $filePaths, string $disk = 'public'): bool
    {
        $allDeleted = true;
        
        foreach ($filePaths as $filePath) {
            if ($filePath && Storage::disk($disk)->exists($filePath)) {
                if (!Storage::disk($disk)->delete($filePath)) {
                    $allDeleted = false;
                }
            }
        }
        
        return $allDeleted;
    }

    /**
     * Delete a single file from storage
     *
     * @param string $filePath File path
     * @param string $disk Storage disk (default: 'public')
     * @return bool True if file deleted successfully
     */
    public static function deleteFile(string $filePath, string $disk = 'public'): bool
    {
        if (!$filePath || !Storage::disk($disk)->exists($filePath)) {
            return false;
        }
        
        return Storage::disk($disk)->delete($filePath);
    }

    /**
     * Get public URL for a file
     *
     * @param string $filePath File path
     * @param string $disk Storage disk (default: 'public')
     * @return string|null Public URL or null
     */
    public static function getFileUrl(string $filePath, string $disk = 'public'): ?string
    {
        if (!$filePath) {
            return null;
        }

        if ($disk === 'public') {
            return Storage::disk($disk)->url($filePath);
        }

        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Get public URLs for multiple files
     *
     * @param array $filePaths Array of file paths
     * @param string $disk Storage disk (default: 'public')
     * @return array Array of public URLs
     */
    public static function getMultipleFileUrls(array $filePaths, string $disk = 'public'): array
    {
        $urls = [];
        
        foreach ($filePaths as $filePath) {
            $url = self::getFileUrl($filePath, $disk);
            if ($url) {
                $urls[] = $url;
            }
        }
        
        return $urls;
    }

    /**
     * Replace files (delete old, save new)
     *
     * @param array $oldFiles Old file paths to delete
     * @param array $newFiles New files to upload
     * @param string $folder Folder name
     * @param string $disk Storage disk (default: 'public')
     * @return array Array of new file paths
     */
    public static function replaceFiles(array $oldFiles, array $newFiles, string $folder, string $disk = 'public'): array
    {
        // Delete old files
        self::deleteMultipleFiles($oldFiles, $disk);
        
        // Save new files
        return self::saveMultipleFiles($newFiles, $folder, $disk);
    }

    /**
     * Merge existing files with new files
     *
     * @param array $existingFiles Existing file paths
     * @param array $newFiles New files to upload
     * @param string $folder Folder name
     * @param string $disk Storage disk (default: 'public')
     * @return array Merged array of file paths
     */
    public static function mergeFiles(array $existingFiles, array $newFiles, string $folder, string $disk = 'public'): array
    {
        $newFilePaths = self::saveMultipleFiles($newFiles, $folder, $disk);
        
        return array_merge($existingFiles, $newFilePaths);
    }

    /**
     * Remove specific files from existing files
     *
     * @param array $existingFiles Existing file paths
     * @param array $filesToRemove File paths to remove
     * @param string $disk Storage disk (default: 'public')
     * @return array Remaining file paths
     */
    public static function removeFiles(array $existingFiles, array $filesToRemove, string $disk = 'public'): array
    {
        // Delete files from storage
        self::deleteMultipleFiles($filesToRemove, $disk);
        
        // Remove from array
        return array_values(array_filter($existingFiles, function($file) use ($filesToRemove) {
            return !in_array($file, $filesToRemove);
        }));
    }

    /**
     * Generate unique file name
     *
     * @param \Illuminate\Http\UploadedFile $file Uploaded file
     * @return string Generated file name
     */
    private static function generateFileName($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $random = Str::random(10);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Validate file before saving
     *
     * @param \Illuminate\Http\UploadedFile $file Uploaded file
     * @param array $allowedMimes Allowed MIME types
     * @param int $maxSize Max size in bytes
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateFile($file, array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], int $maxSize = 5242880): array
    {
        if (!$file || !$file->isValid()) {
            return ['valid' => false, 'message' => 'Invalid file'];
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }

        if ($file->getSize() > $maxSize) {
            return ['valid' => false, 'message' => 'File size exceeds maximum allowed size'];
        }

        return ['valid' => true, 'message' => 'File is valid'];
    }
}


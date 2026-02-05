<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoService
{
    protected string $disk = 'public';
    protected string $basePath = 'videos';

    /**
     * Upload a video file
     */
    public function upload(UploadedFile $file, ?string $folder = null): array
    {
        $folder = $folder ? "{$this->basePath}/{$folder}" : $this->basePath;

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;

        // Store the file
        $path = $file->storeAs($folder, $filename, $this->disk);

        return [
            'path' => $path,
            'url' => $this->getUrl($path),
            'original_name' => $originalName,
            'filename' => $filename,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
        ];
    }

    /**
     * Upload video for a specific lesson
     */
    public function uploadForLesson(UploadedFile $file, int $lessonId): array
    {
        return $this->upload($file, "lessons/{$lessonId}");
    }

    /**
     * Upload video for a specific course
     */
    public function uploadForCourse(UploadedFile $file, int $courseId): array
    {
        return $this->upload($file, "courses/{$courseId}");
    }

    /**
     * Delete a video file
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Get the full URL for a video path
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if a video exists
     */
    public function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get video metadata
     */
    public function getMetadata(string $path): ?array
    {
        if (!$this->exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => $this->getUrl($path),
            'size' => Storage::disk($this->disk)->size($path),
            'size_formatted' => $this->formatBytes(Storage::disk($this->disk)->size($path)),
            'last_modified' => Storage::disk($this->disk)->lastModified($path),
        ];
    }

    /**
     * List all videos in a folder
     */
    public function list(?string $folder = null): array
    {
        $path = $folder ? "{$this->basePath}/{$folder}" : $this->basePath;

        $files = Storage::disk($this->disk)->files($path);

        return array_map(function ($file) {
            return [
                'path' => $file,
                'url' => $this->getUrl($file),
                'filename' => basename($file),
                'size' => Storage::disk($this->disk)->size($file),
                'size_formatted' => $this->formatBytes(Storage::disk($this->disk)->size($file)),
                'last_modified' => Storage::disk($this->disk)->lastModified($file),
            ];
        }, $files);
    }

    /**
     * Get allowed mime types for validation
     */
    public function getAllowedMimeTypes(): array
    {
        return [
            'video/mp4',
            'video/webm',
            'video/ogg',
            'video/quicktime',
            'video/x-msvideo',
        ];
    }

    /**
     * Get allowed extensions for validation
     */
    public function getAllowedExtensions(): array
    {
        return ['mp4', 'webm', 'ogg', 'mov', 'avi'];
    }

    /**
     * Get max file size in kilobytes
     */
    public function getMaxFileSize(): int
    {
        return 512000; // 512MB in KB
    }

    /**
     * Get validation rules for video upload
     */
    public function getValidationRules(bool $required = false): array
    {
        $rules = ['file', 'mimes:' . implode(',', $this->getAllowedExtensions()), 'max:' . $this->getMaxFileSize()];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

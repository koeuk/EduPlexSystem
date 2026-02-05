<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        protected VideoService $videoService
    ) {}

    /**
     * Upload a video
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'video' => $this->videoService->getValidationRules(required: true),
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $folder = $request->input('folder', 'general');
        $videoData = $this->videoService->upload($request->file('video'), $folder);

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'data' => $videoData,
        ]);
    }

    /**
     * Upload video for a specific lesson
     */
    public function uploadForLesson(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'video' => $this->videoService->getValidationRules(required: true),
        ]);

        // Delete old video if exists
        $this->videoService->delete($lesson->video_url);

        // Upload new video
        $videoData = $this->videoService->upload($request->file('video'), "lessons/{$lesson->id}");

        // Update lesson
        $lesson->update(['video_url' => $videoData['path']]);

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'data' => [
                'lesson_id' => $lesson->id,
                'video' => $videoData,
            ],
        ]);
    }

    /**
     * Delete a video
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $deleted = $this->videoService->delete($request->input('path'));

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Video deleted successfully' : 'Video not found',
        ]);
    }

    /**
     * Delete video from a lesson
     */
    public function destroyFromLesson(Lesson $lesson): JsonResponse
    {
        if (!$lesson->video_url) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson has no video',
            ], 404);
        }

        $this->videoService->delete($lesson->video_url);
        $lesson->update(['video_url' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully',
        ]);
    }

    /**
     * Get video metadata
     */
    public function metadata(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $metadata = $this->videoService->getMetadata($request->input('path'));

        if (!$metadata) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $metadata,
        ]);
    }

    /**
     * List videos in a folder
     */
    public function list(Request $request): JsonResponse
    {
        $folder = $request->query('folder');
        $videos = $this->videoService->list($folder);

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    /**
     * Get upload configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'allowed_extensions' => $this->videoService->getAllowedExtensions(),
                'allowed_mime_types' => $this->videoService->getAllowedMimeTypes(),
                'max_file_size' => $this->videoService->getMaxFileSize(),
                'max_file_size_formatted' => $this->formatBytes($this->videoService->getMaxFileSize() * 1024),
            ],
        ]);
    }

    /**
     * Stream video (for lessons)
     */
    public function stream(Lesson $lesson): mixed
    {
        if (!$lesson->video_url || !$this->videoService->exists($lesson->video_url)) {
            abort(404, 'Video not found');
        }

        $path = storage_path("app/public/{$lesson->video_url}");

        return response()->file($path, [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
        ]);
    }

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

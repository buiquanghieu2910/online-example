<?php

if (!function_exists('minio_image_base64')) {
    /**
     * Get image from MinIO as base64 data URL
     *
     * @param string|null $path
     * @return string|null
     */
    function minio_image_base64(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            $disk = Storage::disk('minio');
            
            if (!$disk->exists($path)) {
                return null;
            }

            $contents = $disk->get($path);
            $mimeType = $disk->mimeType($path);
            
            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
        } catch (Exception $e) {
            \Log::error('Failed to get MinIO image: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('minio_temporary_url')) {
    /**
     * Get temporary signed URL from MinIO
     *
     * @param string|null $path
     * @param int $minutes
     * @return string|null
     */
    function minio_temporary_url(?string $path, int $minutes = 60): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            return Storage::disk('minio')->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (Exception $e) {
            \Log::error('Failed to generate MinIO temporary URL: ' . $e->getMessage());
            return null;
        }
    }
}

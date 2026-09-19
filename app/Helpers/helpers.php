<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('glide_image')) {
    /**
     * Generate an on-the-fly resized image URL using Glide.
     *
     * @param  string|null  $path  Relative path in storage (e.g. 'logos/abc.jpg' or 'prewedding/def.jpg')
     * @param  array<string, mixed>  $params  Glide manipulation parameters (e.g. ['w' => 300, 'fit' => 'crop'])
     */
    function glide_image(?string $path, array $params = []): ?string
    {
        if (blank($path)) {
            return null;
        }

        // If path is already an external URL or data URI, return directly
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        // Clean leading slashes and optional 'storage/' prefix
        $cleanedPath = ltrim($path, '/');
        if (str_starts_with($cleanedPath, 'storage/')) {
            $cleanedPath = substr($cleanedPath, 8);
        }

        // If no Glide parameters are provided, return normal storage URL
        if (empty($params)) {
            return Storage::url($cleanedPath);
        }

        return route('image.show', array_merge(['path' => $cleanedPath], $params));
    }
}

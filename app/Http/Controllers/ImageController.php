<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    protected ?Server $server = null;

    /**
     * Get or initialize the Glide Server instance.
     */
    protected function getServer(): Server
    {
        if ($this->server === null) {
            $this->server = ServerFactory::create([
                'response' => new LaravelResponseFactory(app('request')),
                'source' => storage_path('app/public'),
                'cache' => storage_path('framework/cache/images'),
                'cache_path_prefix' => '.cache',
                'base_url' => 'img',
                'max_image_size' => 2000 * 2000,
            ]);
        }

        return $this->server;
    }

    /**
     * Serve dynamic resized and cached images on-the-fly.
     */
    public function show(Request $request, string $path): Response
    {
        // Sanitize path against directory traversal
        $cleanedPath = ltrim(str_replace(['../', '..\\'], '', $path), '/');

        // Check if the source image exists in public storage
        if (! Storage::disk('public')->exists($cleanedPath)) {
            abort(404, 'Image not found.');
        }

        try {
            return $this->getServer()->getImageResponse($cleanedPath, $request->all());
        } catch (\Throwable $e) {
            // If image manipulation fails, fall back to streaming original file or 404
            return response()->file(Storage::disk('public')->path($cleanedPath));
        }
    }
}

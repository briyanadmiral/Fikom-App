<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Service untuk optimasi gambar yang diupload
 * Menggunakan Intervention Image v3 dengan GD driver
 */
class ImageOptimizerService
{
    protected ImageManager $manager;

    // Default settings
    protected int $maxWidth = 800;

    protected int $maxHeight = 800;

    protected int $quality = 85;

    protected bool $convertToWebp = false;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Optimize uploaded image file
     *
     * @param  string  $directory  Storage directory (e.g., 'kop')
     * @param  array  $options  Custom options
     * @return string|null Stored file path or null on failure
     */
    public function optimizeAndStore(UploadedFile $file, string $directory = 'kop', array $options = []): ?string
    {
        try {
            $maxWidth = $options['maxWidth'] ?? $this->maxWidth;
            $maxHeight = $options['maxHeight'] ?? $this->maxHeight;
            $quality = $options['quality'] ?? $this->quality;
            $toWebp = $options['convertToWebp'] ?? $this->convertToWebp;

            // Read the image
            $image = $this->manager->read($file->getPathname());

            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // Only resize if larger than max dimensions
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            }

            // Determine output format
            $extension = $toWebp ? 'webp' : strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                $extension = 'jpg';
            }

            // Generate unique filename
            $filename = uniqid().'_'.time().'.'.$extension;
            $path = $directory.'/'.$filename;

            // Encode based on format
            if ($extension === 'png') {
                $encoded = $image->toPng();
            } elseif ($extension === 'webp') {
                $encoded = $image->toWebp($quality);
            } elseif ($extension === 'gif') {
                $encoded = $image->toGif();
            } else {
                $encoded = $image->toJpeg($quality);
            }

            // Store to public disk
            Storage::disk('public')->put($path, (string) $encoded);

            Log::info('Image optimized successfully', [
                'original_size' => $file->getSize(),
                'original_dimensions' => "{$originalWidth}x{$originalHeight}",
                'new_dimensions' => "{$image->width()}x{$image->height()}",
                'path' => $path,
            ]);

            return $path;

        } catch (Exception $e) {
            Log::error('Image optimization failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            // Fallback: store without optimization
            return $file->store($directory, 'public');
        }
    }

    /**
     * Optimize image with specific settings for logo
     */
    public function optimizeLogo(UploadedFile $file, string $directory = 'kop'): ?string
    {
        return $this->optimizeAndStore($file, $directory, [
            'maxWidth' => 500,
            'maxHeight' => 200,
            'quality' => 90,
            'convertToWebp' => false, // Keep original format for logos
        ]);
    }

    /**
     * Optimize image with specific settings for background
     */
    public function optimizeBackground(UploadedFile $file, string $directory = 'kop'): ?string
    {
        return $this->optimizeAndStore($file, $directory, [
            'maxWidth' => 1200,
            'maxHeight' => 400,
            'quality' => 85,
            'convertToWebp' => false,
        ]);
    }

    /**
     * Optimize stamp/cap image
     */
    public function optimizeStamp(UploadedFile $file, string $directory = 'kop'): ?string
    {
        return $this->optimizeAndStore($file, $directory, [
            'maxWidth' => 300,
            'maxHeight' => 300,
            'quality' => 90,
            'convertToWebp' => false,
        ]);
    }

    /**
     * Check if Intervention Image is available
     */
    public static function isAvailable(): bool
    {
        return class_exists(ImageManager::class);
    }
}

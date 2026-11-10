<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait HandlesImages
{
    protected function openImage(UploadedFile $file)
    {
        return ImageManager::usingDriver(Driver::class)->decodePath($file->getRealPath());
    }

    protected function originalExtension(UploadedFile $file): string
    {
        $ext = strtolower($file->extension() ?: 'jpg');

        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    protected function sendImage($image, UploadedFile $file, string $ext, string $suffix, int $quality = 90)
    {
        $mime = [
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ][$ext] ?? 'application/octet-stream';

        $encoded = $image->encodeUsingFileExtension($ext, quality: $quality);
        $name    = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';

        return response($encoded->toString(), 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '-' . $suffix . '.' . $ext . '"',
        ]);
    }
}
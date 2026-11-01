<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class ImageConversionService
{
    public function convertToData(
        UploadedFile $file,
        string $format,
        int $quality = 85
    ): string {

        $format = strtolower($format);

        $allowedFormats = ['jpg', 'png', 'webp', 'avif'];

        if (!in_array($format, $allowedFormats, true)) {
            throw new RuntimeException('Unsupported output format.');
        }

        $quality = max(1, min(100, $quality));

        // Check AVIF output support
        if ($format === 'avif' && !function_exists('imageavif')) {
            throw new RuntimeException(
                'AVIF output is not supported by your PHP GD installation.'
            );
        }

        // Check image dimensions before decoding
        $info = @getimagesize($file->getRealPath());

        if (!$info) {
            throw new RuntimeException('Invalid image file.');
        }

        [$width, $height] = $info;

        if (
            $width <= 0 ||
            $height <= 0 ||
            $width > 6000 ||
            $height > 6000 ||
            ($width * $height) > 25_000_000
        ) {
            throw new RuntimeException('Image dimensions are too large.');
        }

        // Read source image
        $imageData = file_get_contents($file->getRealPath());

        if ($imageData === false) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $image = @imagecreatefromstring($imageData);

        if (!$image) {
            throw new RuntimeException(
                'Unable to read this image. Your GD installation may not support its format.'
            );
        }

        try {

            // JPG does not support transparency
            if ($format === 'jpg') {

                $background = imagecreatetruecolor($width, $height);

                if (!$background) {
                    throw new RuntimeException('Unable to create image canvas.');
                }

                $white = imagecolorallocate(
                    $background,
                    255,
                    255,
                    255
                );

                imagefill($background, 0, 0, $white);

                imagecopy(
                    $background,
                    $image,
                    0,
                    0,
                    0,
                    0,
                    $width,
                    $height
                );

                imagedestroy($image);

                $image = $background;

            } else {

                // Preserve transparency for PNG, WebP and AVIF
                if (!imageistruecolor($image)) {
                    imagepalettetotruecolor($image);
                }

                imagealphablending($image, false);
                imagesavealpha($image, true);
            }

            // Convert image into binary data
            ob_start();

            $success = match ($format) {

                'jpg' => imagejpeg(
                    $image,
                    null,
                    $quality
                ),

                'png' => imagepng(
                    $image,
                    null,
                    (int) round(9 - ($quality / 100) * 9)
                ),

                'webp' => function_exists('imagewebp')
                    ? imagewebp($image, null, $quality)
                    : false,

                'avif' => function_exists('imageavif')
                    ? imageavif($image, null, $quality)
                    : false,

                default => false,
            };

            $data = ob_get_clean();

            if (!$success || $data === false || $data === '') {
                throw new RuntimeException('Image conversion failed.');
            }

            return $data;

        } finally {

            imagedestroy($image);

        }
    }
}

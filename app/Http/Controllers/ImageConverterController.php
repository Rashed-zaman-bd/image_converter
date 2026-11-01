<?php

namespace App\Http\Controllers;

use App\Services\ImageConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Throwable;

class ImageConverterController extends Controller
{
    public function index()
    {
        return view('converter.index');
    }

    public function convert(
        Request $request,
        ImageConversionService $converter
    ) {

        // Validate uploaded images
        $request->validate([
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp,avif|max:10240',
            'format' => 'required|in:jpg,png,webp,avif',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        $files = $request->file('images');
        $format = $request->input('format');
        $quality = (int) $request->input('quality', 85);

        // Total upload size limit: 50MB
        $totalSize = collect($files)->sum(
            fn ($file) => $file->getSize()
        );

        if ($totalSize > 50 * 1024 * 1024) {
            return back()->withErrors([
                'images' => 'Total image size cannot exceed 50MB.'
            ]);
        }

        // Check AVIF support
        if ($format === 'avif' && !function_exists('imageavif')) {
            return back()->withErrors([
                'format' => 'AVIF output is not supported by your PHP GD installation.'
            ]);
        }

        // Check WebP support
        if ($format === 'webp' && !function_exists('imagewebp')) {
            return back()->withErrors([
                'format' => 'WebP output is not supported by your PHP GD installation.'
            ]);
        }

        // SINGLE IMAGE: Direct download
        if (count($files) === 1) {

            try {

                $file = $files[0];

                $data = $converter->convertToData(
                    $file,
                    $format,
                    $quality
                );

                $originalName = pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $originalName = Str::slug($originalName) ?: 'converted';

                $filename = $originalName . '.' . $format;

                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'avif' => 'image/avif',
                ];

                return response($data, 200, [
                    'Content-Type' => $mimeTypes[$format],
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Content-Length' => strlen($data),
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);

            } catch (Throwable $e) {

                report($e);

                return back()->withErrors([
                    'images' => 'Image conversion failed. Please check your image and try again.'
                ]);
            }
        }

        // MULTIPLE IMAGES: ZIP download

        if (!class_exists(ZipArchive::class)) {
            return back()->withErrors([
                'images' => 'PHP ZIP extension is not enabled.'
            ]);
        }

        $directory = storage_path('app/private/conversions');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $zipName = 'converted_' . Str::uuid() . '.zip';
        $zipPath = $directory . '/' . $zipName;

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors([
                'images' => 'Unable to create ZIP file.'
            ]);
        }

        try {

            foreach ($files as $file) {

                // Convert image
                $data = $converter->convertToData(
                    $file,
                    $format,
                    $quality
                );

                // Original filename
                $originalName = pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $originalName = Str::slug($originalName) ?: 'converted';

                $filename = $originalName . '.' . $format;

                // Avoid duplicate filenames
                $counter = 1;

                while ($zip->locateName($filename) !== false) {

                    $filename = $originalName
                        . '_' . $counter
                        . '.' . $format;

                    $counter++;
                }

                // Add converted image to ZIP
                if (!$zip->addFromString($filename, $data)) {
                    throw new \RuntimeException(
                        'Unable to add converted image to ZIP.'
                    );
                }
            }

            if (!$zip->close()) {
                throw new \RuntimeException('Unable to finalize ZIP file.');
            }

            return response()
                ->download($zipPath, 'converted-images.zip')
                ->deleteFileAfterSend(true);

        } catch (Throwable $e) {

            $zip->close();

            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }

            report($e);

            return back()->withErrors([
                'images' => 'Batch image conversion failed. Please try again.'
            ]);
        }
    }
}

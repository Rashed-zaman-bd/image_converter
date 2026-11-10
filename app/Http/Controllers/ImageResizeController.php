<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageResizeController extends Controller
{
    private const MAX_SIDE = 10000;

    public function index()
    {
        return view('tools.resize');
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'], // 50 MB
            'mode'    => ['required', 'in:pixels,percent'],
            'width'   => ['required_if:mode,pixels', 'nullable', 'integer', 'min:1', 'max:' . self::MAX_SIDE],
            'height'  => ['required_if:mode,pixels', 'nullable', 'integer', 'min:1', 'max:' . self::MAX_SIDE],
            'percent' => ['required_if:mode,percent', 'nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $file  = $request->file('image');
        $image = ImageManager::usingDriver(Driver::class)->decodePath($file->getRealPath());

        if ($data['mode'] === 'percent') {
            $width  = (int) round($image->width() * $data['percent'] / 100);
            $height = (int) round($image->height() * $data['percent'] / 100);
        } else {
            $width  = (int) $data['width'];
            $height = (int) $data['height'];
        }

        $width  = max(1, min($width, self::MAX_SIDE));
        $height = max(1, min($height, self::MAX_SIDE));

        if ($request->boolean('keep_ratio') && $data['mode'] === 'pixels') {
            // Keep the proportions: height follows the width
            $image->scale(width: $width);
        } else {
            $image->resize($width, $height);
        }

        return $this->download($image, $file, $image->width() . 'x' . $image->height());
    }

    private function download($image, UploadedFile $file, string $suffix)
    {
        $ext = strtolower($file->extension() ?: 'jpg');
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;

        $mime = [
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ][$ext] ?? 'application/octet-stream';

        $encoded = $image->encodeUsingFileExtension($ext, quality: 90);
        $name    = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';

        return response($encoded->toString(), 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '-' . $suffix . '.' . $ext . '"',
        ]);
    }
}
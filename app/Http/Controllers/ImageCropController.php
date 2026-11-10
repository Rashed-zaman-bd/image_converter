<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageCropController extends Controller
{
    public function index()
    {
        return view('tools.crop');
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'image'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'], // 50 MB
            'x'      => ['required', 'integer', 'min:0'],
            'y'      => ['required', 'integer', 'min:0'],
            'width'  => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
        ]);

        $file  = $request->file('image');
        $image = ImageManager::usingDriver(Driver::class)->decodePath($file->getRealPath());

        // Keep the crop box inside the image, whatever the browser sent
        $x      = min($data['x'], $image->width() - 1);
        $y      = min($data['y'], $image->height() - 1);
        $width  = min($data['width'], $image->width() - $x);
        $height = min($data['height'], $image->height() - $y);

        $image->crop($width, $height, $x, $y);

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
            'Content-Disposition' => 'attachment; filename="' . $name . '-cropped.' . $ext . '"',
        ]);
    }
}
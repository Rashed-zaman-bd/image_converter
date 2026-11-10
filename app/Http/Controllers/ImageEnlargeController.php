<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesImages;
use Illuminate\Http\Request;

class ImageEnlargeController extends Controller
{
    use HandlesImages;

    private const MAX_SIDE   = 10000;
    private const MAX_PIXELS = 60000000; // 60 megapixels

    public function index()
    {
        return view('tools.enlarge');
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'], // 50 MB
            'scale' => ['required', 'in:2,3,4'],
        ]);

        $file  = $request->file('image');
        $image = $this->openImage($file);

        $scale  = (int) $data['scale'];
        $width  = $image->width() * $scale;
        $height = $image->height() * $scale;

        if ($width > self::MAX_SIDE || $height > self::MAX_SIDE || $width * $height > self::MAX_PIXELS) {
            return back()
                ->withErrors(['scale' => 'This image would be too large at ' . $scale . 'x. Choose a smaller scale or a smaller image.'])
                ->withInput();
        }

        $image->resize($width, $height);

        if ($request->boolean('sharpen')) {
            $image->sharpen(10);
        }

        return $this->sendImage($image, $file, $this->originalExtension($file), $scale . 'x', 92);
    }
}
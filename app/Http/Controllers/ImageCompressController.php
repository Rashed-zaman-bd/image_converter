<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesImages;
use Illuminate\Http\Request;

class ImageCompressController extends Controller
{
    use HandlesImages;

    public function index()
    {
        return view('tools.compress');
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'], // 50 MB
            'quality' => ['required', 'integer', 'min:10', 'max:100'],
            'format'  => ['required', 'in:keep,webp,jpg'],
        ]);

        $file  = $request->file('image');
        $image = $this->openImage($file);

        $ext = $data['format'] === 'keep'
            ? $this->originalExtension($file)
            : $data['format'];

        return $this->sendImage($image, $file, $ext, 'compressed', (int) $data['quality']);
    }
}

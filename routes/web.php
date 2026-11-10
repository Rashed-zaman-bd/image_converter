<?php

use App\Http\Controllers\ImageConverterController;
use App\Http\Controllers\ImageCropController;
use App\Http\Controllers\ImageResizeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Image Converter
|--------------------------------------------------------------------------
*/
Route::get('/', [ImageConverterController::class, 'index'])->name('converter.index');
Route::post('/convert', [ImageConverterController::class, 'convert'])->name('converter.convert');

// Extra converter URL (not used by the Blade views)
Route::get('/image-converter', [ImageConverterController::class, 'index'])->name('image.converter');
Route::post('/image-converter', [ImageConverterController::class, 'convert'])->name('image.convert');

/*
|--------------------------------------------------------------------------
| Image Resizer
|--------------------------------------------------------------------------
*/
Route::get('/image-resizer', [ImageResizeController::class, 'index'])->name('resizer.index');
Route::post('/image-resizer', [ImageResizeController::class, 'process'])->name('resizer.process');

/*
|--------------------------------------------------------------------------
| Crop Image
|--------------------------------------------------------------------------
*/
Route::get('/crop-image', [ImageCropController::class, 'index'])->name('crop.index');
Route::post('/crop-image', [ImageCropController::class, 'process'])->name('crop.process');
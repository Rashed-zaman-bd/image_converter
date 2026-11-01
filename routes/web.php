<?php

use App\Http\Controllers\ImageConverterController;
use Illuminate\Support\Facades\Route;



Route::get('/image-converter', [ImageConverterController::class, 'index'])->name('image.converter');
Route::post('/image-converter', [ImageConverterController::class, 'convert'])->name('image.convert');

Route::get('/', [ImageConverterController::class, 'index'])
    ->name('converter.index');

Route::post('/convert', [ImageConverterController::class, 'convert'])
    ->name('converter.convert');


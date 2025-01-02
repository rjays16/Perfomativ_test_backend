<?php

use Illuminate\Support\Facades\Route;

Route::get('storage/{path}', function($path) {
    return response()->file(storage_path('app/public/' . $path));
})->where('path', '.*');
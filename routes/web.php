<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/docs', function () {
    return response()->file(public_path('docs/index.html'));
});

<?php

use App\Http\Controllers\ImageController;
use App\Livewire\ChatRoom;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/img/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('image.show');

Route::get('/{page:slug}', ChatRoom::class);

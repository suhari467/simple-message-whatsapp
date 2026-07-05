<?php

use App\Livewire\ChatRoom;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/{page:slug}', ChatRoom::class);

<?php

use App\Livewire\Chatroom;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/{page:slug}', Chatroom::class);

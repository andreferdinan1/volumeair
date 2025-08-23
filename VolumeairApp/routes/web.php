<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('volumeair.dashboard'); // file Blade kamu
})->name('volumeair.dashboard');


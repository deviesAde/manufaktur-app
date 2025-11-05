<?php

use Illuminate\Support\Facades\Route;

// Redirect root URL to Filament admin panel
Route::redirect('/', '/admin');


// Route::get('/welcome', function () {
//     return view('welcome');
// })->name('welcome');

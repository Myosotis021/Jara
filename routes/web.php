<?php

use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return 'Silakan login terlebih dahulu.';
})->name('login');

Route::middleware('auth')->group(function () {
    Route::resource('workspaces', WorkspaceController::class)->except(['show']);
});


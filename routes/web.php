<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return 'Silakan login terlebih dahulu.';
})->name('login');

Route::middleware('auth')->group(function () {
    Route::resource('workspaces', WorkspaceController::class);
    Route::post('workspaces/{workspace}/tasks', [TaskController::class, 'store'])->name('workspaces.tasks.store');
    Route::patch('workspaces/{workspace}/tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('workspaces.tasks.toggle-status');
    Route::get('workspaces/{workspace}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('workspaces.tasks.edit');
    Route::put('workspaces/{workspace}/tasks/{task}', [TaskController::class, 'update'])->name('workspaces.tasks.update');
    Route::delete('workspaces/{workspace}/tasks/{task}', [TaskController::class, 'destroy'])->name('workspaces.tasks.destroy');
});


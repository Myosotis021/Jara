<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Redirect Root
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/login'));

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Workspace Management
    Route::resource('workspaces', WorkspaceController::class);

    // Workspace Members Routes (PRD 3 - Kolaborasi Workspace)
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])
        ->name('workspaces.members.store');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])
        ->name('workspaces.members.destroy');

    // Task Management
    Route::post('workspaces/{workspace}/tasks', [TaskController::class, 'store'])->name('workspaces.tasks.store');
    Route::patch('workspaces/{workspace}/tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('workspaces.tasks.toggle-status');
    Route::get('workspaces/{workspace}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('workspaces.tasks.edit');
    Route::put('workspaces/{workspace}/tasks/{task}', [TaskController::class, 'update'])->name('workspaces.tasks.update');
    Route::delete('workspaces/{workspace}/tasks/{task}', [TaskController::class, 'destroy'])->name('workspaces.tasks.destroy');

    // Task Attachments
    Route::post('workspaces/{workspace}/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('workspaces.tasks.attachments.store');
    Route::get('workspaces/{workspace}/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('workspaces.tasks.attachments.download');
    Route::delete('workspaces/{workspace}/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('workspaces.tasks.attachments.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

<?php

use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\WorkspaceController;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = User::first();
    if ($user) {
        auth()->login($user);
    }
    $workspace = Workspace::first();
    if ($workspace) {
        return redirect()->route('workspaces.show', $workspace->id);
    }
    return view('welcome');
});

Route::get('/login', function () {
    return 'Silakan login terlebih dahulu.';
})->name('login');

Route::middleware('auth')->group(function () {
    Route::resource('workspaces', WorkspaceController::class)->except(['show']);

    Route::get('/workspaces/{workspace}', function (Workspace $workspace) {
        if (!auth()->check()) {
            $user = User::first();
            if ($user) {
                auth()->login($user);
            }
        }
        $workspace->load(['members', 'tasks.attachments.uploader', 'tasks.creator']);
        return view('workspaces.show', compact('workspace'));
    })->name('workspaces.show');

    // Task Attachments Routes (PRD 4)
    Route::post('/workspaces/{workspace}/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
        ->name('workspaces.tasks.attachments.store');

    Route::get('/workspaces/{workspace}/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])
        ->name('workspaces.tasks.attachments.download');

    Route::delete('/workspaces/{workspace}/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
        ->name('workspaces.tasks.attachments.destroy');
});

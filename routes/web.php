<?php

use App\Http\Controllers\TaskAttachmentController;
use App\Models\Workspace;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/workspaces/{workspace}', function (Workspace $workspace) {
        $workspace->load(['tasks.attachments.uploader', 'tasks.creator']);
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

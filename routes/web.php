<?php

use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
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
    $user = User::first();
    if ($user) {
        auth()->login($user);
        $workspace = Workspace::first();
        if ($workspace) {
            return redirect()->route('workspaces.show', $workspace->id);
        }
    }
    return 'Silakan login terlebih dahulu.';
})->name('login');

Route::middleware('auth')->group(function () {
    Route::resource('workspaces', WorkspaceController::class)->except(['show']);

    Route::get('/workspaces/{workspace}', function (Workspace $workspace) {
        $workspace->load(['owner', 'members', 'tasks.attachments.uploader', 'tasks.creator']);

        $existingMemberIds = $workspace->members->pluck('id')->push($workspace->user_id)->toArray();
        $availableUsers = User::whereNotIn('id', $existingMemberIds)->get();

        return view('workspaces.show', compact('workspace', 'availableUsers'));
    })->name('workspaces.show');

    // Workspace Members Routes (PRD 3 - Kolaborasi Workspace)
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])
        ->name('workspaces.members.store');

    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])
        ->name('workspaces.members.destroy');

    // Task Attachments Routes (PRD 4)
    Route::post('/workspaces/{workspace}/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
        ->name('workspaces.tasks.attachments.store');

    Route::get('/workspaces/{workspace}/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])
        ->name('workspaces.tasks.attachments.download');

    Route::delete('/workspaces/{workspace}/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
        ->name('workspaces.tasks.attachments.destroy');
});

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->file_size >= 1048576) {
            return round($this->file_size / 1048576, 1) . ' MB';
        }

        return round($this->file_size / 1024, 1) . ' KB';
    }
}

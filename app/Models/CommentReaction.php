<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends Model
{
    protected $fillable = [
        'comment_id',
        'emoji',
        'user_token',
    ];

    public const ALLOWED = ['heart', 'fire', 'wow', 'clap', 'laugh'];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}

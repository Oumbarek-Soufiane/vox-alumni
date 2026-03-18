<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'artwork_id',
        'parent_id',
        'author_name',
        'body',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /** Direct replies to this comment */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('reactions', 'replies');
    }

    /** Parent comment (if this is a reply) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /** Emoji reactions on this comment */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /** Human-friendly relative timestamp */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Returns reaction counts keyed by emoji.
     * e.g. ["heart" => 4, "fire" => 1, ...]
     */
    public function getReactionCountsAttribute(): array
    {
        return $this->reactions
            ->groupBy('emoji')
            ->map(fn ($group) => $group->count())
            ->toArray();
    }

    protected $appends = ['time_ago', 'reaction_counts'];
}

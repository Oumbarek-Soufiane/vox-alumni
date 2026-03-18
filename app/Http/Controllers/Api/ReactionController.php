<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReactionController extends Controller
{
    /**
     * POST /api/comments/{comment}/reactions/{emoji}
     *
     * Toggles a reaction on a comment for the current visitor.
     *
     * Response:
     * {
     *   "counts": { "heart": 3, "fire": 1, ... },
     *   "mine":   ["heart"]
     * }
     */
    public function toggle(Request $request, Comment $comment, string $emoji): JsonResponse
    {
        if (! in_array($emoji, CommentReaction::ALLOWED, true)) {
            return response()->json(['message' => 'Emoji non valide.'], 422);
        }

        $token = $this->token($request);

        $existing = CommentReaction::where([
            'comment_id' => $comment->id,
            'emoji'      => $emoji,
            'user_token' => $token,
        ])->first();

        if ($existing) {
            $existing->delete();
        } else {
            CommentReaction::create([
                'comment_id' => $comment->id,
                'emoji'      => $emoji,
                'user_token' => $token,
            ]);
        }

        // Reload fresh reaction state
        $comment->load('reactions');

        $counts = [];
        foreach (CommentReaction::ALLOWED as $key) {
            $counts[$key] = $comment->reactions->where('emoji', $key)->count();
        }

        $mine = $comment->reactions
            ->where('user_token', $token)
            ->pluck('emoji')
            ->values()
            ->toArray();

        return response()->json(compact('counts', 'mine'))
            ->cookie('vox_token', $token, 60 * 24 * 365);
    }

    private function token(Request $request): string
    {
        return $request->cookie('vox_token') ?? Str::random(40);
    }
}

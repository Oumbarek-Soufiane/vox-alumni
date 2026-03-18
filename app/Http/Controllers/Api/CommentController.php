<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\ProfanityFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    private ProfanityFilter $filter;

    public function __construct()
    {
        $this->filter = new ProfanityFilter();
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request, string $artworkId): JsonResponse
    {
        $token = $this->token($request);

        $comments = Comment::where('artwork_id', $artworkId)
            ->whereNull('parent_id')
            ->with(['reactions', 'replies.reactions'])
            ->latest()
            ->paginate(30);

        $comments->getCollection()->transform(
            fn ($c) => $this->injectMyReactions($c, $token)
        );

        return response()->json($comments)
            ->cookie('vox_token', $token, 60 * 24 * 365);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request, string $artworkId): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'author_name' => ['required', 'string', 'min:2', 'max:80'],
            'body'        => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id'   => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation échouée.', 'errors' => $v->errors()], 422);
        }

        // ── Profanity guard ───────────────────────────────────────────────────
        if ($this->filter->contains($request->input('body'))) {
            return response()->json([
                'message' => 'Votre commentaire contient des termes inappropriés. Merci de rester respectueux.',
                'code'    => 'profanity',
            ], 422);
        }

        if ($this->filter->contains($request->input('author_name'))) {
            return response()->json([
                'message' => 'Le nom contient des termes inappropriés.',
                'code'    => 'profanity_name',
            ], 422);
        }

        // ── Parent validation ─────────────────────────────────────────────────
        $parentId = $request->input('parent_id');

        if ($parentId) {
            $parent = Comment::find($parentId);
            if (! $parent || $parent->artwork_id !== $artworkId) {
                return response()->json(['message' => 'Commentaire parent introuvable.'], 404);
            }
            if ($parent->parent_id !== null) {
                return response()->json(['message' => 'Réponses imbriquées non autorisées.'], 422);
            }
        }

        $comment = Comment::create([
            'artwork_id'  => $artworkId,
            'parent_id'   => $parentId,
            'author_name' => $request->input('author_name'),
            'body'        => $request->input('body'),
        ]);

        $comment->load('reactions', 'replies');
        $comment = $this->injectMyReactions($comment, $this->token($request));

        return response()->json($comment, 201)
            ->cookie('vox_token', $this->token($request), 60 * 24 * 365);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Comment $comment): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'body'        => ['required', 'string', 'min:1', 'max:1000'],
            'author_name' => ['required', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation échouée.', 'errors' => $v->errors()], 422);
        }

        if ($comment->author_name !== $request->input('author_name')) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // ── Profanity guard ───────────────────────────────────────────────────
        if ($this->filter->contains($request->input('body'))) {
            return response()->json([
                'message' => 'Votre commentaire contient des termes inappropriés. Merci de rester respectueux.',
                'code'    => 'profanity',
            ], 422);
        }

        $comment->update(['body' => $request->input('body')]);

        $comment->load('reactions', 'replies');
        $comment = $this->injectMyReactions($comment, $this->token($request));

        return response()->json($comment);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $authorName = $request->input('author_name') ?? $request->query('author_name');

        if ($comment->author_name !== $authorName) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function token(Request $request): string
    {
        return $request->cookie('vox_token') ?? Str::random(40);
    }

    private function injectMyReactions(Comment $comment, string $token): Comment
    {
        $comment->my_reactions = $comment->reactions
            ->where('user_token', $token)
            ->pluck('emoji')
            ->values()
            ->toArray();

        if ($comment->relationLoaded('replies')) {
            $comment->setRelation(
                'replies',
                $comment->replies->map(fn ($r) => $this->injectMyReactions($r, $token))
            );
        }

        return $comment;
    }
}

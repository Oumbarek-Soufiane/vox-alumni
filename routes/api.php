<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ReactionController;
use Illuminate\Support\Facades\Route;

// ── Comments ──────────────────────────────────────────────────────────────────

Route::prefix('artworks/{artworkId}')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('comments',  [CommentController::class, 'index']);
        Route::post('comments', [CommentController::class, 'store']);
    });

Route::middleware('throttle:60,1')->group(function () {
    Route::patch('comments/{comment}',  [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});

// ── Reactions ─────────────────────────────────────────────────────────────────

Route::post('comments/{comment}/reactions/{emoji}', [ReactionController::class, 'toggle'])
    ->middleware('throttle:60,1');

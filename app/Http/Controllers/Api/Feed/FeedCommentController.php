<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedPost;
use App\Services\Feed\FeedNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedCommentController extends Controller
{
    public function __construct(
        private readonly FeedNotificationService $notifications
    ) {}

    /**
     * GET /api/v1/feed/{postId}/comments
     * Top-level comments with their replies eager loaded
     */
    public function index(string $postId): JsonResponse
    {
        $post = FeedPost::findOrFail($postId);

        $comments = FeedComment::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['author', 'replies.author'])
            ->withCount('likes')
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(20);

        return response()->json([
            'success' => true,
            'data'    => $comments,
        ]);
    }

    /**
     * POST /api/v1/feed/{postId}/comments
     * Add a comment or reply
     *
     * Body:
     *   body_en   string  nullable
     *   body_ar   string  nullable
     *   body_ku   string  nullable
     *   parent_id uuid    nullable (if replying to a comment)
     */
    public function store(Request $request, string $postId): JsonResponse
    {
        // ── Normalize plain `body` → body_en (Flutter sends `body`) ──────────
        if ($request->filled('body') && !$request->filled('body_en')) {
            $request->merge(['body_en' => $request->input('body')]);
        }
        if ($request->filled('body_en')) {
            if (!$request->filled('body_ar')) {
                $request->merge(['body_ar' => $request->input('body_en')]);
            }
            if (!$request->filled('body_ku')) {
                $request->merge(['body_ku' => $request->input('body_en')]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $request->validate([
            'body_en'   => 'nullable|string|max:1000',
            'body_ar'   => 'nullable|string|max:1000',
            'body_ku'   => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:feed_comments,id',
        ]);

        if (!$request->filled('body_en') && !$request->filled('body_ar') && !$request->filled('body_ku')) {
            return response()->json(['success' => false, 'message' => 'Comment cannot be empty.'], 422);
        }

        $post  = FeedPost::findOrFail($postId);
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $comment = FeedComment::create([
            'post_id'     => $post->id,
            'author_id'   => $actor->id,
            'author_type' => $actor->getMorphClass(),
            'body_en'     => $request->body_en,
            'body_ar'     => $request->body_ar,
            'body_ku'     => $request->body_ku,
            'parent_id'   => $request->parent_id,
            'status'      => 'approved',
        ]);

        $post->increment('comments_count');

        if ($request->filled('parent_id')) {
            $parentComment = FeedComment::find($request->parent_id);
            if ($parentComment) {
                $this->notifications->notifyCommentReplied($parentComment, $comment, $actor);
            }
        } else {
            $this->notifications->notifyPostCommented($post, $comment, $actor);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added.',
            'data'    => $comment->load('author'),
        ], 201);
    }

    /**
     * DELETE /api/v1/feed/comments/{id}
     * Author can delete their own comment
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $comment = FeedComment::findOrFail($id);
        $actor   = $this->getActor($request);

        if (!$actor || $actor->id !== $comment->author_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $comment->delete();

        // Decrement post comment counter
        FeedPost::where('id', $comment->post_id)->decrement('comments_count');

        return response()->json(['success' => true, 'message' => 'Comment deleted.']);
    }

    /**
     * POST /api/v1/feed/comments/{id}/like
     * Toggle like on a comment
     */
    public function toggleLike(Request $request, string $id): JsonResponse
    {
        $comment = FeedComment::findOrFail($id);
        $actor   = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $exists = $comment->likes()
            ->where('liker_id', $actor->id)
            ->where('liker_type', $actor->getMorphClass())
            ->exists();

        if ($exists) {
            $comment->likes()
                ->where('liker_id', $actor->id)
                ->where('liker_type', $actor->getMorphClass())
                ->delete();
            $comment->decrement('likes_count');
            $action = 'unliked';
        } else {
            $comment->likes()->create([
                'liker_id'   => $actor->id,
                'liker_type' => $actor->getMorphClass(),
            ]);
            $comment->increment('likes_count');
            $action = 'liked';

            // Notify comment author
            if ($action === 'liked') {
                $this->notifications->notifyCommentLiked($comment, $actor);
            }
        }

        return response()->json([
            'success'     => true,
            'action'      => $action,
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }

    private function getActor(Request $request): ?object
    {
        return $request->user()
            ?? $request->user('agent')
            ?? $request->user('office')
            ?? null;
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    }
}
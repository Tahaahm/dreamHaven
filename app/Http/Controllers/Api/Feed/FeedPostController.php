<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Jobs\Feed\NotifyFollowersOfNewPost;
use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedPost;
use App\Models\Feed\FeedPostMedia;
use App\Services\Feed\FeedNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeedPostController extends Controller
{
    public function __construct(
        private readonly FeedNotificationService $notifications
    ) {}

    // =========================================================================
    // FEED LIST
    // =========================================================================

    /**
     * GET /api/v1/feed
     */
    public function index(Request $request): JsonResponse
    {
        $query = FeedPost::published()
            ->with(['author', 'media', 'tags'])
            ->withCount(['comments', 'likes'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('post_type', $request->type);
        }

        $posts = $query->cursorPaginate(20);

        $actor = $this->getActor($request);
        if ($actor) {
            $followingKeys = $actor->getFollowingKeys();
            $posts->getCollection()->transform(function ($post) use ($actor, $followingKeys) {
                $post->is_liked     = $actor->hasLikedPost($post->id);
                $post->is_saved     = $actor->hasSavedPost($post->id);
                $post->is_following = in_array(
                    "{$post->author_type}:{$post->author_id}",
                    $followingKeys
                );
                return $post;
            });
        }

        return response()->json(['success' => true, 'data' => $posts]);
    }

    /**
     * GET /api/v1/feed/following
     */
    public function followingFeed(Request $request): JsonResponse
    {
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $posts = $actor->followingFeed()
            ->with(['author', 'media', 'tags'])
            ->cursorPaginate(20);

        $posts->getCollection()->transform(function ($post) use ($actor) {
            $post->is_liked = $actor->hasLikedPost($post->id);
            $post->is_saved = $actor->hasSavedPost($post->id);
            return $post;
        });

        return response()->json(['success' => true, 'data' => $posts]);
    }

    /**
     * GET /api/v1/feed/trending
     */
    public function trending(Request $request): JsonResponse
    {
        $posts = FeedPost::trending()
            ->with(['author', 'media'])
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $posts]);
    }

    // =========================================================================
    // SINGLE POST
    // =========================================================================

    /**
     * GET /api/v1/feed/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $post = FeedPost::published()
            ->with(['author', 'media', 'tags', 'branch'])
            ->findOrFail($id);

        $post->incrementViews();

        $actor = $this->getActor($request);

        return response()->json([
            'success' => true,
            'data'    => array_merge($post->toArray(), [
                'is_liked' => $actor ? $actor->hasLikedPost($post->id) : false,
                'is_saved' => $actor ? $actor->hasSavedPost($post->id) : false,
            ]),
        ]);
    }

    // =========================================================================
    // CREATE POST
    // =========================================================================

    /**
     * POST /api/v1/feed
     *
     * ✅ FIX: The mobile app (Flutter) was sending a plain `body` field.
     *         The validator only checked body_en / body_ar / body_ku, so all
     *         three were null → 422 "Post must have text or media."
     *
     *         Solution: accept a plain `body` field as a fallback alias.
     *         If `body` is present and body_en/ar/ku are all absent, we
     *         automatically copy `body` → body_en before validating.
     *         This keeps full backward compatibility with any future locale
     *         support while fixing the current mobile client immediately.
     *
     * Multipart form data:
     *   post_type   string   required
     *   body        string   nullable  ← mobile sends this (alias for body_en)
     *   body_en     string   nullable
     *   body_ar     string   nullable
     *   body_ku     string   nullable
     *   branch_id   int      nullable
     *   property_id uuid     nullable
     *   tags        array    nullable
     *   media[]     files    nullable
     */
    public function store(Request $request): JsonResponse
    {
        // ── Normalize plain `body` → body_en alias ────────────────────────────
        // The Flutter app sends `body`. Laravel expects `body_en`.
        // We merge it in so validation and model creation work correctly.
        if ($request->filled('body') && !$request->filled('body_en')) {
            $request->merge(['body_en' => $request->input('body')]);
        }
        // Also copy to body_ar / body_ku if they're empty — keeps DB consistent
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
            'post_type'   => 'required|in:general,listing_share,market_update,question,milestone,tip,office_announcement',
            'body_en'     => 'nullable|string|max:2000',
            'body_ar'     => 'nullable|string|max:2000',
            'body_ku'     => 'nullable|string|max:2000',
            'branch_id'   => 'nullable|exists:branches,id',
            'property_id' => 'nullable|exists:properties,id',
            'tags'        => 'nullable|array|max:10',
            'tags.*'      => 'string|max:60',
            'media'       => 'nullable|array|max:10',
            'media.*'     => 'file|mimes:jpg,jpeg,png,webp,mp4,mov|max:102400',
        ]);

        // Require at least text (any locale) or media
        if (
            !$request->filled('body_en') &&
            !$request->filled('body_ar') &&
            !$request->filled('body_ku') &&
            !$request->hasFile('media')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Post must have text or media.',
            ], 422);
        }

        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        DB::beginTransaction();

        try {
            $post = FeedPost::create([
                'author_id'   => $actor->id,
                'author_type' => $actor->getMorphClass(),
                'post_type'   => $request->post_type,
                'body_en'     => $request->body_en,
                'body_ar'     => $request->body_ar,
                'body_ku'     => $request->body_ku,
                'branch_id'   => $request->branch_id,
                'property_id' => $request->property_id,
                'status'      => 'approved',
            ]);

            // Tags
            if ($request->filled('tags')) {
                foreach ($request->tags as $tag) {
                    $post->tags()->create(['tag' => strtolower(trim($tag))]);
                }
            }

            // Media
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $index => $file) {
                    $this->attachMedia($post, $file, $index);
                }
            }

            DB::commit();

            NotifyFollowersOfNewPost::dispatch($post)->onQueue('notifications');

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data'    => $post->load(['author', 'media', 'tags']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FeedPostController@store failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create post. Please try again.',
            ], 500);
        }
    }

    // =========================================================================
    // UPDATE POST
    // =========================================================================

    /**
     * PUT /api/v1/feed/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $post  = FeedPost::findOrFail($id);
        $actor = $this->getActor($request);

        if (!$actor || !$this->isAuthor($actor, $post)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Same body alias normalization as store()
        if ($request->filled('body') && !$request->filled('body_en')) {
            $request->merge(['body_en' => $request->input('body')]);
        }

        $request->validate([
            'body_en'   => 'nullable|string|max:2000',
            'body_ar'   => 'nullable|string|max:2000',
            'body_ku'   => 'nullable|string|max:2000',
            'branch_id' => 'nullable|exists:branches,id',
            'tags'      => 'nullable|array|max:10',
            'tags.*'    => 'string|max:60',
        ]);

        $post->update($request->only(['body_en', 'body_ar', 'body_ku', 'branch_id']));

        if ($request->has('tags')) {
            $post->tags()->delete();
            foreach ($request->tags as $tag) {
                $post->tags()->create(['tag' => strtolower(trim($tag))]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Post updated.',
            'data'    => $post->load(['author', 'media', 'tags']),
        ]);
    }

    // =========================================================================
    // DELETE POST
    // =========================================================================

    /**
     * DELETE /api/v1/feed/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $post  = FeedPost::findOrFail($id);
        $actor = $this->getActor($request);

        if (!$actor || !$this->isAuthor($actor, $post)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted.']);
    }

    // =========================================================================
    // LIKE / UNLIKE
    // =========================================================================

    /**
     * POST /api/v1/feed/{id}/like
     */
    public function toggleLike(Request $request, string $id): JsonResponse
    {
        $post  = FeedPost::findOrFail($id);
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $result = $actor->togglePostLike($post->id);

        if ($result['action'] === 'liked') {
            $this->notifications->notifyPostLiked($post, $actor);
        }

        return response()->json([
            'success'     => true,
            'action'      => $result['action'],
            'likes_count' => $result['likes_count'],
        ]);
    }

    // =========================================================================
    // SAVE / UNSAVE
    // =========================================================================

    /**
     * POST /api/v1/feed/{id}/save
     */
    public function toggleSave(Request $request, string $id): JsonResponse
    {
        $post  = FeedPost::findOrFail($id);
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $result = $actor->togglePostSave($post->id);

        return response()->json([
            'success'     => true,
            'action'      => $result['action'],
            'saves_count' => $result['saves_count'],
        ]);
    }

    /**
     * GET /api/v1/feed/saved
     */
    public function savedPosts(Request $request): JsonResponse
    {
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $saves = $actor->feedSaves()
            ->with(['post.author', 'post.media'])
            ->latest()
            ->cursorPaginate(20);

        return response()->json(['success' => true, 'data' => $saves]);
    }

    // =========================================================================
    // REPORT
    // =========================================================================

    /**
     * POST /api/v1/feed/{id}/report
     */
    public function report(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|in:spam,fake_listing,inappropriate,misleading_price,harassment,other',
            'notes'  => 'nullable|string|max:500',
        ]);

        $post  = FeedPost::findOrFail($id);
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $exists = $post->reports()
            ->where('reporter_id', $actor->id)
            ->where('reporter_type', $actor->getMorphClass())
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported this post.',
            ], 409);
        }

        $post->reports()->create([
            'reporter_id'   => $actor->id,
            'reporter_type' => $actor->getMorphClass(),
            'reason'        => $request->reason,
            'notes'         => $request->notes,
            'status'        => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted. Our team will review it.',
        ]);
    }

    // =========================================================================
    // MEDIA
    // =========================================================================

    private function attachMedia(FeedPost $post, $file, int $index): void
    {
        $mime    = $file->getMimeType();
        $isVideo = str_starts_with($mime, 'video/');

        if ($isVideo) {
            $this->attachVideo($post, $file, $index);
        } else {
            $this->attachImage($post, $file, $index);
        }
    }

    private function attachImage(FeedPost $post, $file, int $index): void
    {
        $storagePath = $this->compressFeedImage($file);

        if (!$storagePath) {
            Log::warning('FeedPostController: image compression failed', [
                'post_id' => $post->id,
                'index'   => $index,
                'mime'    => $file->getMimeType(),
            ]);
            return;
        }

        $fullPath = storage_path('app/public/' . $storagePath);
        $url      = Storage::disk('public')->url($storagePath);

        FeedPostMedia::create([
            'post_id'         => $post->id,
            'media_type'      => 'image',
            'url'             => $url,
            'thumbnail_url'   => $url,
            'mime_type'       => 'image/jpeg',
            'file_size_bytes' => file_exists($fullPath) ? filesize($fullPath) : null,
            'sort_order'      => $index,
        ]);
    }

    private function compressFeedImage($file): ?string
    {
        try {
            $mime       = $file->getMimeType();
            $sourcePath = $file->getRealPath();

            $sourceImage = match ($mime) {
                'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
                'image/png'               => imagecreatefrompng($sourcePath),
                'image/webp'              => imagecreatefromwebp($sourcePath),
                default                   => null,
            };

            if (!$sourceImage) {
                Log::warning('compressFeedImage: unsupported mime', ['mime' => $mime]);
                return null;
            }

            $originalWidth  = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);
            $maxWidth       = 1280;
            $maxHeight      = 1280;
            $ratio          = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth       = $ratio < 1 ? (int)($originalWidth  * $ratio) : $originalWidth;
            $newHeight      = $ratio < 1 ? (int)($originalHeight * $ratio) : $originalHeight;

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($mime === 'image/png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled(
                $resizedImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            $tempPath = sys_get_temp_dir() . '/' . uniqid('feed_img_') . '.jpg';
            imagejpeg($resizedImage, $tempPath, 75);
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            $storagePath = 'feed/images/' . uniqid('feed_img_') . '.jpg';
            $fullPath    = storage_path('app/public/' . $storagePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            rename($tempPath, $fullPath);

            return $storagePath;
        } catch (\Exception $e) {
            Log::error('compressFeedImage exception', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return null;
        }
    }

    private function attachVideo(FeedPost $post, $file, int $index): void
    {
        try {
            $extension   = $file->getClientOriginalExtension();
            $filename    = uniqid('feed_video_') . '.' . $extension;
            $storagePath = 'feed/videos/' . $filename;
            $fullPath    = storage_path('app/public/' . $storagePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $file->move(dirname($fullPath), $filename);

            $url = Storage::disk('public')->url($storagePath);

            FeedPostMedia::create([
                'post_id'         => $post->id,
                'media_type'      => 'video',
                'url'             => $url,
                'thumbnail_url'   => null,
                'mime_type'       => $file->getClientMimeType(),
                'file_size_bytes' => file_exists($fullPath) ? filesize($fullPath) : null,
                'sort_order'      => $index,
            ]);
        } catch (\Exception $e) {
            Log::error('FeedPostController@attachVideo failed', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getActor(Request $request): ?object
    {
        return $request->user()
            ?? $request->user('agent')
            ?? $request->user('office')
            ?? null;
    }

    private function isAuthor(object $actor, FeedPost $post): bool
    {
        return $actor->id === $post->author_id
            && $actor->getMorphClass() === $post->author_type;
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}

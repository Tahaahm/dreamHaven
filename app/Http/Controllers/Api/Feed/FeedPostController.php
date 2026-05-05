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
     * GET /api/v1/feed/following-feed
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
    // MY POSTS
    // =========================================================================

    /**
     * GET /api/v1/feed/my-posts  (no middleware — auth handled here)
     *
     * Uses the same auth('sanctum')->user() pattern as PropertyController
     * throughout your entire app. This bypasses middleware guard issues
     * and resolves the user the same way every other endpoint does.
     */
    public function myPosts(Request $request): JsonResponse
    {
        // ── Exact same pattern as PropertyController ──────────────────────────
        $actor = auth('sanctum')->user();

        // Also check agent and office guards (same fallback chain as getActor)
        if (!$actor) $actor = auth('agent')->user();
        if (!$actor) $actor = auth('office')->user();

        if (!$actor) {
            Log::warning('FeedPostController@myPosts: unauthenticated', [
                'has_auth_header' => $request->hasHeader('Authorization'),
                'token_prefix'    => $request->bearerToken()
                    ? substr($request->bearerToken(), 0, 10) . '...'
                    : 'none',
            ]);
            return $this->unauthenticated();
        }

        $perPage = min((int) $request->input('per_page', 20), 50);

        $posts = FeedPost::where('author_id', $actor->id)
            ->where('author_type', $actor->getMorphClass())
            ->where('status', 'approved')
            ->with(['author', 'media', 'tags'])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('created_at')
            ->cursorPaginate($perPage);

        $posts->getCollection()->transform(function ($post) use ($actor) {
            $post->is_liked    = $actor->hasLikedPost($post->id);
            $post->is_saved    = $actor->hasSavedPost($post->id);
            $post->saves_count = DB::table('feed_post_saves')
                ->where('post_id', $post->id)
                ->count();
            return $post;
        });

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
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->filled('body') && !$request->filled('body_en')) {
            $request->merge(['body_en' => $request->input('body')]);
        }
        if ($request->filled('body_en')) {
            if (!$request->filled('body_ar'))
                $request->merge(['body_ar' => $request->input('body_en')]);
            if (!$request->filled('body_ku'))
                $request->merge(['body_ku' => $request->input('body_en')]);
        }

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

        if (
            !$request->filled('body_en') && !$request->filled('body_ar') &&
            !$request->filled('body_ku') && !$request->hasFile('media')
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

            if ($request->filled('tags')) {
                foreach ($request->tags as $tag)
                    $post->tags()->create(['tag' => strtolower(trim($tag))]);
            }

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $index => $file)
                    $this->attachMedia($post, $file, $index);
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

        if (!$actor || !$this->isAuthor($actor, $post))
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);

        if ($request->filled('body') && !$request->filled('body_en'))
            $request->merge(['body_en' => $request->input('body')]);
        if ($request->filled('body_en')) {
            if (!$request->filled('body_ar'))
                $request->merge(['body_ar' => $request->input('body_en')]);
            if (!$request->filled('body_ku'))
                $request->merge(['body_ku' => $request->input('body_en')]);
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
            foreach ($request->tags as $tag)
                $post->tags()->create(['tag' => strtolower(trim($tag))]);
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

        if (!$actor || !$this->isAuthor($actor, $post))
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);

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

        if ($result['action'] === 'liked')
            $this->notifications->notifyPostLiked($post, $actor);

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
        str_starts_with($file->getMimeType(), 'video/')
            ? $this->attachVideo($post, $file, $index)
            : $this->attachImage($post, $file, $index);
    }

    private function attachImage(FeedPost $post, $file, int $index): void
    {
        $storagePath = $this->compressFeedImage($file);
        if (!$storagePath) {
            Log::warning('FeedPostController: image compression failed', [
                'post_id' => $post->id,
                'index' => $index,
            ]);
            return;
        }
        $fullPath = storage_path('app/public/' . $storagePath);
        FeedPostMedia::create([
            'post_id'         => $post->id,
            'media_type'      => 'image',
            'url'             => Storage::disk('public')->url($storagePath),
            'thumbnail_url'   => Storage::disk('public')->url($storagePath),
            'mime_type'       => 'image/jpeg',
            'file_size_bytes' => file_exists($fullPath) ? filesize($fullPath) : null,
            'sort_order'      => $index,
        ]);
    }

    private function compressFeedImage($file): ?string
    {
        try {
            $mime        = $file->getMimeType();
            $sourceImage = match ($mime) {
                'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
                'image/png'               => imagecreatefrompng($file->getRealPath()),
                'image/webp'              => imagecreatefromwebp($file->getRealPath()),
                default                   => null,
            };
            if (!$sourceImage) return null;

            $ow = imagesx($sourceImage);
            $oh = imagesy($sourceImage);
            $ratio = min(1280 / $ow, 1280 / $oh);
            $nw    = $ratio < 1 ? (int)($ow * $ratio) : $ow;
            $nh    = $ratio < 1 ? (int)($oh * $ratio) : $oh;

            $canvas = imagecreatetruecolor($nw, $nh);
            if ($mime === 'image/png') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagefilledrectangle(
                    $canvas,
                    0,
                    0,
                    $nw,
                    $nh,
                    imagecolorallocatealpha($canvas, 255, 255, 255, 127)
                );
            }
            imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $nw, $nh, $ow, $oh);

            $tmp  = sys_get_temp_dir() . '/' . uniqid('feed_img_') . '.jpg';
            imagejpeg($canvas, $tmp, 75);
            imagedestroy($sourceImage);
            imagedestroy($canvas);

            $dest = 'feed/images/' . uniqid('feed_img_') . '.jpg';
            $full = storage_path('app/public/' . $dest);
            if (!is_dir(dirname($full))) mkdir(dirname($full), 0755, true);
            rename($tmp, $full);
            return $dest;
        } catch (\Exception $e) {
            Log::error('compressFeedImage', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function attachVideo(FeedPost $post, $file, int $index): void
    {
        try {
            $ext  = $file->getClientOriginalExtension();
            $name = uniqid('feed_video_') . '.' . $ext;
            $dest = 'feed/videos/' . $name;
            $full = storage_path('app/public/' . $dest);
            if (!is_dir(dirname($full))) mkdir(dirname($full), 0755, true);
            $file->move(dirname($full), $name);
            FeedPostMedia::create([
                'post_id'         => $post->id,
                'media_type'      => 'video',
                'url'             => Storage::disk('public')->url($dest),
                'thumbnail_url'   => null,
                'mime_type'       => $file->getClientMimeType(),
                'file_size_bytes' => file_exists($full) ? filesize($full) : null,
                'sort_order'      => $index,
            ]);
        } catch (\Exception $e) {
            Log::error('attachVideo failed', ['post_id' => $post->id, 'error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Resolve the current actor.
     *
     * Uses auth('sanctum')->user() as the primary lookup — exactly the same
     * pattern used in PropertyController and every other controller in this app.
     * Falls back to 'agent' and 'office' guards for non-user actors.
     */
    private function getActor(Request $request): ?object
    {
        // Primary — same as PropertyController throughout the app
        return auth('sanctum')->user()
            ?? auth('agent')->user()
            ?? auth('office')->user()
            ?? null;
    }

    private function isAuthor(object $actor, FeedPost $post): bool
    {
        return $actor->id    === $post->author_id
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
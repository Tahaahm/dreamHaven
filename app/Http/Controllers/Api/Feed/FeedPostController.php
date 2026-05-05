<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Jobs\Feed\NotifyFollowersOfNewPost;
use App\Models\Agent;
use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedPost;
use App\Models\Feed\FeedPostMedia;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\Feed\FeedNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
     * GET /api/v1/feed/my-posts  (auth)
     *
     * Uses getActorFromToken() which resolves the actor directly from the
     * Bearer token — bypasses guard resolution so it works regardless of
     * whether auth:sanctum or a custom token system is used.
     */
    public function myPosts(Request $request): JsonResponse
    {
        // Use token-based resolution — works with any auth system
        $actor = $this->getActorFromToken($request);

        if (!$actor) {
            Log::warning('FeedPostController@myPosts: could not resolve actor', [
                'auth_header' => $request->header('Authorization') ? 'present' : 'missing',
                'guard_user'   => $request->user()?->id,
                'guard_agent'  => $request->user('agent')?->id,
                'guard_office' => $request->user('office')?->id,
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
            $post->saves_count = DB::table('feed_saves')
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
            if (!$request->filled('body_ar')) {
                $request->merge(['body_ar' => $request->input('body_en')]);
            }
            if (!$request->filled('body_ku')) {
                $request->merge(['body_ku' => $request->input('body_en')]);
            }
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

            if ($request->filled('tags')) {
                foreach ($request->tags as $tag) {
                    $post->tags()->create(['tag' => strtolower(trim($tag))]);
                }
            }

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
        $isVideo ? $this->attachVideo($post, $file, $index)
            : $this->attachImage($post, $file, $index);
    }

    private function attachImage(FeedPost $post, $file, int $index): void
    {
        $storagePath = $this->compressFeedImage($file);
        if (!$storagePath) {
            Log::warning('FeedPostController: image compression failed', [
                'post_id' => $post->id,
                'index' => $index,
                'mime' => $file->getMimeType(),
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
            $ratio          = min(1280 / $originalWidth, 1280 / $originalHeight);
            $newWidth       = $ratio < 1 ? (int)($originalWidth  * $ratio) : $originalWidth;
            $newHeight      = $ratio < 1 ? (int)($originalHeight * $ratio) : $originalHeight;
            $resizedImage   = imagecreatetruecolor($newWidth, $newHeight);
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
            if (!file_exists(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
            rename($tempPath, $fullPath);
            return $storagePath;
        } catch (\Exception $e) {
            Log::error('compressFeedImage exception', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
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
            if (!file_exists(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
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
                'error' => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Standard guard-based actor resolution.
     * Works when the route middleware has already authenticated the request.
     */
    private function getActor(Request $request): ?object
    {
        return $request->user()
            ?? $request->user('agent')
            ?? $request->user('office')
            ?? null;
    }

    /**
     * Token-based actor resolution — reads the Bearer token from the
     * Authorization header and looks it up directly in the database.
     *
     * This works regardless of which guard the route middleware uses,
     * and also works on routes with no middleware at all.
     *
     * Lookup order:
     *   1. personal_access_tokens  (Sanctum)
     *   2. users.api_token         (custom token column on User)
     *   3. agents.api_token        (custom token column on Agent)
     *   4. real_estate_offices.api_token  (custom token column on Office)
     *
     * If your app uses a different column name (e.g. 'token', 'auth_token'),
     * update the column names in steps 2-4 below to match your schema.
     */
    private function getActorFromToken(Request $request): ?object
    {
        // First try standard guard resolution (works if middleware ran)
        $actor = $this->getActor($request);
        if ($actor) return $actor;

        // Extract Bearer token from Authorization header
        $header = $request->header('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) return null;
        $token = substr($header, 7);
        if (empty($token)) return null;

        // ── 1. Sanctum personal_access_tokens ─────────────────────────────
        // Sanctum stores SHA-256 hash. Token format: "{id}|{plaintext}"
        try {
            if (str_contains($token, '|')) {
                [$id, $plain] = explode('|', $token, 2);
                $pat = DB::table('personal_access_tokens')->find((int) $id);
                if ($pat && hash_equals($pat->token, hash('sha256', $plain))) {
                    [$modelType, $modelId] = [$pat->tokenable_type, $pat->tokenable_id];
                    $model = $modelType::find($modelId);
                    if ($model) return $model;
                }
            }
        } catch (\Throwable $e) {
            // personal_access_tokens table may not exist — continue
        }

        // ── 2. Custom api_token on users table ─────────────────────────────
        // Your app may store a plain token directly on the user row.
        // Try the most common column names your app might use.
        try {
            $user = User::where('api_token', $token)->first()
                ?? User::where('auth_token', $token)->first()
                ?? User::where('token', $token)->first()
                ?? User::where('remember_token', $token)->first();
            if ($user) return $user;
        } catch (\Throwable $e) {
            // Column doesn't exist — continue
        }

        // ── 3. Custom api_token on agents table ────────────────────────────
        try {
            $agent = Agent::where('api_token', $token)->first()
                ?? Agent::where('auth_token', $token)->first()
                ?? Agent::where('token', $token)->first();
            if ($agent) return $agent;
        } catch (\Throwable $e) {
            // Column doesn't exist — continue
        }

        // ── 4. Custom api_token on offices table ───────────────────────────
        try {
            $office = RealEstateOffice::where('api_token', $token)->first()
                ?? RealEstateOffice::where('auth_token', $token)->first()
                ?? RealEstateOffice::where('token', $token)->first();
            if ($office) return $office;
        } catch (\Throwable $e) {
            // Column doesn't exist — continue
        }

        Log::warning('getActorFromToken: token present but no matching actor found', [
            'token_length' => strlen($token),
            'token_prefix' => substr($token, 0, 8) . '...',
        ]);

        return null;
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
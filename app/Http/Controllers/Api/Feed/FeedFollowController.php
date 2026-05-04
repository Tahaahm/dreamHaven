<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Feed\FeedFollow;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\Feed\FeedNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedFollowController extends Controller
{
    public function __construct(
        private readonly FeedNotificationService $notifications
    ) {}

    /**
     * POST /api/v1/feed/follow
     * Follow or unfollow any account (User / Agent / Office)
     *
     * Body:
     *   followee_id   uuid    required
     *   followee_type string  required  — user | agent | office
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'followee_id'   => 'required|string',
            'followee_type' => 'required|in:user,agent,office',
        ]);

        $actor    = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $followee = $this->resolveModel($request->followee_type, $request->followee_id);

        if (!$followee) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        $result = $actor->toggleFollow($followee);

        // Notify on new follow
        if ($result['action'] === 'followed') {
            $this->notifications->notifyNewFollower($followee, $actor);
        }

        return response()->json([
            'success'         => true,
            'action'          => $result['action'],
            'followers_count' => $result['followers_count'],
        ]);
    }

    /**
     * GET /api/v1/feed/followers
     * My followers list
     */
    public function followers(Request $request): JsonResponse
    {
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $followers = FeedFollow::where('followee_id', $actor->id)
            ->where('followee_type', $actor->getMorphClass())
            ->where('status', 'accepted')
            ->with('follower')
            ->latest()
            ->cursorPaginate(30);

        return response()->json(['success' => true, 'data' => $followers]);
    }

    /**
     * GET /api/v1/feed/following
     * People / accounts I follow
     */
    public function following(Request $request): JsonResponse
    {
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $following = FeedFollow::where('follower_id', $actor->id)
            ->where('follower_type', $actor->getMorphClass())
            ->where('status', 'accepted')
            ->with('followee')
            ->latest()
            ->cursorPaginate(30);

        return response()->json(['success' => true, 'data' => $following]);
    }

    /**
     * GET /api/v1/feed/suggestions
     * Suggested accounts to follow
     */
    public function suggestions(Request $request): JsonResponse
    {
        $actor = $this->getActor($request);
        if (!$actor) return $this->unauthenticated();

        $suggestions = $actor->suggestedToFollow(10);

        return response()->json(['success' => true, 'data' => $suggestions]);
    }

    /**
     * GET /api/v1/feed/profile/{type}/{id}
     * Public profile with follow status
     */
    public function profile(Request $request, string $type, string $id): JsonResponse
    {
        $target = $this->resolveModel($type, $id);
        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        $actor      = $this->getActor($request);
        $isFollowing = $actor ? $actor->isFollowing($target) : false;
        $isMutual    = $actor ? $actor->isMutualFollowing($target) : false;

        // Recent posts from this profile
        $posts = $target->feedPosts()
            ->published()
            ->with(['media'])
            ->latest()
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'profile'          => $target,
                'is_following'     => $isFollowing,
                'is_mutual'        => $isMutual,
                'followers_count'  => $target->followers_count,
                'following_count'  => $target->following_count,
                'posts_count'      => $target->feedPosts()->count(),
                'recent_posts'     => $posts,
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveModel(string $type, string $id): ?object
    {
        return match ($type) {
            'user'   => User::find($id),
            'agent'  => Agent::find($id),
            'office' => RealEstateOffice::find($id),
            default  => null,
        };
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

<?php

namespace App\Models\Support;

use App\Models\Feed\FeedFollow;
use App\Models\Feed\FeedPost;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HasFollowers
 *
 * Add this trait to: User, Agent, RealEstateOffice
 *
 * Gives every author model:
 *   - followers()       — who follows me
 *   - following()       — who I follow
 *   - follow($target)   — follow someone
 *   - unfollow($target) — unfollow someone
 *   - isFollowing()     — check relationship
 *   - followingFeed()   — paginated feed from people I follow
 *   - followers_count / following_count — denormalized, always fast
 */
trait HasFollowers
{
    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * People / offices / agents that follow ME
     */
    public function followers(): MorphMany
    {
        return $this->morphMany(FeedFollow::class, 'followee', 'followee_type', 'followee_id')
            ->where('status', 'accepted');
    }

    /**
     * People / offices / agents that I AM following
     */
    public function following(): MorphMany
    {
        return $this->morphMany(FeedFollow::class, 'follower', 'follower_type', 'follower_id')
            ->where('status', 'accepted');
    }

    // =====================================================================
    // CORE ACTIONS
    // =====================================================================

    /**
     * Follow a target (User, Agent, or RealEstateOffice)
     *
     * Usage:
     *   $user->follow($agent);
     *   $user->follow($office);
     *   $agent->follow($user);
     *
     * Returns:
     *   'followed'        — successfully followed
     *   'already_following' — was already following
     *   'cannot_self_follow' — tried to follow themselves
     */
    public function follow(object $target): string
    {
        // Prevent self-follow
        if ($this->id === $target->id && get_class($this) === get_class($target)) {
            return 'cannot_self_follow';
        }

        $exists = FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->where('followee_id', $target->id)
            ->where('followee_type', $target->getMorphClass())
            ->exists();

        if ($exists) {
            return 'already_following';
        }

        FeedFollow::create([
            'follower_id'   => $this->id,
            'follower_type' => $this->getMorphClass(),
            'followee_id'   => $target->id,
            'followee_type' => $target->getMorphClass(),
            'status'        => 'accepted',
        ]);

        // Update denormalized counters on both sides
        $this->increment('following_count');
        $target->increment('followers_count');

        return 'followed';
    }

    /**
     * Unfollow a target
     *
     * Returns:
     *   'unfollowed'   — successfully unfollowed
     *   'not_following' — was not following
     */
    public function unfollow(object $target): string
    {
        $deleted = FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->where('followee_id', $target->id)
            ->where('followee_type', $target->getMorphClass())
            ->delete();

        if (!$deleted) {
            return 'not_following';
        }

        // Update denormalized counters on both sides
        $this->decrement('following_count');
        $target->decrement('followers_count');

        return 'unfollowed';
    }

    /**
     * Toggle follow — follow if not following, unfollow if already following
     *
     * Returns ['action' => 'followed'|'unfollowed', 'followers_count' => int]
     */
    public function toggleFollow(object $target): array
    {
        if ($this->isFollowing($target)) {
            $this->unfollow($target);
            $action = 'unfollowed';
        } else {
            $this->follow($target);
            $action = 'followed';
        }

        $target->refresh();

        return [
            'action'          => $action,
            'followers_count' => $target->followers_count,
        ];
    }

    // =====================================================================
    // CHECK METHODS
    // =====================================================================

    /**
     * Am I following this target?
     */
    public function isFollowing(object $target): bool
    {
        return FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->where('followee_id', $target->id)
            ->where('followee_type', $target->getMorphClass())
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Is this target following me?
     */
    public function isFollowedBy(object $target): bool
    {
        return FeedFollow::where('follower_id', $target->id)
            ->where('follower_type', $target->getMorphClass())
            ->where('followee_id', $this->id)
            ->where('followee_type', $this->getMorphClass())
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Do we mutually follow each other?
     */
    public function isMutualFollowing(object $target): bool
    {
        return $this->isFollowing($target) && $this->isFollowedBy($target);
    }

    // =====================================================================
    // FEED FROM FOLLOWING
    // =====================================================================

    /**
     * Get the "Following Feed" — posts only from people this model follows
     * This is the most important use of the follow system.
     *
     * Usage:
     *   $user->followingFeed()->paginate(20)
     *
     * Returns a query builder — caller controls pagination/filters
     */
    public function followingFeed()
    {
        // Get all (followee_id, followee_type) pairs this model follows
        $following = FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->where('status', 'accepted')
            ->select('followee_id', 'followee_type')
            ->get();

        if ($following->isEmpty()) {
            // Return empty query — no one followed yet
            return FeedPost::whereRaw('1 = 0');
        }

        // Build query: posts where (author_id, author_type) is in the following list
        $query = FeedPost::where('status', 'approved')
            ->orderBy('created_at', 'desc');

        $query->where(function ($q) use ($following) {
            foreach ($following as $f) {
                $q->orWhere(function ($inner) use ($f) {
                    $inner->where('author_id', $f->followee_id)
                        ->where('author_type', $f->followee_type);
                });
            }
        });

        return $query;
    }

    // =====================================================================
    // LISTS
    // =====================================================================

    /**
     * Get IDs of everyone this model is following
     * Useful for batch "isFollowing" checks on a feed list
     *
     * Returns: ['App\Models\Agent:uuid1', 'App\Models\User:uuid2', ...]
     * Usage:   $followingKeys = $user->getFollowingKeys();
     *          in_array("App\Models\Agent:{$agent->id}", $followingKeys)
     */
    public function getFollowingKeys(): array
    {
        return FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->where('status', 'accepted')
            ->get(['followee_id', 'followee_type'])
            ->map(fn($f) => "{$f->followee_type}:{$f->followee_id}")
            ->toArray();
    }

    /**
     * Suggested accounts to follow:
     * Agents and offices the user has interacted with but doesn't follow yet
     * (viewed their properties, had appointments, etc.)
     *
     * Returns a raw query — caller controls limit/pagination
     */
    public function suggestedToFollow(int $limit = 10): array
    {
        // Get who I already follow
        $alreadyFollowing = FeedFollow::where('follower_id', $this->id)
            ->where('follower_type', $this->getMorphClass())
            ->pluck('followee_id')
            ->toArray();

        // Add self to exclusion list
        $alreadyFollowing[] = $this->id;

        // Most-followed agents not yet followed — simple ranking
        $agents = \App\Models\Agent::whereNotIn('id', $alreadyFollowing)
            ->where('is_verified', true)
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->get(['id', 'agent_name', 'profile_image', 'followers_count', 'city']);

        $offices = \App\Models\RealEstateOffice::whereNotIn('id', $alreadyFollowing)
            ->where('is_verified', true)
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->get(['id', 'company_name', 'profile_image', 'followers_count', 'city']);

        return [
            'agents'  => $agents,
            'offices' => $offices,
        ];
    }
}

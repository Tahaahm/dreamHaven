<?php

namespace App\Jobs\Feed;

use App\Models\Feed\FeedPost;
use App\Services\Feed\FeedNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * NotifyFollowersOfNewPost
 *
 * Dispatched when a new post is published.
 * Runs in the background so the API response is instant
 * even if the author has thousands of followers.
 *
 * Usage in controller:
 *   NotifyFollowersOfNewPost::dispatch($post)->onQueue('notifications');
 */
class NotifyFollowersOfNewPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry up to 3 times if FCM fails
    public int $tries = 3;

    // Wait 30s between retries
    public int $backoff = 30;

    public function __construct(
        private readonly FeedPost $post
    ) {}

    public function handle(FeedNotificationService $service): void
    {
        $service->notifyFollowersOfNewPost($this->post);
    }
}

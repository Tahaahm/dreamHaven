<?php

namespace App\Services\Chat;

use App\Models\Agent;
use App\Models\Chat\ChatConversation;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Sends FCM push notifications to all participants of a conversation
 * except the sender. Reuses the existing FCMNotificationService.
 */
class ChatNotificationService
{
    public function __construct(private FCMNotificationService $fcm) {}

    /**
     * Notify all participants (except sender) about a new message.
     *
     * @param ChatConversation $conversation
     * @param string           $senderId
     * @param string           $senderMorphType   e.g. App\Models\User
     * @param string           $senderName        display name for the notification title
     * @param string           $messageText       preview text
     * @param string           $messageType       text|image|property
     */
    public function notifyNewMessage(
        ChatConversation $conversation,
        string $senderId,
        string $senderMorphType,
        string $senderName,
        string $messageText,
        string $messageType
    ): void {
        $conversation->loadMissing('participants');

        // Build notification title
        $title = $conversation->type === 'group'
            ? ($conversation->name ?? 'Group Chat') . ' — ' . $senderName
            : $senderName;

        // Build notification body
        $body = match ($messageType) {
            'image'    => '📷 ' . $senderName . ' sent a photo',
            'property' => '🏠 ' . $senderName . ' shared a property',
            default    => $messageText,
        };

        // Payload for deep-link in Flutter
        $data = [
            'type'            => 'new_message',
            'conversation_id' => $conversation->id,
            'message_type'    => $messageType,
        ];

        foreach ($conversation->participants as $participant) {
            // Skip sender
            if (
                $participant->participant_id === $senderId &&
                $participant->participant_type === $senderMorphType
            ) {
                continue;
            }

            // Skip participants who have left
            if ($participant->hasLeft()) {
                continue;
            }

            // Skip muted participants
            if ($participant->is_muted) {
                continue;
            }

            $this->sendToParticipant(
                $participant->participant_id,
                $participant->participant_type,
                $title,
                $body,
                $data
            );
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function sendToParticipant(
        string $participantId,
        string $participantType,
        string $title,
        string $body,
        array $data
    ): void {
        try {
            $model = $this->resolveModel($participantId, $participantType);
            if (!$model) {
                return;
            }

            $tokens = $model->getFCMTokens();
            if (empty($tokens)) {
                return;
            }

            foreach ($tokens as $token) {
                $this->fcm->sendToToken($token, $title, $body, $data);
            }
        } catch (\Throwable $e) {
            Log::warning('ChatNotificationService: failed to send FCM', [
                'participant_id'   => $participantId,
                'participant_type' => $participantType,
                'error'            => $e->getMessage(),
            ]);
        }
    }

    private function resolveModel(string $id, string $morphType): mixed
    {
        return match ($morphType) {
            'App\\Models\\Agent'            => Agent::find($id),
            'App\\Models\\RealEstateOffice' => RealEstateOffice::find($id),
            default                         => User::find($id),
        };
    }
}

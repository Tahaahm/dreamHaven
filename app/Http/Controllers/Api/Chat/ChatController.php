<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatConversation;
use App\Models\Chat\ChatMedia;
use App\Models\Chat\ChatParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════════
    //  HELPERS — resolve the current authenticated actor across all guards
    // ════════════════════════════════════════════════════════════════════════════

    /**
     * Returns [model_instance, morph_type_string] for the authenticated actor.
     * Checks sanctum → agent → office in order.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    private function resolveActor(Request $request): array
    {
        foreach (['sanctum', 'agent', 'office'] as $guard) {
            $user = auth($guard)->user();
            if ($user) {
                return [$user, get_class($user)];
            }
        }
        abort(401, 'Unauthenticated.');
    }

    /**
     * Build the display name for an actor model.
     */
    private function actorName(mixed $actor): string
    {
        return $actor->username
            ?? $actor->agent_name
            ?? $actor->company_name
            ?? 'Unknown';
    }

    /**
     * Build the avatar URL for an actor model.
     */
    private function actorAvatar(mixed $actor): ?string
    {
        $raw = $actor->photo_image
            ?? $actor->profile_image
            ?? null;

        if (!$raw || empty($raw)) return null;

        // Already a full URL — return as-is
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        // Build full public storage URL
        return Storage::disk('public')->url($raw);
    }

    /**
     * Resolve role string from morph type.
     */
    private function roleFromMorphType(string $morphType): string
    {
        return match ($morphType) {
            'App\\Models\\Agent'            => 'agent',
            'App\\Models\\RealEstateOffice' => 'office',
            default                         => 'user',
        };
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  LIST CONVERSATIONS  — GET /api/v1/chat/conversations
    // ════════════════════════════════════════════════════════════════════════════

    public function index(Request $request): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        $conversations = ChatConversation::with([
            'participants.participant',
        ])
            ->forParticipant($actor->id, $morphType)
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $data = $conversations->getCollection()->map(function (ChatConversation $conv) use ($actor, $morphType) {
            // Find this actor's participant row for unread count
            $myParticipant = $conv->participants->first(
                fn($p) => $p->participant_id === $actor->id && $p->participant_type === $morphType
            );

            // Build other participants list (for DMs: the other person)
            // In index() method — update the participants map:
            $others = $conv->participants
                ->filter(fn($p) => !($p->participant_id === $actor->id && $p->participant_type === $morphType))
                ->map(fn($p) => [
                    'id'     => $p->participant_id,
                    'type'   => $this->roleFromMorphType($p->participant_type),
                    'name'   => $p->participant ? $this->actorName($p->participant) : 'Unknown',
                    'avatar' => $p->participant ? $this->actorAvatar($p->participant) : null,
                    'role'   => $p->role,
                    'is_me'  => false, // ← these are always "others"
                ])
                ->values();

            // Days until expiry warning (show banner when ≤ 5 days)
            $daysUntilExpiry = $conv->expires_at
                ? (int) now()->diffInDays($conv->expires_at, false)
                : null;

            return [
                'id'                        => $conv->id,
                'type'                      => $conv->type,
                'name'                      => $conv->type === 'group'
                    ? $conv->name
                    : ($others->first()['name'] ?? 'Chat'),
                'avatar'                    => $conv->type === 'group'
                    ? $conv->avatar
                    : ($others->first()['avatar'] ?? null),
                'participants'              => $others,
                'last_message'              => $conv->last_message,
                'last_message_type'         => $conv->last_message_type,
                'last_message_at'           => $conv->last_message_at?->toISOString(),
                'unread_count'              => $myParticipant?->unread_count ?? 0,
                'property_id'              => $conv->property_id,
                'expires_at'               => $conv->expires_at?->toISOString(),
                'days_until_expiry'        => $daysUntilExpiry,
                'show_expiry_warning'      => $daysUntilExpiry !== null && $daysUntilExpiry <= 5,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $conversations->currentPage(),
                'last_page'    => $conversations->lastPage(),
                'total'        => $conversations->total(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  CREATE / FIND DIRECT CONVERSATION  — POST /api/v1/chat/conversations
    // ════════════════════════════════════════════════════════════════════════════

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'                   => 'required|in:direct,group',
            // Direct
            'recipient_id'           => 'required_if:type,direct|string',
            'recipient_type'         => 'required_if:type,direct|in:user,agent,office',
            // Group
            'name'                   => 'required_if:type,group|string|max:100',
            'participant_ids'        => 'required_if:type,group|array|min:1',
            'participant_ids.*'      => 'string',
            'participant_types'      => 'required_if:type,group|array|min:1',
            'participant_types.*'    => 'in:user,agent,office',
            // Optional property context
            'property_id'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        [$actor, $morphType] = $this->resolveActor($request);

        try {
            DB::beginTransaction();

            if ($request->type === 'direct') {
                $recipientMorphType = ChatConversation::morphTypeFor($request->recipient_type);

                // Return existing conversation if it already exists
                $existing = ChatConversation::findDirect(
                    $actor->id,
                    $morphType,
                    $request->recipient_id,
                    $recipientMorphType
                );

                if ($existing) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'data'    => ['id' => $existing->id, 'is_existing' => true],
                    ]);
                }

                // Create new direct conversation
                $conversation = ChatConversation::create([
                    'type'           => 'direct',
                    'created_by'     => $actor->id,
                    'created_by_type' => $morphType,
                    'property_id'    => $request->property_id,
                    'expires_at'     => now()->addDays(30),
                ]);

                // Add both participants
                $this->addParticipant($conversation->id, $actor->id, $morphType, 'admin');
                $this->addParticipant($conversation->id, $request->recipient_id, $recipientMorphType, 'member');
            } else {
                // Group conversation
                $conversation = ChatConversation::create([
                    'type'           => 'group',
                    'name'           => $request->name,
                    'created_by'     => $actor->id,
                    'created_by_type' => $morphType,
                    'property_id'    => $request->property_id,
                    'expires_at'     => now()->addDays(30),
                ]);

                // Add creator as admin
                $this->addParticipant($conversation->id, $actor->id, $morphType, 'admin');

                // Add other participants
                foreach ($request->participant_ids as $index => $pid) {
                    $pType = ChatConversation::morphTypeFor($request->participant_types[$index] ?? 'user');
                    if ($pid !== $actor->id) {
                        $this->addParticipant($conversation->id, $pid, $pType, 'member');
                    }
                }
            }

            DB::commit();

            // Collect Firebase UIDs for all participants
            $participantUids = collect($conversation->participants)
                ->map(fn($p) => $p->participant_id) // participant_id IS the Firebase UID
                ->values()
                ->toArray();

            $this->syncToFirestore($conversation, $participantUids);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'          => $conversation->id,
                    'type'        => $conversation->type,
                    'is_existing' => false,
                    'expires_at'  => $conversation->expires_at?->toISOString(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ChatController@store failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create conversation.'], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  GET SINGLE CONVERSATION  — GET /api/v1/chat/conversations/{id}
    // ════════════════════════════════════════════════════════════════════════════

    public function show(Request $request, string $conversationId): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        $conversation = ChatConversation::with(['participants.participant'])
            ->findOrFail($conversationId);

        // Security: must be a participant
        if (!$conversation->hasParticipant($actor->id, $morphType)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $daysUntilExpiry = $conversation->expires_at
            ? (int) now()->diffInDays($conversation->expires_at, false)
            : null;

        $participants = $conversation->participants->map(fn($p) => [
            'id'     => $p->participant_id,
            'type'   => $this->roleFromMorphType($p->participant_type),
            'name'   => $p->participant ? $this->actorName($p->participant) : 'Unknown',
            'avatar' => $p->participant ? $this->actorAvatar($p->participant) : null,
            'role'   => $p->role,
            'is_me'  => $p->participant_id === $actor->id && $p->participant_type === $morphType,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                   => $conversation->id,
                'type'                 => $conversation->type,
                'name'                 => $conversation->name,
                'participants'         => $participants,
                'property_id'         => $conversation->property_id,
                'expires_at'          => $conversation->expires_at?->toISOString(),
                'days_until_expiry'   => $daysUntilExpiry,
                'show_expiry_warning' => $daysUntilExpiry !== null && $daysUntilExpiry <= 5,
                'last_message_at'     => $conversation->last_message_at?->toISOString(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  MARK CONVERSATION AS READ  — POST /api/v1/chat/conversations/{id}/read
    // ════════════════════════════════════════════════════════════════════════════

    public function markRead(Request $request, string $conversationId): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        $participant = ChatParticipant::where('conversation_id', $conversationId)
            ->where('participant_id', $actor->id)
            ->where('participant_type', $morphType)
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Not a participant.'], 403);
        }

        $participant->markAsRead();

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  NOTIFY LAST MESSAGE  — POST /api/v1/chat/conversations/{id}/notify
    //  Called by Flutter after a message is written to Firestore.
    //  Updates last-message preview + resets expiry + increments unread counts.
    // ════════════════════════════════════════════════════════════════════════════

    public function notifyMessage(Request $request, string $conversationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text'         => 'required|string|max:1000',
            'type'         => 'required|in:text,image,property',
            'sender_id'    => 'required|string',
            'sender_type'  => 'required|in:user,agent,office',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $conversation = ChatConversation::with('participants.participant')
            ->findOrFail($conversationId);
        $morphType    = ChatConversation::morphTypeFor($request->sender_type);

        if (!$conversation->hasParticipant($request->sender_id, $morphType)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        // Update preview + reset 30-day expiry
        $conversation->updateLastMessage(
            $request->text,
            $request->type,
            $request->sender_id,
            $morphType
        );

        // Increment unread for everyone except sender
        $conversation->incrementUnreadForOthers($request->sender_id, $morphType);

        // ── Send FCM push notifications to all other participants ──
        $this->sendChatNotifications(
            conversation: $conversation,
            senderId: $request->sender_id,
            senderType: $morphType,
            text: $request->text,
            messageType: $request->type,
        );

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  UPLOAD MEDIA  — POST /api/v1/chat/media/upload
    // ════════════════════════════════════════════════════════════════════════════

    public function uploadMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id'      => 'required|string|exists:chat_conversations,id',
            'file'                 => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf',
            'firestore_message_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        [$actor, $morphType] = $this->resolveActor($request);

        $conversation = ChatConversation::findOrFail($request->conversation_id);

        if (!$conversation->hasParticipant($actor->id, $morphType)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $file     = $request->file('file');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = 'chat/' . $conversation->id . '/' . $fileName;

            Storage::disk('public')->put($path, file_get_contents($file));

            $url = Storage::disk('public')->url($path);

            $isImage = str_starts_with($file->getMimeType(), 'image/');

            $media = ChatMedia::create([
                'conversation_id'      => $conversation->id,
                'firestore_message_id' => $request->firestore_message_id,
                'uploader_id'          => $actor->id,
                'uploader_type'        => $morphType,
                'disk'                 => 'public',
                'path'                 => $path,
                'url'                  => $url,
                'mime_type'            => $file->getMimeType(),
                'size_bytes'           => $file->getSize(),
                'type'                 => $isImage ? 'image' : 'file',
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'   => $media->id,
                    'url'  => $url,
                    'type' => $media->type,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatController@uploadMedia failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Upload failed.'], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  TOTAL UNREAD COUNT  — GET /api/v1/chat/unread-count
    //  Used for the badge on the chat icon in CompactAppBar.
    // ════════════════════════════════════════════════════════════════════════════

    public function unreadCount(Request $request): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        $total = ChatParticipant::where('participant_id', $actor->id)
            ->where('participant_type', $morphType)
            ->whereNull('left_at')
            ->sum('unread_count');

        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => (int) $total],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  ADD PARTICIPANT TO GROUP  — POST /api/v1/chat/conversations/{id}/participants
    // ════════════════════════════════════════════════════════════════════════════

    public function addParticipantToGroup(Request $request, string $conversationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participant_id'   => 'required|string',
            'participant_type' => 'required|in:user,agent,office',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        [$actor, $morphType] = $this->resolveActor($request);

        $conversation = ChatConversation::findOrFail($conversationId);

        if ($conversation->type !== 'group') {
            return response()->json(['success' => false, 'message' => 'Not a group conversation.'], 422);
        }

        // Only group admins can add participants
        $myParticipant = ChatParticipant::where('conversation_id', $conversationId)
            ->where('participant_id', $actor->id)
            ->where('participant_type', $morphType)
            ->first();

        if (!$myParticipant || !$myParticipant->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only admins can add participants.'], 403);
        }

        $pMorphType = ChatConversation::morphTypeFor($request->participant_type);

        if ($conversation->hasParticipant($request->participant_id, $pMorphType)) {
            return response()->json(['success' => false, 'message' => 'Already a participant.'], 422);
        }

        $this->addParticipant($conversation->id, $request->participant_id, $pMorphType, 'member');

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  LEAVE GROUP  — DELETE /api/v1/chat/conversations/{id}/leave
    // ════════════════════════════════════════════════════════════════════════════

    public function leaveGroup(Request $request, string $conversationId): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        $participant = ChatParticipant::where('conversation_id', $conversationId)
            ->where('participant_id', $actor->id)
            ->where('participant_type', $morphType)
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Not a participant.'], 403);
        }

        $participant->update(['left_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPER
    // ════════════════════════════════════════════════════════════════════════════

    private function addParticipant(
        string $conversationId,
        string $participantId,
        string $participantType,
        string $role = 'member'
    ): ChatParticipant {
        return ChatParticipant::create([
            'conversation_id'  => $conversationId,
            'participant_id'   => $participantId,
            'participant_type' => $participantType,
            'role'             => $role,
            'unread_count'     => 0,
        ]);
    }

    public function firebaseToken(Request $request): JsonResponse
    {
        [$actor, $morphType] = $this->resolveActor($request);

        try {
            // Use the same FirebaseAuthService that already works in your project
            $firebaseAuth = app(\App\Services\FirebaseAuthService::class);

            $customToken = $firebaseAuth->generateCustomToken($actor->id, [
                'role' => $this->roleFromMorphType($morphType),
            ]);

            if (!$customToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not generate token.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data'    => ['token' => $customToken],
            ]);
        } catch (\Throwable $e) {
            Log::error('firebaseToken failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Could not generate token.',
            ], 500);
        }
    }

    // After DB::commit() in the store method, sync to Firestore
    private function syncToFirestore(ChatConversation $conversation, array $participantUids): void
    {
        try {
            $firebaseAuth = app(\App\Services\FirebaseAuthService::class);
            $firestore = $firebaseAuth->getFirestore(); // use your existing service

            $firestore->collection('conversations')
                ->document($conversation->id)
                ->set([
                    'type'            => $conversation->type,
                    'participant_uids' => $participantUids,
                    'created_by'      => $conversation->created_by,
                    'created_at'      => new \Google\Cloud\Core\Timestamp(
                        new \DateTime($conversation->created_at)
                    ),
                    'last_message'    => '',
                    'last_message_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                    'property_id'     => $conversation->property_id,
                ]);
        } catch (\Throwable $e) {
            Log::error('Firestore sync failed', ['error' => $e->getMessage()]);
        }
    }

    private function sendChatNotifications(
        ChatConversation $conversation,
        string           $senderId,
        string           $senderType,
        string           $text,
        string           $messageType,
    ): void {
        try {
            // Get sender name for notification title
            $senderParticipant = $conversation->participants->first(
                fn($p) => $p->participant_id === $senderId
                    && $p->participant_type === $senderType
            );
            $senderName = $senderParticipant?->participant
                ? $this->actorName($senderParticipant->participant)
                : 'New Message';

            // Notification body based on message type
            $body = match ($messageType) {
                'image'    => '📷 Photo',
                'property' => '🏠 Property',
                default    => $text,
            };

            // Conversation title
            $title = $conversation->type === 'group'
                ? ($conversation->name ?? 'Group Chat')
                : $senderName;

            // Send to all participants EXCEPT the sender
            foreach ($conversation->participants as $participant) {
                // Skip sender
                if (
                    $participant->participant_id === $senderId
                    && $participant->participant_type === $senderType
                ) {
                    continue;
                }

                // Skip if they left
                if ($participant->left_at !== null) continue;

                // Skip if muted
                if ($participant->is_muted) continue;

                $recipient = $participant->participant;
                if (!$recipient) continue;

                // Get FCM token — check all possible field names
                $fcmToken = $recipient->fcm_token
                    ?? $recipient->device_token
                    ?? $recipient->push_token
                    ?? null;

                if (!$fcmToken) continue;

                // Determine recipient language for localization
                $lang = $recipient->language
                    ?? $recipient->preferred_language
                    ?? 'en';

                // Send via your existing NotificationService
                $this->sendFcmNotification(
                    token: $fcmToken,
                    title: $title,
                    body: $body,
                    lang: $lang,
                    conversationId: $conversation->id,
                    senderId: $senderId,
                );
            }
        } catch (\Throwable $e) {
            Log::error('sendChatNotifications failed', ['error' => $e->getMessage()]);
            // Non-critical — don't bubble up
        }
    }

    private function sendFcmNotification(
        string $token,
        string $title,
        string $body,
        string $lang,
        string $conversationId,
        string $senderId,
    ): void {
        try {
            $factory    = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(config('firebase.service_account_path'));
            $messaging  = $factory->createMessaging();

            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget(
                'token',
                $token
            )
                ->withNotification([
                    'title' => $title,
                    'body'  => $body,
                ])
                ->withData([
                    'type'            => 'chat_message',
                    'conversation_id' => $conversationId,
                    'sender_id'       => $senderId,
                    'click_action'    => 'FLUTTER_NOTIFICATION_CLICK',
                ])
                ->withAndroidConfig([
                    'priority'     => 'high',
                    'notification' => [
                        'channel_id' => 'chat_messages',
                        'sound'      => 'default',
                    ],
                ])
                ->withApnsConfig([
                    'headers' => ['apns-priority' => '10'],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ]);

            $messaging->send($message);

            Log::info('Chat FCM sent', [
                'conversation_id' => $conversationId,
                'token_prefix'    => substr($token, 0, 20),
            ]);
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::warning('FCM send failed', ['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('FCM unexpected error', ['error' => $e->getMessage()]);
        }
    }
}
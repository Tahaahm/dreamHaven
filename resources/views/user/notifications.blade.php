<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Notifications - Dream Mulk</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #303b97;
            --primary-dark: #1a225a;
            --accent: #d4af37;       /* Gold */
            --accent-glow: rgba(212, 175, 55, 0.3);
            --bg-dark: #0a0e27;
            --text-light: #ffffff;
            --text-muted: #a0aec0;

            /* Glassmorphism Variables */
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            padding-top: 110px; /* Space for fixed navbar */
            background-image:
                radial-gradient(circle at 10% 20%, rgba(48, 59, 151, 0.2) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 20%);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        /* --- Page Header --- */
        .page-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
        }

        /* --- Stats Grid --- */
        .notifications-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
            box-shadow: 0 10px 30px var(--accent-glow);
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- Notification List --- */
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .notification-card {
            background: rgba(30, 35, 66, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            padding: 25px;
        }

        /* Unread Indicator (Left Border) */
        .notification-card.unread {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.05) 0%, rgba(30, 35, 66, 0.6) 100%);
            border-left: 4px solid var(--accent);
        }

        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            border-color: rgba(255,255,255,0.2);
        }

        .notification-header {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        /* --- Icons --- */
        .notification-icon {
            width: 55px;
            height: 55px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .notification-icon.property { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2)); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        .notification-icon.appointment { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2)); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        .notification-icon.system { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(102, 126, 234, 0.2)); color: #818cf8; border-color: rgba(102, 126, 234, 0.3); }
        .notification-icon.promotion { background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(212, 175, 55, 0.2)); color: var(--accent); border-color: var(--accent); }
        .notification-icon.alert { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.2)); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }

        /* --- Content --- */
        .notification-content { flex: 1; }

        .notification-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .notification-message {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .notification-time i { color: var(--accent); margin-right: 5px; }

        /* --- Badges --- */
        .notification-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-high { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-medium { background: rgba(249, 115, 22, 0.15); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.3); }
        .badge-low { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        /* --- Actions --- */
        .notification-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
            align-items: center;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #b8941f 100%);
            color: #000;
            font-weight: 600;
        }
        .btn-primary:hover {
            box-shadow: 0 0 15px var(--accent-glow);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.05);
            color: var(--text-light);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--text-light);
        }

        .btn-danger {
            background: transparent;
            color: #f87171;
            border: 1px solid transparent;
            margin-left: auto;
        }
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* --- Empty State --- */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--glass-bg);
            border-radius: 20px;
            border: 1px dashed rgba(255,255,255,0.1);
        }
        .empty-state i {
            font-size: 4rem;
            color: var(--accent);
            opacity: 0.5;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .notification-header { flex-direction: column; }
            .notification-actions { flex-direction: column; align-items: stretch; }
            .btn-danger { margin-left: 0; text-align: center; justify-content: center; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="container">
        <div class="page-header">
            <h1>My Notifications</h1>
            <p>Stay updated with all your important alerts</p>
        </div>

        @php
            $unreadCount = $notifications->where('is_read', false)->count();
            $totalCount = $notifications->count();
        @endphp

        <div class="notifications-stats">
            <div class="stat-card">
                <div class="stat-number">{{ $totalCount }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $unreadCount }}</div>
                <div class="stat-label">Unread</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $totalCount - $unreadCount }}</div>
                <div class="stat-label">Read</div>
            </div>
        </div>

        <div class="notifications-list">
            @if($notifications->isEmpty())
                <div class="empty-state">
                    <i class="far fa-bell-slash"></i>
                    <h3>No Notifications Yet</h3>
                    <p style="color: #a0aec0;">You are all caught up! Check back later.</p>
                </div>
            @else
                @foreach($notifications as $notification)
                    <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}" id="notification-{{ $notification->id }}">

                        <div class="notification-header">
                            <div class="notification-icon {{ $notification->type }}">
                                @switch($notification->type)
                                    @case('property') <i class="fas fa-home"></i> @break
                                    @case('appointment') <i class="far fa-calendar-check"></i> @break
                                    @case('system') <i class="fas fa-info-circle"></i> @break
                                    @case('promotion') <i class="fas fa-tag"></i> @break
                                    @case('alert') <i class="fas fa-exclamation-triangle"></i> @break
                                    @default <i class="far fa-bell"></i>
                                @endswitch
                            </div>

                            <div class="notification-content">
                                <h3 class="notification-title">{{ $notification->title }}</h3>
                                <p class="notification-message">{{ $notification->message }}</p>

                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <i class="far fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($notification->sent_at)->diffForHumans() }}
                                    </span>

                                    @if($notification->priority)
                                        <span class="notification-badge badge-{{ $notification->priority }}">
                                            {{ ucfirst($notification->priority) }} Priority
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="notification-actions">
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="btn btn-primary">
                                    {{ $notification->action_text ?? 'View Details' }} <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif

                            @if(!$notification->is_read)
                                <button class="btn btn-secondary mark-read-btn" data-id="{{ $notification->id }}">
                                    <i class="fas fa-check"></i> Mark as Read
                                </button>
                            @endif

                            <button class="btn btn-danger delete-btn" data-id="{{ $notification->id }}">
                                <i class="far fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Mark as read functionality
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const notificationId = this.getAttribute('data-id');
                    const card = document.getElementById('notification-' + notificationId);

                    fetch(`/notifications/read/${notificationId}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            card.classList.remove('unread');
                            this.remove(); // Remove the button itself

                            // Update stats (simple decrement for UI feeling)
                            const unreadStat = document.querySelector('.notifications-stats .stat-card:nth-child(2) .stat-number');
                            const readStat = document.querySelector('.notifications-stats .stat-card:nth-child(3) .stat-number');

                            let unreadVal = parseInt(unreadStat.textContent);
                            let readVal = parseInt(readStat.textContent);

                            if (unreadVal > 0) {
                                unreadStat.textContent = unreadVal - 1;
                                readStat.textContent = readVal + 1;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update notification');
                    });
                });
            });

            // Delete functionality
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Are you sure you want to remove this notification?')) {
                        return;
                    }

                    const notificationId = this.getAttribute('data-id');
                    const card = document.getElementById('notification-' + notificationId);

                    fetch(`/notifications/delete/${notificationId}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            // Fade out animation
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'translateX(50px)';

                            setTimeout(() => {
                                card.remove();
                                // Check if list is empty to reload for empty state
                                if (document.querySelectorAll('.notification-card').length === 0) {
                                    location.reload();
                                }
                            }, 300);

                            // Update Total Count
                            const totalStat = document.querySelector('.notifications-stats .stat-card:first-child .stat-number');
                            let totalVal = parseInt(totalStat.textContent);
                            if (totalVal > 0) totalStat.textContent = totalVal - 1;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete notification');
                    });
                });
            });
        });
    </script>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Notifications - Dream Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --primary-light: #764ba2;
            --primary-dark: #5568d3;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(102, 126, 234, 0.08);
            --shadow-md: 0 4px 16px rgba(102, 126, 234, 0.12);
            --shadow-lg: 0 8px 32px rgba(102, 126, 234, 0.16);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.6;
            padding-top: 80px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--text-gray);
        }

        .notifications-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notification-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .notification-card.unread {
            border-left-color: var(--primary-color);
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, white 10%);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 20px 10px 20px;
            gap: 15px;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-icon.property {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.2) 100%);
            color: #22c55e;
        }

        .notification-icon.appointment {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.2) 100%);
            color: #3b82f6;
        }

        .notification-icon.system {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: var(--primary-color);
        }

        .notification-icon.promotion {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(249, 115, 22, 0.2) 100%);
            color: #f97316;
        }

        .notification-icon.alert {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.2) 100%);
            color: #ef4444;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .notification-message {
            font-size: 0.95rem;
            color: var(--text-gray);
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .notification-time {
            font-size: 0.813rem;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notification-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-high {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .badge-medium {
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }

        .badge-low {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .notification-actions {
            display: flex;
            gap: 10px;
            padding: 0 20px 20px 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-gray);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .empty-state i {
            font-size: 5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-gray);
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .notifications-stats {
                grid-template-columns: 1fr;
            }

            .notification-header {
                flex-direction: column;
            }

            .notification-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
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
                    <i class="fas fa-bell"></i>
                    <h3>No Notifications Yet</h3>
                    <p>You don't have any notifications at the moment</p>
                </div>
            @else
                @foreach($notifications as $notification)
                    <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}" id="notification-{{ $notification->id }}">
                        <div class="notification-header">
                            <div class="notification-icon {{ $notification->type }}">
                                @switch($notification->type)
                                    @case('property')
                                        <i class="fas fa-home"></i>
                                        @break
                                    @case('appointment')
                                        <i class="fas fa-calendar-check"></i>
                                        @break
                                    @case('system')
                                        <i class="fas fa-info-circle"></i>
                                        @break
                                    @case('promotion')
                                        <i class="fas fa-tag"></i>
                                        @break
                                    @case('alert')
                                        <i class="fas fa-exclamation-triangle"></i>
                                        @break
                                    @default
                                        <i class="fas fa-bell"></i>
                                @endswitch
                            </div>

                            <div class="notification-content">
                                <h3 class="notification-title">{{ $notification->title }}</h3>
                                <p class="notification-message">{{ $notification->message }}</p>
                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <i class="fas fa-clock"></i>
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
                                    <i class="fas fa-arrow-right"></i>
                                    {{ $notification->action_text ?? 'View Details' }}
                                </a>
                            @endif

                            @if(!$notification->is_read)
                                <button class="btn btn-secondary mark-read-btn" data-id="{{ $notification->id }}">
                                    <i class="fas fa-check"></i>
                                    Mark as Read
                                </button>
                            @endif

                            <button class="btn btn-danger delete-btn" data-id="{{ $notification->id }}">
                                <i class="fas fa-trash"></i>
                                Delete
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
                            this.remove();

                            // Update unread count
                            const unreadStat = document.querySelector('.notifications-stats .stat-card:nth-child(2) .stat-number');
                            const currentCount = parseInt(unreadStat.textContent);
                            if (currentCount > 0) {
                                unreadStat.textContent = currentCount - 1;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to mark notification as read');
                    });
                });
            });

            // Delete functionality
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Are you sure you want to delete this notification?')) {
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
                            card.style.opacity = '0';
                            card.style.transform = 'translateX(100px)';
                            setTimeout(() => {
                                card.remove();

                                // Check if no notifications left
                                if (document.querySelectorAll('.notification-card').length === 0) {
                                    location.reload();
                                }
                            }, 300);

                            // Update total count
                            const totalStat = document.querySelector('.notifications-stats .stat-card:first-child .stat-number');
                            const currentCount = parseInt(totalStat.textContent);
                            if (currentCount > 0) {
                                totalStat.textContent = currentCount - 1;
                            }
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Appointments - Dream Mulk</title>

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
            max-width: 1100px;
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
        .appointments-stats {
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

        /* --- Appointments List --- */
        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .appointment-card {
            background: rgba(30, 35, 66, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        /* Hover Glow Effect */
        .appointment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            border-color: rgba(255,255,255,0.2);
        }

        /* Status Indicators (Left Border Strip) */
        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 4px;
            background: var(--text-muted); /* Default */
        }
        .appointment-card.pending::before { background: var(--accent); }
        .appointment-card.confirmed::before { background: #10b981; } /* Emerald */
        .appointment-card.completed::before { background: #3b82f6; } /* Blue */
        .appointment-card.cancelled::before { background: #ef4444; } /* Red */

        .appointment-header {
            display: flex;
            padding: 30px;
            gap: 30px;
            align-items: flex-start;
        }

        /* --- Date Badge --- */
        .appointment-date-badge {
            min-width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--primary) 0%, #101436 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            flex-shrink: 0;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .date-day {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-light);
            line-height: 1;
        }

        .date-month {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent);
            margin-top: 5px;
        }

        /* --- Info Section --- */
        .appointment-info { flex: 1; }

        .appointment-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--text-light);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Status Badge inside Title */
        .status-badge {
            font-family: 'Poppins', sans-serif;
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .status-pending { background: rgba(212, 175, 55, 0.15); color: var(--accent); border: 1px solid rgba(212, 175, 55, 0.3); }
        .status-confirmed { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-completed { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
        }

        .detail-item i {
            color: var(--accent);
            width: 20px;
            text-align: center;
        }

        /* --- Actions --- */
        .appointment-actions {
            padding: 0 30px 30px 30px; /* Left/Right align with header padding */
            display: flex;
            gap: 15px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
            margin-top: 5px;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 0.9rem;
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
            color: #000; /* Dark text on gold for readability */
            font-weight: 600;
        }
        .btn-primary:hover {
            box-shadow: 0 0 15px var(--accent-glow);
            transform: translateY(-2px);
            color: #000;
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
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            margin-left: auto; /* Push to right */
        }
        .btn-danger:hover {
            background: #ef4444;
            color: white;
        }

        /* --- Empty State --- */
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            background: var(--glass-bg);
            border-radius: 24px;
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

        /* --- Modal Styling --- */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }

        .modal-content {
            background: #1a1f3c; /* Solid dark color for readability */
            border: 1px solid var(--accent);
            border-radius: 24px;
            padding: 40px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            position: relative;
        }

        .modal-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 15px;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        /* Dark mode inputs usually have white calendar icons, this fixes webkit */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.6;
            cursor: pointer;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .modal-actions .btn { flex: 1; justify-content: center; }

        /* Responsive */
        @media (max-width: 768px) {
            .appointment-header { flex-direction: column; }
            .appointment-date-badge { flex-direction: row; width: 100%; height: 60px; gap: 15px; }
            .date-day { font-size: 1.5rem; }
            .date-month { margin-top: 0; }
            .appointment-actions { flex-direction: column; }
            .btn-danger { margin-left: 0; }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="container">
        <div class="page-header">
            <h1>My Appointments</h1>
            <p>Manage your exclusive viewing schedule</p>
        </div>

        @php
            $totalCount = $appointments->count();
            $pendingCount = $appointments->where('status', 'pending')->count();
            $confirmedCount = $appointments->where('status', 'confirmed')->count();
            $completedCount = $appointments->where('status', 'completed')->count();
        @endphp

        <div class="appointments-stats">
            <div class="stat-card">
                <div class="stat-number">{{ $totalCount }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $pendingCount }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $confirmedCount }}</div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $completedCount }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="appointments-list">
            @if($appointments->isEmpty())
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Appointments Yet</h3>
                    <p style="color: #a0aec0;">Your viewing schedule is currently empty.</p>
                </div>
            @else
                @foreach($appointments as $appointment)
                    <div class="appointment-card {{ $appointment->status }}" id="appointment-{{ $appointment->id }}">

                        <div class="appointment-header">
                            <div class="appointment-date-badge">
                                <div class="date-day">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d') }}</div>
                                <div class="date-month">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M Y') }}</div>
                            </div>

                            <div class="appointment-info">
                                <h3 class="appointment-title">
                                    {{ ucfirst($appointment->type ?? 'Viewing') }}
                                    <span class="status-badge status-{{ $appointment->status }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </h3>

                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <i class="far fa-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                                    </div>

                                    @if($appointment->agent)
                                        <div class="detail-item">
                                            <i class="far fa-user"></i>
                                            <span>Agent: {{ $appointment->agent->agent_name }}</span>
                                        </div>
                                    @endif

                                    @if($appointment->property)
                                        <div class="detail-item">
                                            <i class="fas fa-home"></i>
                                            <span>{{ $appointment->property->name['en'] ?? 'Property' }}</span>
                                        </div>
                                    @endif

                                    @if($appointment->location)
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $appointment->location }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($appointment->status !== 'completed' && $appointment->status !== 'cancelled')
                            <div class="appointment-actions">
                                @if($appointment->property)
                                    <a href="{{ route('property.PropertyDetail', ['property_id' => $appointment->property->id]) }}" class="btn btn-secondary">
                                        <i class="far fa-eye"></i> View Property
                                    </a>
                                @endif

                                <button class="btn btn-primary reschedule-btn" data-id="{{ $appointment->id }}" data-date="{{ $appointment->appointment_date }}" data-time="{{ $appointment->appointment_time }}">
                                    <i class="far fa-calendar-alt"></i> Reschedule
                                </button>

                                <form action="{{ route('appointments.cancel', $appointment->id) }}" method="POST" style="display: inline; margin-left: auto;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="modal" id="rescheduleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Schedule</h2>
            </div>
            <form id="rescheduleForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="new_date">Select New Date</label>
                        <input type="date" id="new_date" name="appointment_date" class="form-input" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_time">Select New Time</label>
                        <input type="time" id="new_time" name="appointment_time" class="form-input" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Confirm Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('rescheduleModal');
        const rescheduleForm = document.getElementById('rescheduleForm');

        document.querySelectorAll('.reschedule-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                const currentDate = this.getAttribute('data-date');
                const currentTime = this.getAttribute('data-time');

                rescheduleForm.action = `/appointments/${appointmentId}/reschedule`;
                document.getElementById('new_date').value = currentDate;
                document.getElementById('new_time').value = currentTime;
                modal.classList.add('active');
            });
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // AJAX Submission
        rescheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const actionUrl = this.action;

            // Optional: Add loading state to button here

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.json();
                }
            })
            .then(data => {
                if (data && data.error) alert(data.error);
                // If success json returned instead of redirect, reload page
                if (data && data.success) location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>

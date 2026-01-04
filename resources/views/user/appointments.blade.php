<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Appointments - Dream Haven</title>
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
            max-width: 1000px;
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

        .appointments-stats {
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

        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .appointment-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .appointment-card.pending {
            border-left-color: #f97316;
        }

        .appointment-card.confirmed {
            border-left-color: #22c55e;
        }

        .appointment-card.completed {
            border-left-color: #64748b;
        }

        .appointment-card.cancelled {
            border-left-color: #ef4444;
            opacity: 0.7;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 25px;
            gap: 20px;
        }

        .appointment-date-badge {
            min-width: 100px;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: 12px;
            text-align: center;
            color: white;
            flex-shrink: 0;
        }

        .date-day {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .date-month {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .appointment-info {
            flex: 1;
        }

        .appointment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .appointment-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            color: var(--text-gray);
        }

        .detail-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }

        .status-confirmed {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .status-completed {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .appointment-actions {
            display: flex;
            gap: 10px;
            padding: 0 25px 25px 25px;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .appointments-stats {
                grid-template-columns: 1fr;
            }

            .appointment-header {
                flex-direction: column;
            }

            .appointment-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="container">
        <div class="page-header">
            <h1>My Appointments</h1>
            <p>Manage all your property viewing appointments</p>
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
                    <i class="fas fa-calendar-check"></i>
                    <h3>No Appointments Yet</h3>
                    <p>You haven't scheduled any property viewings yet</p>
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
                                    {{ ucfirst($appointment->type ?? 'viewing') }} Appointment
                                </h3>
                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                                    </div>
                                    @if($appointment->agent)
                                        <div class="detail-item">
                                            <i class="fas fa-user"></i>
                                            <span>Agent: {{ $appointment->agent->agent_name }}</span>
                                        </div>
                                    @endif
                                    @if($appointment->office)
                                        <div class="detail-item">
                                            <i class="fas fa-building"></i>
                                            <span>Office: {{ $appointment->office->company_name }}</span>
                                        </div>
                                    @endif
                                    @if($appointment->property)
                                        <div class="detail-item">
                                            <i class="fas fa-home"></i>
                                            <span>Property: {{ $appointment->property->name['en'] ?? 'Property' }}</span>
                                        </div>
                                    @endif
                                    @if($appointment->location)
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $appointment->location }}</span>
                                        </div>
                                    @endif
                                    <div class="detail-item">
                                        <i class="fas fa-tag"></i>
                                        <span class="status-badge status-{{ $appointment->status }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($appointment->status !== 'completed' && $appointment->status !== 'cancelled')
                            <div class="appointment-actions">
                                @if($appointment->property)
                                    <a href="{{ route('property.PropertyDetail', ['property_id' => $appointment->property->id]) }}" class="btn btn-primary">
                                        <i class="fas fa-eye"></i>
                                        View Property
                                    </a>
                                @endif

                                <button class="btn btn-secondary reschedule-btn" data-id="{{ $appointment->id }}" data-date="{{ $appointment->appointment_date }}" data-time="{{ $appointment->appointment_time }}">
                                    <i class="fas fa-calendar-alt"></i>
                                    Reschedule
                                </button>

                                <form action="{{ route('appointments.cancel', $appointment->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div class="modal" id="rescheduleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reschedule Appointment</h2>
            </div>
            <form id="rescheduleForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="new_date">New Date</label>
                        <input type="date" id="new_date" name="appointment_date" class="form-input" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_time">New Time</label>
                        <input type="time" id="new_time" name="appointment_time" class="form-input" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        Confirm Reschedule
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                        Cancel
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

                // Set form action
                rescheduleForm.action = `/appointments/${appointmentId}/reschedule`;

                // Set current values
                document.getElementById('new_date').value = currentDate;
                document.getElementById('new_time').value = currentTime;

                // Show modal
                modal.classList.add('active');
            });
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Handle form submission
        rescheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const actionUrl = this.action;

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
                if (data && data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>

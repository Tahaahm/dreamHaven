@extends('layouts.agent-layout')

@section('title', 'Appointments - Dream Mulk')

@section('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
    .stat-value { font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: #64748b; }

    .page-title { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; }

    .appointments-container { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }

    .appointment-item { padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 24px; align-items: start; transition: background 0.2s; }
    .appointment-item:last-child { border-bottom: none; }
    .appointment-item:hover { background: #f9fafb; }

    .appointment-date-badge { min-width: 80px; background: #303b97; color: white; padding: 16px; border-radius: 12px; text-align: center; }
    .date-day { font-size: 28px; font-weight: 700; line-height: 1; }
    .date-month { font-size: 14px; opacity: 0.9; margin-top: 4px; }
    .date-time { font-size: 12px; margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.2); }

    .appointment-details { flex: 1; }
    .appointment-title { font-size: 18px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
    .appointment-meta { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 12px; }
    .meta-item { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #64748b; }
    .meta-item i { color: #303b97; }

    .appointment-actions { display: flex; gap: 8px; margin-top: 12px; }
    .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
    .btn:hover { transform: translateY(-1px); }
    .btn-confirm { background: #dcfce7; color: #16a34a; }
    .btn-confirm:hover { background: #bbf7d0; }
    .btn-complete { background: #dbeafe; color: #2563eb; }
    .btn-complete:hover { background: #bfdbfe; }
    .btn-cancel { background: #fee2e2; color: #dc2626; }
    .btn-cancel:hover { background: #fecaca; }

    .status-badge { padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-confirmed { background: #dcfce7; color: #16a34a; }
    .status-completed { background: #dbeafe; color: #2563eb; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }

    .empty-state { padding: 80px 20px; text-align: center; color: #64748b; }
    .empty-state i { font-size: 64px; opacity: 0.3; margin-bottom: 20px; }

    .alert { padding: 16px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
    .alert-success { background: #d1fae5; border: 2px solid #059669; color: #059669; font-weight: 600; }
    .alert-error { background: #fee2e2; border: 2px solid #dc2626; color: #dc2626; font-weight: 600; }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .appointment-item { flex-direction: column; gap: 16px; }
        .appointment-date-badge { width: 100%; }
    }
</style>
@endsection

@section('content')
{{-- Success/Error Messages --}}
@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    {{ session('error') }}
</div>
@endif

<h1 class="page-title">My Appointments</h1>

{{-- Statistics Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Appointments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending'] }}</div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['confirmed'] }}</div>
        <div class="stat-label">Confirmed</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['completed'] }}</div>
        <div class="stat-label">Completed</div>
    </div>
</div>

{{-- Appointments List --}}
<div class="appointments-container">
    @forelse($appointments as $appointment)
        <div class="appointment-item">
            {{-- Date Badge --}}
            <div class="appointment-date-badge">
                <div class="date-day">{{ $appointment->appointment_date->format('d') }}</div>
                <div class="date-month">{{ $appointment->appointment_date->format('M Y') }}</div>
                @php
                    // Handle appointment_time safely regardless of database format
                    try {
                        if ($appointment->appointment_time instanceof \Carbon\Carbon) {
                            $timeStr = $appointment->appointment_time->format('h:i A');
                        } else {
                            $timeStr = \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A');
                        }
                    } catch (\Exception $e) {
                        $timeStr = $appointment->appointment_time ?? 'N/A';
                    }
                @endphp
                <div class="date-time">{{ $timeStr }}</div>
            </div>

            {{-- Appointment Details --}}
            <div class="appointment-details">
                {{-- ✅ FIX: Client Name (from appointments table or user relationship) --}}
                <h3 class="appointment-title">
                    {{ $appointment->client_name ?? $appointment->user->username ?? 'Client' }}
                </h3>

                {{-- Client Meta Information --}}
                <div class="appointment-meta">
                    {{-- ✅ FIX: Phone Number --}}
                    <div class="meta-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ $appointment->client_phone ?? $appointment->user->phone ?? 'N/A' }}</span>
                    </div>

                    {{-- ✅ FIX: Email Address --}}
                    <div class="meta-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $appointment->client_email ?? $appointment->user->email ?? 'N/A' }}</span>
                    </div>

                    {{-- Property Name --}}
                    @if($appointment->property)
                        <div class="meta-item">
                            <i class="fas fa-home"></i>
                            <span>{{ is_array($appointment->property->name) ? ($appointment->property->name['en'] ?? 'Property') : $appointment->property->name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Appointment Notes/Message --}}
                @if($appointment->notes || $appointment->message)
                    <p style="color: #64748b; font-size: 14px; margin: 12px 0;">
                        {{ $appointment->notes ?? $appointment->message }}
                    </p>
                @endif

                {{-- Status Badge and Action Buttons --}}
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 12px; flex-wrap: wrap;">
                    {{-- Status Badge --}}
                    <span class="status-badge status-{{ $appointment->status }}">
                        {{ ucfirst($appointment->status) }}
                    </span>

                    {{-- Action Buttons --}}
                    <div class="appointment-actions">
                        {{-- Confirm Button (Only for Pending) --}}
                        @if($appointment->status == 'pending')
                            <form method="POST" action="{{ route('agent.appointment.status', $appointment->id) }}" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-confirm">
                                    <i class="fas fa-check"></i> Confirm
                                </button>
                            </form>
                        @endif

                        {{-- Complete Button (Only for Confirmed) --}}
                        @if($appointment->status == 'confirmed')
                            <form method="POST" action="{{ route('agent.appointment.status', $appointment->id) }}" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-complete">
                                    <i class="fas fa-check-circle"></i> Complete
                                </button>
                            </form>
                        @endif

                        {{-- Cancel Button (Not for Cancelled or Completed) --}}
                        @if($appointment->status != 'cancelled' && $appointment->status != 'completed')
                            <form method="POST" action="{{ route('agent.appointment.status', $appointment->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-cancel">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No appointments yet</p>
            <p>Appointments from clients will appear here</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($appointments->hasPages())
    <div style="margin-top: 24px;">
        {{ $appointments->links() }}
    </div>
@endif
@endsection

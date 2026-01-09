@extends('layouts.office-layout')

@section('title', 'Appointments - Dream Mulk')
@section('search-placeholder', 'Search appointments...')

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .table-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { text-align: left; padding: 18px 20px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; background: var(--bg-card); border-bottom: 1px solid var(--border-color); }
    .data-table td { padding: 20px; border-bottom: 1px solid var(--border-color); font-size: 15px; color: var(--text-secondary); }
    .data-table tr:hover { background: var(--bg-hover); }
    .data-table tr:last-child td { border-bottom: none; }
    .client-info { display: flex; flex-direction: column; }
    .client-name { font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
    .client-phone { font-size: 13px; color: var(--text-muted); }
    .status-badge { display: inline-block; padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .status-badge.pending { background: rgba(249,115,22,0.15); color: #f97316; }
    .status-badge.confirmed { background: rgba(59,130,246,0.15); color: #3b82f6; }
    .status-badge.completed { background: rgba(34,197,94,0.15); color: #22c55e; }
    .status-badge.cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
    .action-select { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 12px; border-radius: 8px; font-size: 14px; cursor: pointer; }
    .action-select:hover { border-color: #6366f1; }
    .empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
    .empty i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
</style>
@endsection

@section('content')
<h1 class="page-title">Appointments</h1>

@if($appointments->count() > 0)
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Agent</th>
                <th>Property</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>
                    <div class="client-info">
                        <span class="client-name">{{ $appointment->user->name ?? 'N/A' }}</span>
                        <span class="client-phone">{{ $appointment->user->phone_number ?? '' }}</span>
                    </div>
                </td>
                <td>{{ $appointment->agent->agent_name ?? 'N/A' }}</td>
                <td>{{ json_decode($appointment->property->name ?? '{}')->en ?? 'N/A' }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}<br>
                    <small style="opacity: 0.7;">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                </td>
                <td><span class="status-badge {{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                <td>
                    <form action="{{ route('office.appointments.update', $appointment->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select name="status" class="action-select" onchange="this.form.submit()">
                            <option value="">Change Status</option>
                            <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $appointments->links() }}
</div>
@else
<div class="empty">
    <i class="fas fa-calendar-alt"></i>
    <h3>No Appointments Yet</h3>
    <p>Appointments will appear here once scheduled</p>
</div>
@endif
@endsection

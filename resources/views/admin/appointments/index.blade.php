@extends('layouts.admin-layout')

@section('title', 'Appointments')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Appointments</h1>
            <p class="text-slate-500 font-medium">Schedule management and meeting tracking.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="bg-white border border-slate-200 text-slate-500 px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                <i class="far fa-calendar"></i> {{ now()->format('D, M d') }}
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Today</span>
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            </div>
            <h3 class="text-3xl font-black text-slate-900">{{ $stats['today'] }}</h3>
            <p class="text-xs text-slate-500 font-medium">Meetings scheduled for today</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending</span>
                <i class="fas fa-hourglass-half text-amber-500 text-lg"></i>
            </div>
            <h3 class="text-3xl font-black text-slate-900">{{ $stats['pending'] }}</h3>
            <p class="text-xs text-slate-500 font-medium">Waiting for confirmation</p>
        </div>
         <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Confirmed</span>
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            </div>
            <h3 class="text-3xl font-black text-slate-900">{{ $stats['confirmed'] }}</h3>
            <p class="text-xs text-slate-500 font-medium">Upcoming approved meetings</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                <i class="fas fa-calendar-alt text-indigo-500 text-lg"></i>
            </div>
            <h3 class="text-3xl font-black text-slate-900">{{ $stats['total'] }}</h3>
            <p class="text-xs text-slate-500 font-medium">All time records</p>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-slate-100 flex gap-4 overflow-x-auto no-scrollbar">
            @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                <a href="{{ route('admin.appointments.index', ['status' => $val]) }}"
                   class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition {{ request('status') == $val ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Schedule & Urgency</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Client (User)</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Professional (Host)</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Property Interest</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($appointments as $appt)
                    @php
                        // --- DEADLINE LOGIC ---
                        $date = \Carbon\Carbon::parse($appt->appointment_date);
                        $isPast = $date->isPast() && !$date->isToday();
                        $diff = now()->diffInDays($date, false);

                        if ($isPast) {
                            $urgencyColor = 'bg-slate-100 text-slate-400 border-slate-200'; // Past
                            $urgencyText = 'Past Event';
                            $icon = 'fa-history';
                        } elseif ($date->isToday() || $diff <= 1) {
                            $urgencyColor = 'bg-rose-50 text-rose-600 border-rose-100 animate-pulse'; // Critical
                            $urgencyText = $date->isToday() ? 'Today' : 'Tomorrow';
                            $icon = 'fa-exclamation-circle';
                        } elseif ($diff <= 3) {
                            $urgencyColor = 'bg-amber-50 text-amber-600 border-amber-100'; // Warning
                            $urgencyText = 'Soon';
                            $icon = 'fa-clock';
                        } else {
                            $urgencyColor = 'bg-indigo-50 text-indigo-600 border-indigo-100'; // Safe
                            $urgencyText = 'Upcoming';
                            $icon = 'fa-calendar';
                        }

                        // --- RESOLVE HOST (Agent or Office) ---
                        $hostName = 'Unknown';
                        $hostType = 'N/A';
                        $hostAvatar = null;

                        if ($appt->agent) {
                            $hostName = $appt->agent->agent_name;
                            $hostType = 'Agent';
                            $hostAvatar = $appt->agent->profile_image;
                        } elseif ($appt->office) {
                            $hostName = $appt->office->company_name;
                            $hostType = 'Office';
                            $hostAvatar = $appt->office->profile_image; // Assuming logo
                        }
                    @endphp

                    <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ route('admin.appointments.show', $appt->id) }}'">

                        {{-- Schedule --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center border-2 {{ $urgencyColor }}">
                                    <span class="text-[10px] font-bold uppercase">{{ $date->format('M') }}</span>
                                    <span class="text-lg font-black leading-none">{{ $date->format('d') }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $date->format('l') }}</p>
                                    <p class="text-xs font-mono text-slate-500">{{ $appt->appointment_time ? $appt->appointment_time->format('h:i A') : 'TBD' }}</p>
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase border {{ $urgencyColor }}">
                                        {{ $urgencyText }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Client --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ substr($appt->client_name ?? ($appt->user->name ?? 'U'), 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $appt->client_name ?? ($appt->user->name ?? 'Unknown') }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">ID: {{ $appt->user_id ?? 'Guest' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Host --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 overflow-hidden">
                                     @if($hostAvatar)
                                        <img src="{{ asset($hostAvatar) }}" class="w-full h-full object-cover">
                                     @else
                                        <i class="fas fa-briefcase text-xs"></i>
                                     @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 truncate max-w-[120px]">{{ $hostName }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $hostType }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Property --}}
                        <td class="px-6 py-4">
                            @if($appt->property)
                                @php
                                    $pName = is_array($appt->property->name) ? ($appt->property->name['en'] ?? 'Property') : $appt->property->name;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-home text-slate-300"></i>
                                    <span class="text-xs font-bold text-slate-600 truncate max-w-[150px]" title="{{ $pName }}">
                                        {{ $pName }}
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">General Inquiry</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClass = match($appt->status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'completed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wide border {{ $statusClass }}">
                                {{ ucfirst($appt->status) }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-6 py-4 text-right">
                             <a href="{{ route('admin.appointments.show', $appt->id) }}" class="text-slate-400 hover:text-indigo-600 transition">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">No appointments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $appointments->links() }}
        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

@endsection

@extends('layouts.admin-layout')

@section('title', 'Appointment Details')

@section('content')

@php
    $date = \Carbon\Carbon::parse($appointment->appointment_date);

    // Host Logic
    $hostName = 'Unknown';
    $hostType = 'N/A';
    $hostImage = null;
    $hostLink = '#';

    if ($appointment->agent) {
        $hostName = $appointment->agent->agent_name;
        $hostType = 'Agent';
        $hostImage = $appointment->agent->profile_image;
        $hostLink = route('admin.agents.show', $appointment->agent_id);
    } elseif ($appointment->office) {
        $hostName = $appointment->office->company_name;
        $hostType = 'Real Estate Office';
        $hostImage = $appointment->office->profile_image;
        $hostLink = route('admin.offices.show', $appointment->office_id);
    }

    // Client Logic
    $clientName = $appointment->client_name ?? ($appointment->user->name ?? 'Guest User');
    $clientEmail = $appointment->client_email ?? ($appointment->user->email ?? 'N/A');
    $clientPhone = $appointment->client_phone ?? ($appointment->user->phone ?? 'N/A');

    // Property Logic
    $pName = 'General Inquiry';
    $pAddress = 'N/A';
    $pImage = null;

    if ($appointment->property) {
        $rawName = $appointment->property->name;
        $pName = is_array($rawName) ? ($rawName['en'] ?? 'Property') : $rawName;
        $pAddress = $appointment->property->address;
        $rawImg = $appointment->property->images;
        if(is_array($rawImg) && !empty($rawImg)) $pImage = $rawImg[0];
    }
@endphp

<div class="max-w-4xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1">
                <a href="{{ route('admin.appointments.index') }}" class="hover:text-slate-900 transition">Appointments</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-bold">Details</span>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Appointment Info</h1>
        </div>
        <div class="flex gap-3">
            @if($appointment->status !== 'cancelled')
                <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST" onsubmit="return confirm('Cancel this appointment?');">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-white border border-slate-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition flex items-center gap-2">
                        <i class="fas fa-ban"></i> Cancel
                    </button>
                </form>
            @endif
             <form action="{{ route('admin.appointments.delete', $appointment->id) }}" method="POST" onsubmit="return confirm('Permanently delete this record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition flex items-center gap-2">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Left: Main Details --}}
        <div class="md:col-span-2 space-y-6">

            {{-- Time Card --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex items-center gap-6">
                <div class="w-20 h-20 rounded-2xl bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center text-indigo-700">
                    <span class="text-xs font-bold uppercase">{{ $date->format('M') }}</span>
                    <span class="text-3xl font-black leading-none">{{ $date->format('d') }}</span>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">{{ $appointment->appointment_time ? $appointment->appointment_time->format('h:i A') : 'Time TBD' }}</h2>
                    <p class="text-slate-500 font-medium">{{ $date->format('l, Y') }}</p>
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $appointment->status == 'confirmed' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </div>
            </div>

            {{-- Parties --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Client --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Client (Requester)</p>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500 text-lg">
                            {{ substr($clientName, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $clientName }}</p>
                            <p class="text-xs text-slate-500 font-mono">ID: {{ $appointment->user_id ?? 'Guest' }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="fas fa-envelope w-4"></i> {{ $clientEmail }}
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <i class="fas fa-phone w-4"></i> {{ $clientPhone }}
                        </div>
                    </div>
                </div>

                {{-- Host --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Host (Professional)</p>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                            @if($hostImage)
                                <img src="{{ asset($hostImage) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-briefcase text-slate-400"></i>
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $hostName }}</p>
                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">{{ $hostType }}</span>
                        </div>
                    </div>
                    <a href="{{ $hostLink }}" class="block text-center text-xs font-bold text-indigo-600 hover:bg-indigo-50 py-2 rounded-lg transition border border-indigo-100">
                        View Profile
                    </a>
                </div>
            </div>

            {{-- Notes --}}
            @if($appointment->notes)
            <div class="bg-amber-50 rounded-3xl border border-amber-100 p-6">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-2">Message / Notes</p>
                <p class="text-sm text-amber-900 italic">"{{ $appointment->notes }}"</p>
            </div>
            @endif

        </div>

        {{-- Right: Property --}}
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-2 overflow-hidden">
                 <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-4 mb-2">Subject Property</p>
                 @if($appointment->property)
                    <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-slate-100 mb-4 mx-auto" style="width: calc(100% - 16px);">
                        @if($pImage)
                            <img src="{{ asset($pImage) }}" class="w-full h-full object-cover">
                        @else
                             <div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-home text-2xl"></i></div>
                        @endif
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="text-sm font-bold text-slate-900 line-clamp-2 mb-1">{{ $pName }}</h3>
                        <p class="text-xs text-slate-500 mb-4"><i class="fas fa-map-marker-alt mr-1"></i> {{ $pAddress ?? 'No address' }}</p>
                        <a href="{{ route('admin.properties.show', $appointment->property_id) }}" class="block w-full py-2.5 bg-black text-white text-center text-xs font-bold rounded-xl hover:bg-slate-800 transition">
                            View Property
                        </a>
                    </div>
                 @else
                    <div class="p-8 text-center text-slate-400 text-sm">
                        <i class="fas fa-question-circle text-2xl mb-2"></i><br>
                        No specific property linked.
                    </div>
                 @endif
            </div>

            <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                 <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Metadata</p>
                 <div class="space-y-2 text-xs text-slate-600">
                     <div class="flex justify-between">
                         <span>Created</span>
                         <span class="font-mono">{{ $appointment->created_at->format('Y-m-d H:i') }}</span>
                     </div>
                     <div class="flex justify-between">
                         <span>Last Updated</span>
                         <span class="font-mono">{{ $appointment->updated_at->format('Y-m-d H:i') }}</span>
                     </div>
                     <div class="flex justify-between">
                         <span>UUID</span>
                         <span class="font-mono" title="{{ $appointment->id }}">{{ substr($appointment->id, 0, 8) }}...</span>
                     </div>
                 </div>
            </div>
        </div>

    </div>

</div>

@endsection

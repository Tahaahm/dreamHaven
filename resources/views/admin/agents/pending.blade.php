@extends('layouts.admin-layout')

@section('title', 'Pending Agents')

@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pending Agent Approvals</h1>
            <p class="text-gray-600 mt-1">Review and approve agent registrations</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to All Agents
        </a>
    </div>
</div>

<div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
    <div class="flex items-center">
        <i class="fas fa-info-circle text-yellow-600 text-2xl mr-4"></i>
        <div>
            <h4 class="text-lg font-bold text-yellow-800">{{ $agents->total() }} Agents Awaiting Approval</h4>
            <p class="text-sm text-yellow-700">Review each agent carefully before approving</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6">
    @forelse($agents as $agent)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="w-16 h-16 gradient-info rounded-full flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($agent->agent_name, 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $agent->agent_name }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agent->primary_email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Phone</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agent->primary_phone ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">City</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agent->city ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Registered</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agent->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @if($agent->agent_bio)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">{{ Str::limit($agent->agent_bio, 200) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col space-y-2 ml-4">
                    <form action="{{ route('admin.agents.verify', $agent->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600 transition w-full">
                            <i class="fas fa-check mr-2"></i> Approve
                        </button>
                    </form>
                    <a href="{{ route('admin.agents.show', $agent->id) }}" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600 transition text-center">
                        <i class="fas fa-eye mr-2"></i> View Details
                    </a>
                    <form action="{{ route('admin.agents.delete', $agent->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-600 transition w-full">
                            <i class="fas fa-times mr-2"></i> Reject
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">All Caught Up!</h3>
        <p class="text-gray-600">There are no pending agent approvals at the moment.</p>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $agents->links() }}
</div>

@endsection

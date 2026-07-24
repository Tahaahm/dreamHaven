@extends('layouts.admin-layout')

@section('title', 'Users')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Route as Rt;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;

    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    /* Real stats, cached for two minutes */
    $s = Cache::remember('admin.users.stats', 120, function () {
        $safe = function ($cb) { try { return (int) $cb(); } catch (\Throwable $e) { return 0; } };
        return [
            'total'    => $safe(fn() => \App\Models\User::count()),
            'week'     => $safe(fn() => \App\Models\User::where('created_at', '>=', now()->subDays(7))->count()),
            'prevWeek' => $safe(fn() => \App\Models\User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count()),
            'verified' => $safe(fn() => \App\Models\User::where('is_verified', true)->count()),
            'admins'   => $safe(fn() => \App\Models\User::where('role', 'admin')->count()),
            'agents'   => $safe(fn() => \App\Models\User::where('role', 'agent')->count()),
        ];
    });

    $growth = $s['prevWeek'] > 0 ? round((($s['week'] - $s['prevWeek']) / $s['prevWeek']) * 100) : ($s['week'] > 0 ? 100 : 0);
    $verifyRate = $s['total'] > 0 ? (int) round(($s['verified'] / $s['total']) * 100) : 0;

    /* Last-seen per user on this page (one grouped query, if an activity table exists) */
    $activityTable = Cache::remember('admin.activity.table', 3600, function () {
        foreach (['property_interactions', 'interactions', 'user_interactions', 'property_views'] as $t) {
            try { if (Schema::hasTable($t) && Schema::hasColumn($t, 'user_id')) return $t; } catch (\Throwable $e) {}
        }
        return null;
    });

    $seen = collect();
    if ($activityTable && $users->count()) {
        try {
            $seen = DB::table($activityTable)
                ->whereIn('user_id', $users->pluck('id'))
                ->select('user_id', DB::raw('MAX(created_at) as last_at'), DB::raw('COUNT(*) as hits'))
                ->groupBy('user_id')->get()->keyBy('user_id');
        } catch (\Throwable $e) { $seen = collect(); }
    }

    $verifyRoute   = Rt::has('admin.users.activate') ? 'admin.users.activate' : (Rt::has('admin.users.verify') ? 'admin.users.verify' : null);
    $suspendRoute  = Rt::has('admin.users.suspend') ? 'admin.users.suspend' : (Rt::has('admin.users.unverify') ? 'admin.users.unverify' : null);
    $hasFilters    = request()->hasAny(['search', 'role', 'status']);
@endphp

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Dream Mulk network</p>
            <h1 class="page-ttl">Users</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">
                {{ number_format($users->total()) }} account{{ $users->total() === 1 ? '' : 's' }} matching this view
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="dmExportTable('#usersTable', 'users')" class="btn-ghost">
                <i class="fas fa-download"></i> Export CSV
            </button>
            @if($link('admin.users.create'))
                <a href="{{ $link('admin.users.create') }}" class="btn-solid"><i class="fas fa-plus"></i> Add user</a>
            @endif
        </div>
    </div>

    {{-- STATS --}}
    <div class="stat-row cols-4">
        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Total users</span>
                <i class="fas fa-users text-slate-300 text-[11px]"></i>
            </div>
            <b class="num">{{ number_format($s['total']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">all time</p>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">New this week</span>
                <span class="chip {{ $growth > 0 ? 'chip-up' : ($growth < 0 ? 'chip-down' : 'chip-flat') }}">
                    {{ $growth > 0 ? '+' : '' }}{{ $growth }}%
                </span>
            </div>
            <b class="num">{{ number_format($s['week']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">vs {{ $s['prevWeek'] }} last week</p>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Verified</span>
                <i class="fas fa-shield-halved text-emerald-500 text-[11px]"></i>
            </div>
            <b class="num">{{ $verifyRate }}%</b>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mt-2">
                <div class="h-full rounded-full bg-emerald-500" style="width:{{ $verifyRate }}%"></div>
            </div>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">By role</span>
                <i class="fas fa-user-shield text-[11px]" style="color:var(--dm)"></i>
            </div>
            <b class="num">{{ number_format($s['agents']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">agents · {{ $s['admins'] }} admins</p>
        </div>
    </div>

    {{-- SEARCH + FILTERS --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="search-wrap">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone…">
        @foreach(['role', 'status', 'sort_by', 'sort_order'] as $keep)
            @if(request($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
        @endforeach
    </form>

    <div class="pill-row mb-5">
        <a href="{{ route('admin.users.index') }}" class="pill {{ !$hasFilters ? 'on' : '' }}">All</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['status' => 'verified'])) }}"
           class="pill {{ request('status') === 'verified' ? 'on' : '' }}"><i class="fas fa-shield-halved text-[10px]"></i> Verified</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['status' => 'unverified'])) }}"
           class="pill {{ request('status') === 'unverified' ? 'on-alert' : '' }}"><i class="fas fa-hourglass-half text-[10px]"></i> Pending</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['status' => 'email_unverified'])) }}"
           class="pill {{ request('status') === 'email_unverified' ? 'on' : '' }}"><i class="far fa-envelope text-[10px]"></i> No email check</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['role' => 'agent'])) }}"
           class="pill {{ request('role') === 'agent' ? 'on' : '' }}"><i class="fas fa-user-tie text-[10px]"></i> Agents</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['role' => 'admin'])) }}"
           class="pill {{ request('role') === 'admin' ? 'on' : '' }}"><i class="fas fa-user-shield text-[10px]"></i> Admins</a>
        <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['sort_by' => 'created_at', 'sort_order' => 'asc'])) }}"
           class="pill {{ request('sort_order') === 'asc' ? 'on' : '' }}"><i class="fas fa-arrow-up-1-9 text-[10px]"></i> Oldest first</a>
        @if($hasFilters)
            <a href="{{ route('admin.users.index') }}" class="pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3"><i class="fas fa-xmark text-[10px]"></i> Clear</a>
        @endif
    </div>

    {{-- ══════════ DESKTOP TABLE ══════════ --}}
    <div class="card overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="tbl" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Status</th>
                        <th>Last seen</th>
                        <th class="text-right">Joined</th>
                        <th class="w-36" data-noexport></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $act  = $seen->get($user->id);
                            $last = $act ? \Carbon\Carbon::parse($act->last_at) : null;
                            $gap  = $last ? $last->diffInDays(now()) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($user->photo_image)
                                        <img src="{{ asset($user->photo_image) }}" class="avatar" alt="">
                                    @else
                                        <div class="avatar avatar-fb">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ $link('admin.users.show', $user->id) ?? '#' }}" class="block text-[13px] font-bold text-slate-900 hover:text-[#303b97] truncate max-w-[180px]">
                                            {{ $user->username }}
                                        </a>
                                        <span class="text-[10px] font-mono text-slate-400">#{{ \Illuminate\Support\Str::limit($user->id, 10, '') }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <p class="text-[12.5px] font-semibold text-slate-700 truncate max-w-[190px]">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $user->phone }}</p>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $user->role === 'admin' ? 'b-plan' : ($user->role === 'agent' ? 'b-mute' : 'b-mute') }}">
                                    {{ strtoupper($user->role ?? 'user') }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($user->is_verified)
                                    <span class="badge b-ok"><span class="dotlet"></span> Verified</span>
                                @else
                                    <span class="badge b-warn"><span class="dotlet"></span> Pending</span>
                                @endif
                                @if($user->is_suspended ?? false)
                                    <span class="badge b-bad mt-1">Suspended</span>
                                @endif
                            </td>

                            <td>
                                @if($last)
                                    <p class="text-[12px] font-bold {{ $gap > 30 ? 'text-amber-600' : 'text-slate-700' }}">{{ $last->diffForHumans(null, true) }} ago</p>
                                    <p class="text-[10.5px] font-bold text-slate-400">{{ number_format($act->hits) }} actions</p>
                                @elseif($activityTable)
                                    <span class="badge b-mute">Never active</span>
                                @else
                                    <span class="text-[11px] font-bold text-slate-300">—</span>
                                @endif
                            </td>

                            <td class="text-right">
                                <p class="text-[12px] font-bold text-slate-600">{{ optional($user->created_at)->format('d M Y') }}</p>
                                <p class="text-[10.5px] font-semibold text-slate-400">{{ optional($user->created_at)->format('h:i A') }}</p>
                            </td>

                            <td data-noexport>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($link('admin.users.show', $user->id))
                                        <a href="{{ $link('admin.users.show', $user->id) }}" class="iact" title="View"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if($link('admin.users.edit', $user->id))
                                        <a href="{{ $link('admin.users.edit', $user->id) }}" class="iact" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif

                                    @if(!$user->is_verified && $verifyRoute)
                                        <form method="POST" action="{{ route($verifyRoute, $user->id) }}" class="inline">
                                            @csrf
                                            <button class="iact good" title="Verify"><i class="fas fa-check"></i></button>
                                        </form>
                                    @elseif($user->is_verified && $suspendRoute)
                                        <form method="POST" action="{{ route($suspendRoute, $user->id) }}" class="inline">
                                            @csrf
                                            <button class="iact danger" title="Suspend"><i class="fas fa-ban"></i></button>
                                        </form>
                                    @endif

                                    @if($user->role !== 'admin' && $link('admin.users.delete', $user->id))
                                        <form method="POST" action="{{ $link('admin.users.delete', $user->id) }}" class="inline"
                                              onsubmit="return confirm('Delete {{ addslashes($user->username) }}? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button class="iact danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No users match this view</h3>
                                    <p>Try a different search or clear the filters.</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="pager">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ══════════ MOBILE CARDS ══════════ --}}
    <div class="md:hidden">
        @forelse($users as $user)
            @php
                $act  = $seen->get($user->id);
                $last = $act ? \Carbon\Carbon::parse($act->last_at) : null;
            @endphp
            <div class="mcard">
                @if(!$user->is_verified)
                    <div class="mcard-strip" style="background:#fffbeb;color:#b45309">
                        <span><i class="fas fa-hourglass-half mr-1"></i> Waiting for verification</span>
                        @if($verifyRoute)
                            <form method="POST" action="{{ route($verifyRoute, $user->id) }}">
                                @csrf
                                <button class="underline underline-offset-2">VERIFY</button>
                            </form>
                        @endif
                    </div>
                @endif

                <div class="mcard-body">
                    <div class="flex items-start gap-3 mb-3">
                        @if($user->photo_image)
                            <img src="{{ asset($user->photo_image) }}" class="avatar !w-12 !h-12" alt="">
                        @else
                            <div class="avatar avatar-fb !w-12 !h-12 text-sm">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ $link('admin.users.show', $user->id) ?? '#' }}" class="text-[14px] font-black text-slate-900 truncate">{{ $user->username }}</a>
                                <span class="badge {{ $user->is_verified ? 'b-ok' : 'b-warn' }} shrink-0">{{ $user->is_verified ? 'Verified' : 'Pending' }}</span>
                            </div>
                            <p class="text-[11.5px] font-semibold text-slate-400 truncate mt-0.5">{{ $user->email }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase mt-1">
                                {{ $user->role ?? 'user' }}
                                <span class="mx-1.5 text-slate-200">·</span>
                                joined {{ optional($user->created_at)->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    @if($last)
                        <div class="flex items-center gap-2.5 bg-slate-50 rounded-xl px-3 py-2.5 mb-3">
                            <i class="fas fa-wave-square text-[11px]" style="color:var(--dm)"></i>
                            <p class="text-[11.5px] font-bold text-slate-600">
                                Last seen {{ $last->diffForHumans(null, true) }} ago · {{ number_format($act->hits) }} actions
                            </p>
                        </div>
                    @endif

                    <div class="mgrid" style="grid-template-columns:repeat({{ $user->role !== 'admin' ? 3 : 2 }},1fr)">
                        <a href="{{ $link('admin.users.show', $user->id) ?? '#' }}" class="mbtn mbtn-p"><i class="fas fa-eye text-[10px]"></i> View</a>
                        <a href="{{ $link('admin.users.edit', $user->id) ?? '#' }}" class="mbtn mbtn-s"><i class="fas fa-pen text-[10px]"></i> Edit</a>
                        @if($user->role !== 'admin' && $link('admin.users.delete', $user->id))
                            <form method="POST" action="{{ $link('admin.users.delete', $user->id) }}" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button class="mbtn mbtn-d"><i class="fas fa-trash text-[10px]"></i> Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>No users match this view</h3>
                <p>Try a different search or clear the filters.</p>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
            </div>
        @endforelse

        @if($users->hasPages())
            <div class="pt-2">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

@endsection

{{-- ═══════════════════════════════════════════════════════════════
     DREAM MULK — WEEKLY BRIEFING PARTIAL
     Save as: resources/views/admin/partials/weekly-briefing.blade.php

     Expects:
       $briefing = [
          'week_label'  => 'Jul 20 – Jul 26',
          'top_posters' => [ ['id','name','image','count'], ... ],
          'expired'     => [ ['id','name','image','plan','ended'], ... ],
          'expiring'    => [ ['id','name','image','plan','days','ends'], ... ],
       ]
       $editRoute  e.g. 'admin.agents.edit'
       $showRoute  e.g. 'admin.agents.show'
       $entityIcon e.g. 'fa-user' or 'fa-building'
════════════════════════════════════════════════════════════════ --}}

<div class="mb-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    {{-- Briefing header --}}
    <div class="bg-dm-navy px-4 md:px-6 py-4 flex items-center justify-between relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--dm-gold)">
                <i class="fas fa-chart-line text-white text-sm"></i>
            </div>
            <div>
                <h2 class="text-white text-sm md:text-base font-black uppercase tracking-widest">Weekly Briefing</h2>
                <p class="text-white/50 text-[10px] font-bold uppercase tracking-wider">{{ $briefing['week_label'] }}</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-4 text-right">
            <div>
                <p class="text-lg font-black" style="color: var(--dm-gold)">{{ collect($briefing['top_posters'])->sum('count') }}</p>
                <p class="text-white/40 text-[9px] font-bold uppercase tracking-wider">Posts this week</p>
            </div>
            <div class="w-px h-8 bg-white/10"></div>
            <div>
                <p class="text-lg font-black {{ count($briefing['expiring']) > 0 ? 'text-rose-300' : 'text-white' }}">{{ count($briefing['expiring']) }}</p>
                <p class="text-white/40 text-[9px] font-bold uppercase tracking-wider">Expiring</p>
            </div>
            <div class="w-px h-8 bg-white/10"></div>
            <div>
                <p class="text-lg font-black text-white/80">{{ count($briefing['expired']) }}</p>
                <p class="text-white/40 text-[9px] font-bold uppercase tracking-wider">Expired</p>
            </div>
        </div>
    </div>

    {{-- 3 columns on desktop · stacked accordion feel on mobile --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">

        {{-- ── COLUMN 1: TOP POSTERS ─────────────────────────────── --}}
        <div class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">
                    <i class="fas fa-trophy mr-1.5" style="color: var(--dm-gold)"></i>Top Posters
                </h3>
                <span class="text-[9px] font-bold text-slate-300 uppercase">This week</span>
            </div>

            @forelse($briefing['top_posters'] as $i => $p)
            <a href="{{ route($showRoute, $p['id']) }}"
               class="flex items-center gap-3 py-2 px-2 -mx-2 rounded-xl hover:bg-slate-50 active:scale-[0.99] transition group">
                {{-- Rank medal --}}
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0
                    {{ $i == 0 ? 'text-white' : ($i == 1 ? 'bg-slate-200 text-slate-600' : ($i == 2 ? 'bg-amber-100 text-amber-700' : 'bg-slate-50 text-slate-400')) }}"
                    style="{{ $i == 0 ? 'background: var(--dm-gold)' : '' }}">
                    {{ $i + 1 }}
                </span>
                <div class="w-8 h-8 rounded-lg bg-dm-navy-soft border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                    @if($p['image'])
                        <img src="{{ asset($p['image']) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas {{ $entityIcon }} dm-navy opacity-40 text-xs"></i>
                    @endif
                </div>
                <span class="flex-1 text-xs font-bold text-slate-800 truncate">{{ $p['name'] }}</span>
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black
                    {{ $i == 0 ? 'bg-dm-gold-soft dm-gold' : 'bg-slate-100 text-slate-500' }}">
                    {{ $p['count'] }} <span class="font-bold opacity-60">posts</span>
                </span>
            </a>
            @empty
            <div class="py-6 text-center">
                <i class="fas fa-inbox text-slate-200 text-2xl mb-2"></i>
                <p class="text-xs font-semibold text-slate-400">No new listings this week yet.</p>
            </div>
            @endforelse
        </div>

        {{-- ── COLUMN 2: EXPIRING THIS WEEK (act now) ─────────────── --}}
        <div class="p-4 md:p-5 {{ count($briefing['expiring']) > 0 ? 'bg-rose-50/40' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[11px] font-black uppercase tracking-widest text-rose-500">
                    <i class="fas fa-hourglass-end mr-1.5"></i>Expiring This Week
                </h3>
                @if(count($briefing['expiring']) > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[9px] font-black">{{ count($briefing['expiring']) }}</span>
                @endif
            </div>

            @forelse($briefing['expiring'] as $e)
            <div class="flex items-center gap-3 py-2 px-2 -mx-2 rounded-xl hover:bg-white transition">
                <div class="w-8 h-8 rounded-lg bg-white border border-rose-100 flex items-center justify-center overflow-hidden shrink-0">
                    @if($e['image'])
                        <img src="{{ asset($e['image']) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas {{ $entityIcon }} text-rose-300 text-xs"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $e['name'] }}</p>
                    <p class="text-[10px] font-bold text-rose-500">
                        {{ $e['days'] == 0 ? 'Expires TODAY' : ($e['days'] == 1 ? 'Expires tomorrow' : $e['days'] . ' days · ' . $e['ends']) }}
                        <span class="text-slate-300 mx-1">·</span>
                        <span class="uppercase text-slate-400">{{ $e['plan'] }}</span>
                    </p>
                </div>
                <a href="{{ route($editRoute, $e['id']) }}"
                   class="shrink-0 px-3 py-1.5 rounded-lg bg-rose-500 text-white text-[10px] font-black uppercase hover:bg-rose-600 active:scale-95 transition">
                    Renew
                </a>
            </div>
            @empty
            <div class="py-6 text-center">
                <i class="fas fa-shield-heart text-emerald-200 text-2xl mb-2"></i>
                <p class="text-xs font-semibold text-slate-400">No expirations this week. All healthy ✓</p>
            </div>
            @endforelse
        </div>

        {{-- ── COLUMN 3: EXPIRED THIS WEEK (recover) ──────────────── --}}
        <div class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">
                    <i class="fas fa-circle-xmark mr-1.5 text-slate-400"></i>Expired This Week
                </h3>
                <span class="text-[9px] font-bold text-slate-300 uppercase">Win back</span>
            </div>

            @forelse($briefing['expired'] as $x)
            <div class="flex items-center gap-3 py-2 px-2 -mx-2 rounded-xl hover:bg-slate-50 transition">
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 grayscale">
                    @if($x['image'])
                        <img src="{{ asset($x['image']) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas {{ $entityIcon }} text-slate-300 text-xs"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-600 truncate">{{ $x['name'] }}</p>
                    <p class="text-[10px] font-bold text-slate-400">
                        Ended {{ $x['ended'] }}
                        <span class="text-slate-300 mx-1">·</span>
                        <span class="uppercase">{{ $x['plan'] }}</span>
                    </p>
                </div>
                <a href="{{ route($editRoute, $x['id']) }}"
                   class="shrink-0 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase active:scale-95 transition text-white hover:opacity-90"
                   style="background: var(--dm-gold)">
                    Reactivate
                </a>
            </div>
            @empty
            <div class="py-6 text-center">
                <i class="fas fa-face-smile text-slate-200 text-2xl mb-2"></i>
                <p class="text-xs font-semibold text-slate-400">Nobody expired this week.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

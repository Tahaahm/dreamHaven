@extends('layouts.admin-layout')
@section('title', 'Agents Directory')
@section('content')
<style>
*{box-sizing:border-box}
.dm-page{padding:32px 24px 80px;max-width:1100px;margin:0 auto}
.dm-eyebrow{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.dm-eyebrow-line{width:20px;height:2px;background:#C9A961;border-radius:2px;flex-shrink:0}
.dm-eyebrow-text{font-size:11px;letter-spacing:.12em;color:var(--text-muted);text-transform:uppercase}
.dm-page-title{font-size:26px;font-weight:500;color:var(--text-primary);margin-bottom:3px}
.dm-page-sub{font-size:13px;color:var(--text-secondary);margin-bottom:28px}

/* briefing */
.dm-briefing{border:.5px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:24px}
.dm-briefing-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:.5px solid var(--border);flex-wrap:wrap;gap:12px}
.dm-briefing-head-left{display:flex;align-items:center;gap:10px}
.dm-briefing-icon{width:32px;height:32px;border-radius:8px;background:#233264;display:flex;align-items:center;justify-content:center;color:#C9A961;font-size:14px;flex-shrink:0}
.dm-briefing-title{font-size:13px;font-weight:500;color:var(--text-primary)}
.dm-briefing-week{font-size:11px;color:var(--text-muted);margin-top:1px}
.dm-briefing-totals{display:flex;gap:20px}
.dm-bt{text-align:right}
.dm-bt-num{font-size:18px;font-weight:500;color:var(--text-primary);line-height:1}
.dm-bt-num.warn{color:#b91c1c}
.dm-bt-label{font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-top:2px}
.dm-briefing-cols{display:grid;grid-template-columns:repeat(3,1fr)}
@media(max-width:700px){.dm-briefing-cols{grid-template-columns:1fr}}
.dm-bcol{padding:16px 20px;border-right:.5px solid var(--border)}
.dm-bcol:last-child{border-right:none}
@media(max-width:700px){.dm-bcol{border-right:none;border-bottom:.5px solid var(--border)}.dm-bcol:last-child{border-bottom:none}}
.dm-col-label{font-size:10px;font-weight:500;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.dm-col-dot{width:6px;height:6px;border-radius:50%;background:#C9A961;flex-shrink:0}
.dm-col-dot.red{background:#ef4444}
.dm-col-dot.gray{background:var(--border-strong)}

/* poster row */
.dm-poster-row{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:.5px solid var(--border)}
.dm-poster-row:last-child{border-bottom:none}
.dm-rank{font-size:11px;font-weight:500;color:var(--text-muted);width:16px;text-align:center;flex-shrink:0}
.dm-rank.gold{color:#C9A961}
.dm-av-sm{width:28px;height:28px;border-radius:7px;background:#ECF0F8;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:500;color:#233264;flex-shrink:0;overflow:hidden}
.dm-av-sm img{width:100%;height:100%;object-fit:cover}
.dm-poster-name{font-size:12px;font-weight:500;color:var(--text-primary);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dm-poster-count{font-size:11px;font-weight:500;color:#C9A961;padding:2px 7px;border-radius:4px;background:#FBF6EA;flex-shrink:0;white-space:nowrap}
.dm-empty-col{text-align:center;padding:20px 0}
.dm-empty-col i{font-size:20px;color:var(--border-strong);display:block;margin-bottom:6px}
.dm-empty-col p{font-size:11px;color:var(--text-muted)}

/* expiry row */
.dm-expiry-row{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:.5px solid var(--border)}
.dm-expiry-row:last-child{border-bottom:none}
.dm-exp-days{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0;width:52px}
.dm-exp-days.urgent{color:#b91c1c}
.dm-exp-days.warn{color:#d97706}
.dm-exp-days.muted{color:var(--text-muted)}
.dm-exp-name{font-size:12px;font-weight:500;color:var(--text-primary);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dm-exp-name.faded{color:var(--text-secondary)}
.dm-exp-plan{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);flex-shrink:0}
.dm-renew-btn{font-size:10px;font-weight:500;padding:3px 10px;border-radius:4px;border:.5px solid #fca5a5;background:#fef2f2;color:#b91c1c;cursor:pointer;white-space:nowrap;flex-shrink:0;text-decoration:none;display:inline-block}
.dm-renew-btn:hover{background:#fee2e2}
.dm-react-btn{font-size:10px;font-weight:500;padding:3px 10px;border-radius:4px;border:.5px solid var(--border-strong);background:var(--surface-1);color:var(--text-secondary);cursor:pointer;white-space:nowrap;flex-shrink:0;text-decoration:none;display:inline-block}
.dm-react-btn:hover{border-color:#C9A961;color:#92400e}

/* stats */
.dm-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:22px}
@media(max-width:700px){.dm-stats{grid-template-columns:repeat(2,1fr)}}
.dm-stat{background:var(--surface-1);border-radius:var(--radius);padding:14px 16px}
.dm-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:6px}
.dm-stat-value{font-size:22px;font-weight:500;color:var(--text-primary);line-height:1}
.dm-stat-value.red{color:#b91c1c}
.dm-stat-sub{font-size:10px;margin-top:4px}
.dm-stat-bar{height:2px;background:var(--border);border-radius:1px;margin-top:8px;overflow:hidden}
.dm-stat-bar-fill{height:100%;border-radius:1px;background:#233264}

/* filter */
.dm-filter{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.dm-search-wrap{position:relative;flex:1;max-width:300px;min-width:180px}
.dm-search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--text-muted)}
.dm-search-form{margin:0}
.dm-search-input{width:100%;padding:8px 12px 8px 34px;font-size:13px;border:.5px solid var(--border);border-radius:var(--radius);background:var(--surface-1);color:var(--text-primary);outline:none}
.dm-search-input:focus{border-color:#233264}
.dm-pills{display:flex;gap:6px;flex-wrap:wrap}
.dm-pill{font-size:11px;font-weight:500;padding:5px 12px;border-radius:20px;border:.5px solid var(--border);background:var(--surface-2);color:var(--text-secondary);cursor:pointer;text-decoration:none;display:inline-block;white-space:nowrap;transition:all .12s}
.dm-pill:hover{border-color:var(--border-strong);color:var(--text-primary)}
.dm-pill.on{background:#233264;color:#fff;border-color:#233264}
.dm-pill.on-red{background:#b91c1c;color:#fff;border-color:#b91c1c}
.dm-pill.on-amber{background:#fffbeb;color:#b45309;border-color:#fde68a}

/* table */
.dm-table-wrap{border:.5px solid var(--border);border-radius:12px;overflow:hidden}
.dm-table{width:100%;border-collapse:collapse}
.dm-table thead th{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);padding:12px 16px;text-align:left;background:var(--surface-1);border-bottom:.5px solid var(--border)}
.dm-table thead th.r{text-align:right}
.dm-table thead th.c{text-align:center}
.dm-table tbody tr{border-bottom:.5px solid var(--border);transition:background .1s}
.dm-table tbody tr:last-child{border-bottom:none}
.dm-table tbody tr:hover{background:var(--surface-1)}
.dm-table tbody tr.exp-row{background:#fff8f8}
.dm-table tbody tr.exp-row:hover{background:#fff1f1}
.dm-table td{padding:13px 16px;vertical-align:middle}
.dm-table td.c{text-align:center}
.dm-table td.r{text-align:right}

/* agent cell */
.dm-agent-cell{display:flex;align-items:center;gap:11px}
.dm-avatar{width:36px;height:36px;border-radius:9px;background:#ECF0F8;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:500;color:#233264;flex-shrink:0;overflow:hidden;position:relative}
.dm-avatar img{width:100%;height:100%;object-fit:cover}
.dm-avatar-badge{position:absolute;bottom:-2px;right:-2px;width:14px;height:14px;border-radius:50%;background:#233264;border:2px solid var(--surface-2);display:flex;align-items:center;justify-content:center}
.dm-avatar-badge i{font-size:7px;color:#fff}
.dm-agent-name{font-size:13px;font-weight:500;color:var(--text-primary);line-height:1.2;text-decoration:none}
.dm-agent-name:hover{color:#233264}
.dm-agent-city{font-size:11px;color:var(--text-muted);margin-top:2px}

.dm-contact-email{font-size:12px;color:var(--text-primary)}
.dm-contact-phone{font-size:11px;color:var(--text-muted);margin-top:2px}

.dm-perf-num{font-size:14px;font-weight:500;color:var(--text-primary);text-align:center}
.dm-perf-rat{font-size:11px;color:#d97706;text-align:center;margin-top:2px}

.dm-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 9px;border-radius:20px}
.dm-badge-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.dm-badge.verified{background:#f0fdf4;color:#15803d}
.dm-badge.verified .dm-badge-dot{background:#22c55e}
.dm-badge.pending{background:#fffbeb;color:#b45309}
.dm-badge.pending .dm-badge-dot{background:#f59e0b;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

/* sub cell */
.dm-sub-cell{display:flex;align-items:center;gap:10px}
.dm-ring-wrap{flex-shrink:0;width:36px;height:36px;position:relative}
.dm-ring-svg{width:100%;height:100%;transform:rotate(-90deg)}
.dm-ring-track{fill:none;stroke:var(--border);stroke-width:3}
.dm-ring-fill{fill:none;stroke-width:3;stroke-linecap:round}
.dm-ring-label{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:500}
.dm-sub-plan{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)}
.dm-sub-date{font-size:11px;color:var(--text-primary);margin-top:2px}
.dm-sub-date.urgent{color:#b91c1c}
.dm-sub-date.warn{color:#b45309}
.dm-sub-date.muted{color:var(--text-muted)}

/* actions */
.dm-actions{display:flex;align-items:center;justify-content:flex-end;gap:3px}
.dm-act{width:30px;height:30px;border-radius:7px;border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;transition:all .12s}
.dm-act:hover{background:var(--surface-1);border-color:var(--border-strong);color:var(--text-primary)}
.dm-act.renew{color:#b91c1c;border-color:#fca5a5;background:#fff5f5}
.dm-act.renew:hover{background:#fef2f2}
.dm-act.approve{color:#15803d;border-color:#86efac}
.dm-act.approve:hover{background:#f0fdf4}
.dm-act.danger:hover{background:#fef2f2;color:#b91c1c;border-color:#fca5a5}

/* pagination */
.dm-pag{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--surface-1);border-top:.5px solid var(--border)}
.dm-pag-info{font-size:12px;color:var(--text-muted)}
.dm-pag-btns{display:flex;gap:4px}
.dm-pag-btn{min-width:28px;height:28px;padding:0 6px;border-radius:6px;border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s}
.dm-pag-btn.active{background:#233264;color:#fff;border-color:#233264}
.dm-pag-btn:hover:not(.active){background:var(--surface-1);border-color:var(--border-strong)}

/* mobile cards */
.dm-mobile-cards{display:none}
@media(max-width:700px){.dm-table-wrap{display:none}.dm-mobile-cards{display:block}.dm-stats{grid-template-columns:repeat(2,1fr)}}
.dm-card{background:var(--surface-2);border:.5px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:10px}
.dm-card.exp-card{border-color:#fca5a5}
.dm-card-banner{padding:8px 14px;display:flex;align-items:center;justify-content:space-between;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em}
.dm-card-banner.red{background:#fef2f2;color:#b91c1c;border-bottom:.5px solid #fca5a5}
.dm-card-banner.gray{background:var(--surface-1);color:var(--text-muted);border-bottom:.5px solid var(--border)}
.dm-card-body{padding:14px}
.dm-card-top{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px}
.dm-card-avatar{width:40px;height:40px;border-radius:10px;background:#ECF0F8;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#233264;flex-shrink:0;overflow:hidden}
.dm-card-avatar img{width:100%;height:100%;object-fit:cover}
.dm-card-info{flex:1;min-width:0}
.dm-card-name{font-size:14px;font-weight:500;color:var(--text-primary)}
.dm-card-meta{font-size:11px;color:var(--text-muted);margin-top:2px}
.dm-card-sub-row{display:flex;align-items:center;gap:10px;background:var(--surface-1);border-radius:var(--radius);padding:10px 12px;margin-bottom:10px}
.dm-card-actions{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
.dm-card-actions.no-approve{grid-template-columns:repeat(3,1fr)}
.dm-card-act{padding:9px;border-radius:var(--radius);border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s;font-size:12px;gap:5px}
.dm-card-act span{font-size:11px;font-weight:500}
.dm-card-act.primary{background:#233264;color:#fff;border-color:#233264}
.dm-card-act.success{background:#f0fdf4;color:#15803d;border-color:#86efac}
.dm-card-act.danger-soft{background:#fff5f5;color:#b91c1c;border-color:#fca5a5}
</style>

<div class="dm-page">

  {{-- eyebrow + title --}}
  <div class="dm-eyebrow">
    <div class="dm-eyebrow-line"></div>
    <span class="dm-eyebrow-text">Dream Mulk · Admin</span>
  </div>
  <h1 class="dm-page-title">Agents</h1>
  <p class="dm-page-sub">
    {{ $stats['total'] ?? 0 }} registered
    @if(($stats['pending'] ?? 0) > 0) · {{ $stats['pending'] }} pending approval @endif
    @if(($stats['expiring_soon'] ?? 0) > 0) · <span style="color:#b91c1c">{{ $stats['expiring_soon'] }} expiring soon</span> @endif
    · week of {{ $briefing['week_label'] ?? '' }}
  </p>

  {{-- ── WEEKLY BRIEFING ─────────────────────────────────────── --}}
  @if(isset($briefing))
  <div class="dm-briefing">
    <div class="dm-briefing-head">
      <div class="dm-briefing-head-left">
        <div class="dm-briefing-icon" aria-hidden="true"><i class="ti ti-chart-line"></i></div>
        <div>
          <div class="dm-briefing-title">Weekly briefing</div>
          <div class="dm-briefing-week">{{ $briefing['week_label'] ?? '' }}</div>
        </div>
      </div>
      <div class="dm-briefing-totals">
        <div class="dm-bt">
          <div class="dm-bt-num">{{ collect($briefing['top_posters'] ?? [])->sum('count') }}</div>
          <div class="dm-bt-label">New posts</div>
        </div>
        <div class="dm-bt">
          <div class="dm-bt-num {{ count($briefing['expiring'] ?? []) > 0 ? 'warn' : '' }}">{{ count($briefing['expiring'] ?? []) }}</div>
          <div class="dm-bt-label">Expiring</div>
        </div>
        <div class="dm-bt">
          <div class="dm-bt-num">{{ count($briefing['expired'] ?? []) }}</div>
          <div class="dm-bt-label">Expired</div>
        </div>
      </div>
    </div>

    <div class="dm-briefing-cols">

      {{-- Top posters --}}
      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot"></span>Top posters this week</div>
        @forelse($briefing['top_posters'] ?? [] as $i => $p)
        <div class="dm-poster-row">
          <span class="dm-rank {{ $i === 0 ? 'gold' : '' }}">{{ $i + 1 }}</span>
          <div class="dm-av-sm">
            @if($p['image'])<img src="{{ asset($p['image']) }}" alt="">@else{{ strtoupper(substr($p['name'],0,2)) }}@endif
          </div>
          <span class="dm-poster-name">{{ $p['name'] }}</span>
          <span class="dm-poster-count">{{ $p['count'] }} posts</span>
        </div>
        @empty
        <div class="dm-empty-col">
          <i class="ti ti-inbox" aria-hidden="true"></i>
          <p>No listings posted this week yet.</p>
        </div>
        @endforelse
      </div>

      {{-- Expiring this week --}}
      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot red"></span>Expiring this week</div>
        @forelse($briefing['expiring'] ?? [] as $e)
        <div class="dm-expiry-row">
          <span class="dm-exp-days {{ $e['urg_class'] }}">{{ $e['day_label'] }}</span>
          <div class="dm-av-sm">
            @if($e['image'])<img src="{{ asset($e['image']) }}" alt="">@else{{ strtoupper(substr($e['name'],0,2)) }}@endif
          </div>
          <span class="dm-exp-name">{{ $e['name'] }}</span>
          <span class="dm-exp-plan">{{ $e['plan'] }}</span>
          <a href="{{ route('admin.agents.edit', $e['id']) }}" class="dm-renew-btn">Renew</a>
        </div>
        @empty
        <div class="dm-empty-col">
          <i class="ti ti-shield-check" aria-hidden="true"></i>
          <p>No expirations this week. All healthy.</p>
        </div>
        @endforelse
      </div>

      {{-- Expired this week --}}
      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot gray"></span>Expired this week</div>
        @forelse($briefing['expired'] ?? [] as $x)
        <div class="dm-expiry-row">
          <span class="dm-exp-days muted">{{ $x['ended'] }}</span>
          <div class="dm-av-sm" style="opacity:.45">
            @if($x['image'])<img src="{{ asset($x['image']) }}" alt="">@else{{ strtoupper(substr($x['name'],0,2)) }}@endif
          </div>
          <span class="dm-exp-name faded">{{ $x['name'] }}</span>
          <span class="dm-exp-plan">{{ $x['plan'] }}</span>
          <a href="{{ route('admin.agents.edit', $x['id']) }}" class="dm-react-btn">Reactivate</a>
        </div>
        @empty
        <div class="dm-empty-col">
          <i class="ti ti-mood-smile" aria-hidden="true"></i>
          <p>Nobody expired this week.</p>
        </div>
        @endforelse
      </div>

    </div>
  </div>
  @endif

  {{-- ── STATS ────────────────────────────────────────────────── --}}
  <div class="dm-stats">
    <div class="dm-stat">
      <div class="dm-stat-label">Total agents</div>
      <div class="dm-stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
      <div class="dm-stat-bar"><div class="dm-stat-bar-fill" style="width:100%"></div></div>
    </div>
    <div class="dm-stat">
      <div class="dm-stat-label">Verified</div>
      <div class="dm-stat-value">{{ number_format($stats['verified'] ?? 0) }}</div>
      <div class="dm-stat-bar"><div class="dm-stat-bar-fill" style="width:{{ ($stats['total'] ?? 0) > 0 ? round(($stats['verified'] / $stats['total']) * 100) : 0 }}%"></div></div>
    </div>
    <div class="dm-stat">
      <div class="dm-stat-label">Pending</div>
      <div class="dm-stat-value">{{ number_format($stats['pending'] ?? 0) }}</div>
      @if(($stats['pending'] ?? 0) > 0)<div class="dm-stat-sub" style="color:#b45309">Action required</div>@endif
    </div>
    <div class="dm-stat">
      <div class="dm-stat-label">Active plans</div>
      <div class="dm-stat-value">{{ number_format($stats['active_subs'] ?? 0) }}</div>
      <div class="dm-stat-bar"><div class="dm-stat-bar-fill" style="width:{{ ($stats['total'] ?? 0) > 0 ? round(($stats['active_subs'] / $stats['total']) * 100) : 0 }}%;background:#C9A961"></div></div>
    </div>
    <div class="dm-stat">
      <div class="dm-stat-label">Expiring ≤7d</div>
      <div class="dm-stat-value {{ ($stats['expiring_soon'] ?? 0) > 0 ? 'red' : '' }}">{{ number_format($stats['expiring_soon'] ?? 0) }}</div>
      @if(($stats['expiring_soon'] ?? 0) > 0)<div class="dm-stat-sub" style="color:#b91c1c">Renewals needed</div>@endif
    </div>
  </div>

  {{-- ── FILTER BAR ───────────────────────────────────────────── --}}
  <div class="dm-filter">
    <div class="dm-search-wrap">
      <i class="ti ti-search" aria-hidden="true"></i>
      <form method="GET" action="{{ route('admin.agents.index') }}" class="dm-search-form">
        <input class="dm-search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search agents…" aria-label="Search agents">
      </form>
    </div>
    <div class="dm-pills">
      <a href="{{ route('admin.agents.index') }}" class="dm-pill {{ !request()->hasAny(['status','type','expiry']) ? 'on' : '' }}">All</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('status','page'),['status'=>'verified'])) }}" class="dm-pill {{ request('status')=='verified' ? 'on' : '' }}">Verified</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('status','page'),['status'=>'pending'])) }}" class="dm-pill {{ request('status')=='pending' ? 'on' : '' }}">Pending</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('expiry','page'),['expiry'=>'expiring'])) }}" class="dm-pill {{ request('expiry')=='expiring' ? 'on-red' : '' }}">Expiring ≤7d</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('expiry','page'),['expiry'=>'expired'])) }}" class="dm-pill {{ request('expiry')=='expired' ? 'on-red' : '' }}">Expired</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('expiry','page'),['expiry'=>'active'])) }}" class="dm-pill {{ request('expiry')=='active' ? 'on' : '' }}">Active plan</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('type','page'),['type'=>'independent'])) }}" class="dm-pill {{ request('type')=='independent' ? 'on' : '' }}">Independent</a>
      <a href="{{ route('admin.agents.index', array_merge(request()->except('type','page'),['type'=>'company'])) }}" class="dm-pill {{ request('type')=='company' ? 'on' : '' }}">Company</a>
    </div>
  </div>

  {{-- expiry computed inline per row below --}}

  {{-- ── DESKTOP TABLE ───────────────────────────────────────── --}}
  <div class="dm-table-wrap">
    <table class="dm-table" aria-label="Agents directory">
      <thead>
        <tr>
          <th>Agent</th>
          <th>Contact</th>
          <th class="c">Listings</th>
          <th class="c">Status</th>
          <th>Subscription</th>
          <th class="r">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($agents as $agent)
        <tr class="{{ $agent->dm_expiring ? 'exp-row' : '' }}">

          <td>
            <div class="dm-agent-cell">
              <div class="dm-avatar">
                @if($agent->profile_image)<img src="{{ asset($agent->profile_image) }}" alt="">@else{{ strtoupper(substr($agent->agent_name,0,2)) }}@endif
                @if($agent->type === 'company')
                  <div class="dm-avatar-badge"><i class="ti ti-building" aria-hidden="true"></i></div>
                @endif
              </div>
              <div>
                <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-agent-name">{{ $agent->agent_name }}</a>
                <div class="dm-agent-city"><i class="ti ti-map-pin" style="font-size:10px;margin-right:3px" aria-hidden="true"></i>{{ $agent->city ?? 'N/A' }}</div>
              </div>
            </div>
          </td>

          <td>
            <div class="dm-contact-email">{{ $agent->primary_email }}</div>
            @if($agent->primary_phone)<div class="dm-contact-phone">{{ $agent->primary_phone }}</div>@endif
          </td>

          <td class="c">
            <div class="dm-perf-num">{{ $agent->properties_count ?? 0 }}</div>
            @if(($agent->overall_rating ?? 0) > 0)<div class="dm-perf-rat">{{ number_format($agent->overall_rating,1) }} ★</div>@endif
          </td>

          <td class="c">
            @if($agent->is_verified)
              <span class="dm-badge verified"><span class="dm-badge-dot"></span>Verified</span>
            @else
              <span class="dm-badge pending"><span class="dm-badge-dot"></span>Pending</span>
            @endif
          </td>

          <td>
            <div class="dm-sub-cell">
              <div class="dm-ring-wrap" aria-hidden="true">
                <svg class="dm-ring-svg" viewBox="0 0 36 36">
                  <circle class="dm-ring-track" cx="18" cy="18" r="15"/>
                  @if($days !== null)
                  <circle class="dm-ring-fill" cx="18" cy="18" r="15"
                    stroke="{{ $agent->dm_ring_color }}"
                    stroke-dasharray="{{ $agent->dm_expired ? 0 : $agent->dm_dash }} {{ $agent->dm_circumference }}"/>
                  @endif
                </svg>
                <div class="dm-ring-label" style="color:{{ $agent->dm_label_color }}">
                  @if($agent->dm_expired)✕@elseif($agent->dm_days !== null){{ $agent->dm_days }}d@else—@endif
                </div>
              </div>
              <div>
                <div class="dm-sub-plan">{{ $agent->current_plan ?? 'Free' }}</div>
                <div class="dm-sub-date {{ $agent->dm_date_class }}">
                  @if($agent->dm_end_date){{ $agent->dm_expired ? 'Ended' : 'Ends' }} {{ $agent->dm_end_date }}@else No subscription@endif
                </div>
              </div>
            </div>
          </td>

          <td class="r">
            <div class="dm-actions">
              @if($agent->dm_expiring || $agent->dm_expired)
                <a href="{{ route('admin.agents.edit', $agent->id) }}" class="dm-act renew" title="Renew plan" aria-label="Renew plan"><i class="ti ti-refresh" aria-hidden="true"></i></a>
              @endif
              <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-act" title="View" aria-label="View agent"><i class="ti ti-eye" aria-hidden="true"></i></a>
              <a href="{{ route('admin.agents.edit', $agent->id) }}" class="dm-act" title="Edit" aria-label="Edit agent"><i class="ti ti-pencil" aria-hidden="true"></i></a>
              @if(!$agent->is_verified)
                <form action="{{ route('admin.agents.verify', $agent->id) }}" method="POST" style="display:inline">@csrf
                  <button type="submit" class="dm-act approve" title="Approve" aria-label="Approve agent"><i class="ti ti-check" aria-hidden="true"></i></button>
                </form>
              @endif
              <form action="{{ route('admin.agents.delete', $agent->id) }}" method="POST" style="display:inline">@csrf @method('DELETE')
                <button type="submit" class="dm-act danger"
                  onclick="return confirm('Permanently delete this agent and all their listings?')"
                  title="Delete" aria-label="Delete agent"><i class="ti ti-trash" aria-hidden="true"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding:48px 24px;text-align:center">
            <i class="ti ti-user-off" style="font-size:28px;color:var(--border-strong);display:block;margin-bottom:10px" aria-hidden="true"></i>
            <p style="font-size:14px;font-weight:500;color:var(--text-primary);margin-bottom:4px">No agents found</p>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">Try adjusting your search or filters.</p>
            <a href="{{ route('admin.agents.index') }}" style="font-size:12px;font-weight:500;color:#233264">Clear filters</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <div class="dm-pag">
      <span class="dm-pag-info">
        Showing {{ $agents->firstItem() ?? 0 }}–{{ $agents->lastItem() ?? 0 }} of {{ $agents->total() }} agents
      </span>
      <div class="dm-pag-btns">
        @if($agents->onFirstPage())
          <span class="dm-pag-btn" aria-disabled="true"><i class="ti ti-chevron-left" aria-hidden="true"></i></span>
        @else
          <a href="{{ $agents->previousPageUrl() }}" class="dm-pag-btn" aria-label="Previous page"><i class="ti ti-chevron-left" aria-hidden="true"></i></a>
        @endif
        @foreach($agents->getUrlRange(1, $agents->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="dm-pag-btn {{ $page == $agents->currentPage() ? 'active' : '' }}" aria-label="Page {{ $page }}" {{ $page == $agents->currentPage() ? 'aria-current=page' : '' }}>{{ $page }}</a>
        @endforeach
        @if($agents->hasMorePages())
          <a href="{{ $agents->nextPageUrl() }}" class="dm-pag-btn" aria-label="Next page"><i class="ti ti-chevron-right" aria-hidden="true"></i></a>
        @else
          <span class="dm-pag-btn" aria-disabled="true"><i class="ti ti-chevron-right" aria-hidden="true"></i></span>
        @endif
      </div>
    </div>
  </div>

  {{-- ── MOBILE CARDS ─────────────────────────────────────────── --}}
  <div class="dm-mobile-cards" aria-label="Agents list">
    @forelse($agents as $agent)
    <div class="dm-card {{ $agent->dm_expiring ? 'exp-card' : '' }}">
      @if($agent->dm_expiring)
        <div class="dm-card-banner red">
          <span><i class="ti ti-hourglass-empty" style="margin-right:5px" aria-hidden="true"></i>{{ $agent->dm_days == 0 ? 'Expires today' : 'Expires in '.$agent->dm_days.' days' }}</span>
          <a href="{{ route('admin.agents.edit', $agent->id) }}" style="text-decoration:underline;color:#b91c1c;font-weight:500">Renew now</a>
        </div>
      @elseif($agent->dm_expired)
        <div class="dm-card-banner gray">
          <span>Subscription ended {{ $agent->dm_end_date }}</span>
          <a href="{{ route('admin.agents.edit', $agent->id) }}" style="text-decoration:underline;color:var(--text-secondary)">Reactivate</a>
        </div>
      @endif
      <div class="dm-card-body">
        <div class="dm-card-top">
          <div class="dm-card-avatar">
            @if($agent->profile_image)<img src="{{ asset($agent->profile_image) }}" alt="">@else{{ strtoupper(substr($agent->agent_name,0,2)) }}@endif
          </div>
          <div class="dm-card-info">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
              <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-card-name">{{ $agent->agent_name }}</a>
              @if($agent->is_verified)
                <span class="dm-badge verified" style="font-size:10px;padding:2px 7px"><span class="dm-badge-dot"></span>Verified</span>
              @else
                <span class="dm-badge pending" style="font-size:10px;padding:2px 7px"><span class="dm-badge-dot"></span>Pending</span>
              @endif
            </div>
            <div class="dm-card-meta">
              {{ $agent->city ?? 'N/A' }} · {{ $agent->properties_count ?? 0 }} listings
              @if(($agent->overall_rating ?? 0) > 0) · <span style="color:#d97706">{{ number_format($agent->overall_rating,1) }} ★</span>@endif
            </div>
            <div class="dm-card-meta" style="margin-top:1px">{{ $agent->primary_email }}</div>
          </div>
        </div>

        {{-- subscription widget --}}
        <div class="dm-card-sub-row">
          <div class="dm-ring-wrap" aria-hidden="true">
            <svg class="dm-ring-svg" viewBox="0 0 36 36">
              <circle class="dm-ring-track" cx="18" cy="18" r="15"/>
              @if($days !== null)
              <circle class="dm-ring-fill" cx="18" cy="18" r="15" stroke="{{ $agent->dm_ring_color }}" stroke-dasharray="{{ $agent->dm_expired ? 0 : $agent->dm_dash }} {{ $agent->dm_circumference }}"/>
              @endif
            </svg>
            <div class="dm-ring-label" style="color:{{ $agent->dm_label_color }}">
              @if($agent->dm_expired)✕@elseif($agent->dm_days !== null){{ $agent->dm_days }}d@else—@endif
            </div>
          </div>
          <div>
            <div class="dm-sub-plan">{{ $agent->current_plan ?? 'Free' }}</div>
            <div class="dm-sub-date {{ $agent->dm_date_class }}" style="font-size:12px">
              @if($agent->agent->dm_end_date){{ $agent->dm_expired ? 'Ended' : 'Ends' }} {{ $agent->dm_end_date }}@else No subscription@endif
            </div>
          </div>
        </div>

        <div class="dm-card-actions {{ $agent->is_verified ? 'no-approve' : '' }}">
          <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-card-act primary"><i class="ti ti-eye" aria-hidden="true"></i><span>View</span></a>
          <a href="{{ route('admin.agents.edit', $agent->id) }}" class="dm-card-act"><i class="ti ti-pencil" aria-hidden="true"></i><span>Edit</span></a>
          @if(!$agent->is_verified)
            <form action="{{ route('admin.agents.verify', $agent->id) }}" method="POST">@csrf
              <button type="submit" class="dm-card-act success" style="width:100%"><i class="ti ti-check" aria-hidden="true"></i><span>Approve</span></button>
            </form>
          @endif
          <form action="{{ route('admin.agents.delete', $agent->id) }}" method="POST">@csrf @method('DELETE')
            <button type="submit" class="dm-card-act danger-soft" style="width:100%"
              onclick="return confirm('Delete this agent?')">
              <i class="ti ti-trash" aria-hidden="true"></i><span>Delete</span>
            </button>
          </form>
        </div>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:48px 24px;background:var(--surface-2);border:.5px solid var(--border);border-radius:12px">
      <i class="ti ti-user-off" style="font-size:28px;color:var(--border-strong);display:block;margin-bottom:10px" aria-hidden="true"></i>
      <p style="font-size:14px;font-weight:500;color:var(--text-primary);margin-bottom:4px">No agents found</p>
      <a href="{{ route('admin.agents.index') }}" style="font-size:12px;font-weight:500;color:#233264">Clear filters</a>
    </div>
    @endforelse
    <div style="padding:12px 0">{{ $agents->withQueryString()->links() }}</div>
  </div>

</div>
@endsection

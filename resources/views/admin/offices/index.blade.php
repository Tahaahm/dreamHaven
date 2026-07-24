@extends('layouts.admin-layout')
@section('title', 'Offices Directory')
@section('content')
<style>
*{box-sizing:border-box}
.dm-page{padding:32px 24px 80px;max-width:1100px;margin:0 auto}
.dm-eyebrow{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.dm-eyebrow-line{width:20px;height:2px;background:#C9A961;border-radius:2px;flex-shrink:0}
.dm-eyebrow-text{font-size:11px;letter-spacing:.12em;color:var(--text-muted);text-transform:uppercase}
.dm-page-title{font-size:26px;font-weight:500;color:var(--text-primary);margin-bottom:3px}
.dm-page-sub{font-size:13px;color:var(--text-secondary);margin-bottom:28px}
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
.dm-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:22px}
@media(max-width:700px){.dm-stats{grid-template-columns:repeat(2,1fr)}}
.dm-stat{background:var(--surface-1);border-radius:var(--radius);padding:14px 16px}
.dm-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:6px}
.dm-stat-value{font-size:22px;font-weight:500;color:var(--text-primary);line-height:1}
.dm-stat-value.red{color:#b91c1c}
.dm-stat-sub{font-size:10px;margin-top:4px}
.dm-stat-bar{height:2px;background:var(--border);border-radius:1px;margin-top:8px;overflow:hidden}
.dm-stat-bar-fill{height:100%;border-radius:1px;background:#233264}
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
.dm-agent-cell{display:flex;align-items:center;gap:11px}
.dm-avatar{width:36px;height:36px;border-radius:9px;background:#ECF0F8;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:500;color:#233264;flex-shrink:0;overflow:hidden}
.dm-avatar img{width:100%;height:100%;object-fit:cover}
.dm-agent-name{font-size:13px;font-weight:500;color:var(--text-primary);line-height:1.2;text-decoration:none}
.dm-agent-name:hover{color:#233264}
.dm-agent-city{font-size:11px;color:var(--text-muted);margin-top:2px}
.dm-contact-email{font-size:12px;color:var(--text-primary)}
.dm-contact-phone{font-size:11px;color:var(--text-muted);margin-top:2px}
.dm-perf-num{font-size:14px;font-weight:500;color:var(--text-primary);text-align:center}
.dm-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 9px;border-radius:20px}
.dm-badge-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.dm-badge.verified{background:#f0fdf4;color:#15803d}
.dm-badge.verified .dm-badge-dot{background:#22c55e}
.dm-badge.pending{background:#fffbeb;color:#b45309}
.dm-badge.pending .dm-badge-dot{background:#f59e0b;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
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
.dm-actions{display:flex;align-items:center;justify-content:flex-end;gap:3px}
.dm-act{width:30px;height:30px;border-radius:7px;border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;transition:all .12s}
.dm-act:hover{background:var(--surface-1);border-color:var(--border-strong);color:var(--text-primary)}
.dm-act.renew{color:#b91c1c;border-color:#fca5a5;background:#fff5f5}
.dm-act.renew:hover{background:#fef2f2}
.dm-act.approve{color:#15803d;border-color:#86efac}
.dm-act.approve:hover{background:#f0fdf4}
.dm-act.danger:hover{background:#fef2f2;color:#b91c1c;border-color:#fca5a5}
.dm-pag{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--surface-1);border-top:.5px solid var(--border)}
.dm-pag-info{font-size:12px;color:var(--text-muted)}
.dm-pag-btns{display:flex;gap:4px}
.dm-pag-btn{min-width:28px;height:28px;padding:0 6px;border-radius:6px;border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s}
.dm-pag-btn.active{background:#233264;color:#fff;border-color:#233264}
.dm-pag-btn:hover:not(.active){background:var(--surface-1);border-color:var(--border-strong)}
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
.dm-card-name{font-size:14px;font-weight:500;color:var(--text-primary);text-decoration:none}
.dm-card-meta{font-size:11px;color:var(--text-muted);margin-top:2px}
.dm-card-sub-row{display:flex;align-items:center;gap:10px;background:var(--surface-1);border-radius:var(--radius);padding:10px 12px;margin-bottom:10px}
.dm-card-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
.dm-card-actions.with-approve{grid-template-columns:repeat(4,1fr)}
.dm-card-act{padding:9px;border-radius:var(--radius);border:.5px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s;font-size:12px;gap:5px}
.dm-card-act span{font-size:11px;font-weight:500}
.dm-card-act.primary{background:#233264;color:#fff;border-color:#233264}
.dm-card-act.success{background:#f0fdf4;color:#15803d;border-color:#86efac}
.dm-card-act.danger-soft{background:#fff5f5;color:#b91c1c;border-color:#fca5a5}
</style>

<div class="dm-page">

  <div class="dm-eyebrow">
    <div class="dm-eyebrow-line"></div>
    <span class="dm-eyebrow-text">Dream Mulk · Admin</span>
  </div>
  <h1 class="dm-page-title">Offices</h1>
  <p class="dm-page-sub">
    {{ $stats['total'] ?? 0 }} registered agencies
    @if(($stats['pending'] ?? 0) > 0) · {{ $stats['pending'] }} pending@endif
    @if(($stats['expiring_soon'] ?? 0) > 0) · <span style="color:#b91c1c">{{ $stats['expiring_soon'] }} expiring soon</span>@endif
    · week of {{ $briefing['week_label'] ?? '' }}
  </p>

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

      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot"></span>Top posters this week</div>
        @forelse($briefing['top_posters'] ?? [] as $i => $p)
        <div class="dm-poster-row">
          <span class="dm-rank {{ $i === 0 ? 'gold' : '' }}">{{ $i + 1 }}</span>
          <div class="dm-av-sm">
            @if($p['image'])<img src="{{ asset($p['image']) }}" alt="">@else<i class="ti ti-building" style="font-size:11px;color:#233264" aria-hidden="true"></i>@endif
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

      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot red"></span>Expiring this week</div>
        @forelse($briefing['expiring'] ?? [] as $e)
        @php $urgClass = $e['days'] == 0 ? 'urgent' : ($e['days'] <= 3 ? 'urgent' : 'warn'); $dayLabel = $e['days'] == 0 ? 'Today' : ($e['days'] == 1 ? 'Tomorrow' : $e['days'].'d'); @endphp
        <div class="dm-expiry-row">
          <span class="dm-exp-days {{ $urgClass }}">{{ $dayLabel }}</span>
          <div class="dm-av-sm">
            @if($e['image'])<img src="{{ asset($e['image']) }}" alt="">@else<i class="ti ti-building" style="font-size:11px;color:#233264" aria-hidden="true"></i>@endif
          </div>
          <span class="dm-exp-name">{{ $e['name'] }}</span>
          <span class="dm-exp-plan">{{ $e['plan'] }}</span>
          <a href="{{ route('admin.offices.edit', $e['id']) }}" class="dm-renew-btn">Renew</a>
        </div>
        @empty
        <div class="dm-empty-col">
          <i class="ti ti-shield-check" aria-hidden="true"></i>
          <p>No expirations this week. All healthy.</p>
        </div>
        @endforelse
      </div>

      <div class="dm-bcol">
        <div class="dm-col-label"><span class="dm-col-dot gray"></span>Expired this week</div>
        @forelse($briefing['expired'] ?? [] as $x)
        <div class="dm-expiry-row">
          <span class="dm-exp-days muted">{{ $x['ended'] }}</span>
          <div class="dm-av-sm" style="opacity:.45">
            @if($x['image'])<img src="{{ asset($x['image']) }}" alt="">@else<i class="ti ti-building" style="font-size:11px;color:#233264" aria-hidden="true"></i>@endif
          </div>
          <span class="dm-exp-name faded">{{ $x['name'] }}</span>
          <span class="dm-exp-plan">{{ $x['plan'] }}</span>
          <a href="{{ route('admin.offices.edit', $x['id']) }}" class="dm-react-btn">Reactivate</a>
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

  @php $verPct = ($stats['total'] ?? 0) > 0 ? round(($stats['verified'] / $stats['total']) * 100) : 0; @endphp
  <div class="dm-stats">
    <div class="dm-stat">
      <div class="dm-stat-label">Total offices</div>
      <div class="dm-stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
      <div class="dm-stat-bar"><div class="dm-stat-bar-fill" style="width:100%"></div></div>
    </div>
    <div class="dm-stat">
      <div class="dm-stat-label">Verified</div>
      <div class="dm-stat-value">{{ number_format($stats['verified'] ?? 0) }}</div>
      <div class="dm-stat-bar"><div class="dm-stat-bar-fill" style="width:{{ $verPct }}%"></div></div>
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

  <div class="dm-filter">
    <div class="dm-search-wrap">
      <i class="ti ti-search" aria-hidden="true"></i>
      <form method="GET" action="{{ route('admin.offices.index') }}" class="dm-search-form">
        <input class="dm-search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search offices…" aria-label="Search offices">
      </form>
    </div>
    <div class="dm-pills">
      <a href="{{ route('admin.offices.index') }}" class="dm-pill {{ !request()->hasAny(['status','expiry']) ? 'on' : '' }}">All</a>
      <a href="{{ route('admin.offices.index', array_merge(request()->except('status','page'),['status'=>'verified'])) }}" class="dm-pill {{ request('status')=='verified' ? 'on' : '' }}">Verified</a>
      <a href="{{ route('admin.offices.index', array_merge(request()->except('status','page'),['status'=>'pending'])) }}" class="dm-pill {{ request('status')=='pending' ? 'on' : '' }}">Pending</a>
      <a href="{{ route('admin.offices.index', array_merge(request()->except('expiry','page'),['expiry'=>'expiring'])) }}" class="dm-pill {{ request('expiry')=='expiring' ? 'on-red' : '' }}">Expiring ≤7d</a>
      <a href="{{ route('admin.offices.index', array_merge(request()->except('expiry','page'),['expiry'=>'expired'])) }}" class="dm-pill {{ request('expiry')=='expired' ? 'on-red' : '' }}">Expired</a>
      <a href="{{ route('admin.offices.index', array_merge(request()->except('expiry','page'),['expiry'=>'active'])) }}" class="dm-pill {{ request('expiry')=='active' ? 'on' : '' }}">Active plan</a>
    </div>
  </div>

  {{-- expiry computed inline per row below --}}

  {{-- Desktop table --}}
  <div class="dm-table-wrap">
    <table class="dm-table" aria-label="Offices directory">
      <thead>
        <tr>
          <th>Office</th>
          <th>Contact</th>
          <th class="c">Listings</th>
          <th class="c">Status</th>
          <th>Subscription</th>
          <th class="r">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($offices as $office)
        @php
          $__sub = $office->subscription;
          $__end = $__sub && $__sub->end_date ? \Carbon\Carbon::parse($__sub->end_date) : null;
          $__start = $__sub && $__sub->start_date ? \Carbon\Carbon::parse($__sub->start_date) : null;
          $__days = $__end ? now()->startOfDay()->diffInDays($__end->startOfDay(), false) : null;
          $active = $__sub && $__sub->status === 'active' && $__days !== null && $__days >= 0;
          $expired = $__sub && $__days !== null && $__days < 0;
          $expiring = $active && $__days <= 7;
          $pct = 0;
          if ($__start && $__end && $__end->gt($__start)) {
            $__total = $__start->diffInDays($__end);
            $__left = max(0, $__days ?? 0);
            $pct = $__total > 0 ? (int)round(($__left / $__total) * 100) : 0;
          }
          $days = $__days;
          $end = $__end;
          $circumference = 94.25;
          $dash = round(($pct / 100) * $circumference, 2);
          $ringColor = $expired ? '#ef4444' : ($expiring ? '#ef4444' : ($pct < 25 ? '#f59e0b' : '#22c55e'));
          $labelColor = $expired ? '#b91c1c' : ($expiring ? '#b91c1c' : ($pct < 25 ? '#92400e' : '#15803d'));
          $dateClass = ($expired || $expiring) ? 'urgent' : ($pct < 25 ? 'warn' : '');
        @endphp
        <tr class="{{ $expiring ? 'exp-row' : '' }}">
          <td>
            <div class="dm-agent-cell">
              <div class="dm-avatar">
                @if($office->logo)<img src="{{ asset($office->logo) }}" alt="">@else<i class="ti ti-building" style="font-size:14px;color:#233264;opacity:.5" aria-hidden="true"></i>@endif
              </div>
              <div>
                <a href="{{ route('admin.offices.show', $office->id) }}" class="dm-agent-name">{{ $office->company_name }}</a>
                <div class="dm-agent-city"><i class="ti ti-map-pin" style="font-size:10px;margin-right:3px" aria-hidden="true"></i>{{ $office->city ?? 'N/A' }}</div>
              </div>
            </div>
          </td>
          <td>
            <div class="dm-contact-email">{{ $office->email_address }}</div>
            @if($office->phone_number)<div class="dm-contact-phone">{{ $office->phone_number }}</div>@endif
          </td>
          <td class="c"><div class="dm-perf-num">{{ $office->owned_properties_count ?? 0 }}</div></td>
          <td class="c">
            @if($office->is_verified)
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
                  <circle class="dm-ring-fill" cx="18" cy="18" r="15" stroke="{{ $ringColor }}" stroke-dasharray="{{ $expired ? 0 : $dash }} {{ $circumference }}"/>
                  @endif
                </svg>
                <div class="dm-ring-label" style="color:{{ $labelColor ?? 'var(--text-muted)' }}">
                  @if($expired)✕@elseif($days !== null){{ $days }}d@else—@endif
                </div>
              </div>
              <div>
                <div class="dm-sub-plan">{{ $office->current_plan ?? 'Free' }}</div>
                <div class="dm-sub-date {{ $dateClass ?? 'muted' }}">
                  @if($end){{ $expired ? 'Ended' : 'Ends' }} {{ $end->format('d M Y') }}@else No subscription@endif
                </div>
              </div>
            </div>
          </td>
          <td class="r">
            <div class="dm-actions">
              @if($expiring || $expired)
                <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-act renew" title="Renew plan" aria-label="Renew plan"><i class="ti ti-refresh" aria-hidden="true"></i></a>
              @endif
              <a href="{{ route('admin.offices.show', $office->id) }}" class="dm-act" title="View" aria-label="View office"><i class="ti ti-eye" aria-hidden="true"></i></a>
              <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-act" title="Edit" aria-label="Edit office"><i class="ti ti-pencil" aria-hidden="true"></i></a>
              @if(!$office->is_verified)
                <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST" style="display:inline">@csrf
                  <button type="submit" class="dm-act approve" title="Verify" aria-label="Verify office"><i class="ti ti-check" aria-hidden="true"></i></button>
                </form>
              @endif
              <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST" style="display:inline">@csrf @method('DELETE')
                <button type="submit" class="dm-act danger"
                  onclick="return confirm('Permanently delete this office?')"
                  title="Delete" aria-label="Delete office"><i class="ti ti-trash" aria-hidden="true"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding:48px 24px;text-align:center">
            <i class="ti ti-building-off" style="font-size:28px;color:var(--border-strong);display:block;margin-bottom:10px" aria-hidden="true"></i>
            <p style="font-size:14px;font-weight:500;color:var(--text-primary);margin-bottom:4px">No offices found</p>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">Try adjusting your search or filters.</p>
            <a href="{{ route('admin.offices.index') }}" style="font-size:12px;font-weight:500;color:#233264">Clear filters</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="dm-pag">
      <span class="dm-pag-info">Showing {{ $offices->firstItem() ?? 0 }}–{{ $offices->lastItem() ?? 0 }} of {{ $offices->total() }} offices</span>
      <div class="dm-pag-btns">
        @if($offices->onFirstPage())
          <span class="dm-pag-btn"><i class="ti ti-chevron-left" aria-hidden="true"></i></span>
        @else
          <a href="{{ $offices->previousPageUrl() }}" class="dm-pag-btn" aria-label="Previous page"><i class="ti ti-chevron-left" aria-hidden="true"></i></a>
        @endif
        @foreach($offices->getUrlRange(1, $offices->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="dm-pag-btn {{ $page == $offices->currentPage() ? 'active' : '' }}" aria-label="Page {{ $page }}">{{ $page }}</a>
        @endforeach
        @if($offices->hasMorePages())
          <a href="{{ $offices->nextPageUrl() }}" class="dm-pag-btn" aria-label="Next page"><i class="ti ti-chevron-right" aria-hidden="true"></i></a>
        @else
          <span class="dm-pag-btn"><i class="ti ti-chevron-right" aria-hidden="true"></i></span>
        @endif
      </div>
    </div>
  </div>

  {{-- Mobile cards --}}
  <div class="dm-mobile-cards" aria-label="Offices list">
    @forelse($offices as $office)
    @php
          $__sub = $office->subscription;
          $__end = $__sub && $__sub->end_date ? \Carbon\Carbon::parse($__sub->end_date) : null;
          $__start = $__sub && $__sub->start_date ? \Carbon\Carbon::parse($__sub->start_date) : null;
          $__days = $__end ? now()->startOfDay()->diffInDays($__end->startOfDay(), false) : null;
          $active = $__sub && $__sub->status === 'active' && $__days !== null && $__days >= 0;
          $expired = $__sub && $__days !== null && $__days < 0;
          $expiring = $active && $__days <= 7;
          $pct = 0;
          if ($__start && $__end && $__end->gt($__start)) {
            $__total = $__start->diffInDays($__end);
            $__left = max(0, $__days ?? 0);
            $pct = $__total > 0 ? (int)round(($__left / $__total) * 100) : 0;
          }
          $days = $__days;
          $end = $__end;
          $circumference = 94.25;
          $dash = round(($pct / 100) * $circumference, 2);
          $ringColor = $expired ? '#ef4444' : ($expiring ? '#ef4444' : ($pct < 25 ? '#f59e0b' : '#22c55e'));
          $labelColor = $expired ? '#b91c1c' : ($expiring ? '#b91c1c' : ($pct < 25 ? '#92400e' : '#15803d'));
          $dateClass = ($expired || $expiring) ? 'urgent' : ($pct < 25 ? 'warn' : '');
        @endphp
    <div class="dm-card {{ $expiring ? 'exp-card' : '' }}">
      @if($expiring)
        <div class="dm-card-banner red">
          <span><i class="ti ti-hourglass-empty" style="margin-right:5px" aria-hidden="true"></i>{{ $days == 0 ? 'Expires today' : 'Expires in '.$days.' days' }}</span>
          <a href="{{ route('admin.offices.edit', $office->id) }}" style="text-decoration:underline;color:#b91c1c;font-weight:500">Renew now</a>
        </div>
      @elseif($expired)
        <div class="dm-card-banner gray">
          <span>Subscription ended {{ $end?->format('d M Y') }}</span>
          <a href="{{ route('admin.offices.edit', $office->id) }}" style="text-decoration:underline;color:var(--text-secondary)">Reactivate</a>
        </div>
      @endif
      <div class="dm-card-body">
        <div class="dm-card-top">
          <div class="dm-card-avatar">
            @if($office->logo)<img src="{{ asset($office->logo) }}" alt="">@else<i class="ti ti-building" style="font-size:14px;color:#233264;opacity:.5" aria-hidden="true"></i>@endif
          </div>
          <div class="dm-card-info">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
              <a href="{{ route('admin.offices.show', $office->id) }}" class="dm-card-name">{{ $office->company_name }}</a>
              @if($office->is_verified)
                <span class="dm-badge verified" style="font-size:10px;padding:2px 7px"><span class="dm-badge-dot"></span>Verified</span>
              @else
                <span class="dm-badge pending" style="font-size:10px;padding:2px 7px"><span class="dm-badge-dot"></span>Pending</span>
              @endif
            </div>
            <div class="dm-card-meta">{{ $office->city ?? 'N/A' }} · {{ $office->owned_properties_count ?? 0 }} listings</div>
            <div class="dm-card-meta" style="margin-top:1px">{{ $office->email_address }}</div>
          </div>
        </div>
        <div class="dm-card-sub-row">
          <div class="dm-ring-wrap" aria-hidden="true">
            <svg class="dm-ring-svg" viewBox="0 0 36 36">
              <circle class="dm-ring-track" cx="18" cy="18" r="15"/>
              @if($days !== null)
              <circle class="dm-ring-fill" cx="18" cy="18" r="15" stroke="{{ $ringColor }}" stroke-dasharray="{{ $expired ? 0 : $dash }} {{ $circumference }}"/>
              @endif
            </svg>
            <div class="dm-ring-label" style="color:{{ $labelColor ?? 'var(--text-muted)' }}">
              @if($expired)✕@elseif($days !== null){{ $days }}d@else—@endif
            </div>
          </div>
          <div>
            <div class="dm-sub-plan">{{ $office->current_plan ?? 'Free' }}</div>
            <div class="dm-sub-date {{ $dateClass ?? 'muted' }}" style="font-size:12px">
              @if($end){{ $expired ? 'Ended' : 'Ends' }} {{ $end->format('d M Y') }}@else No subscription@endif
            </div>
          </div>
        </div>
        <div class="dm-card-actions {{ !$office->is_verified ? 'with-approve' : '' }}">
          <a href="{{ route('admin.offices.show', $office->id) }}" class="dm-card-act primary"><i class="ti ti-eye" aria-hidden="true"></i><span>View</span></a>
          <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-card-act"><i class="ti ti-pencil" aria-hidden="true"></i><span>Edit</span></a>
          @if(!$office->is_verified)
            <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST">@csrf
              <button type="submit" class="dm-card-act success" style="width:100%"><i class="ti ti-check" aria-hidden="true"></i><span>Verify</span></button>
            </form>
          @endif
          <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST">@csrf @method('DELETE')
            <button type="submit" class="dm-card-act danger-soft" style="width:100%" onclick="return confirm('Delete this office?')">
              <i class="ti ti-trash" aria-hidden="true"></i><span>Delete</span>
            </button>
          </form>
        </div>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:48px 24px;background:var(--surface-2);border:.5px solid var(--border);border-radius:12px">
      <i class="ti ti-building-off" style="font-size:28px;color:var(--border-strong);display:block;margin-bottom:10px" aria-hidden="true"></i>
      <p style="font-size:14px;font-weight:500;color:var(--text-primary);margin-bottom:4px">No offices found</p>
      <a href="{{ route('admin.offices.index') }}" style="font-size:12px;font-weight:500;color:#233264">Clear filters</a>
    </div>
    @endforelse
    <div style="padding:12px 0">{{ $offices->withQueryString()->links() }}</div>
  </div>

</div>
@endsection

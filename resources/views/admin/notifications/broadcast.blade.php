{{-- resources/views/admin/notifications/broadcast.blade.php --}}
@extends('layouts.admin-layout')

@section('title', 'Broadcast')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Route as Rt;
    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    // Pre-targeted people, e.g. from the dashboard "Gone quiet" card:
    //   /admin/notifications/broadcast?users=id1,id2,id3
    $preIds = collect(explode(',', (string) request('users')))->filter()->unique()->values();

    $preUsers = collect();
    if ($preIds->isNotEmpty()) {
        try {
            $preUsers = \App\Models\User::whereIn('id', $preIds)
                ->select('id', 'username', 'email', 'language', 'photo_image')
                ->get();
        } catch (\Throwable $e) {
            $preUsers = collect();
        }
    }

    $preset = request('preset'); // e.g. "winback"
@endphp

<style>
    .bc-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
    @media (max-width:1023px) { .bc-grid { grid-template-columns:1fr; } }

    .bc-tabs { display:flex; gap:2px; background:#f1f2f7; padding:4px; border-radius:12px; margin-bottom:18px; }
    .bc-tab { flex:1; border:0; background:transparent; padding:9px 12px; border-radius:9px; font-size:12.5px; font-weight:800;
              color:#64748b; cursor:pointer; transition:.16s; font-family:inherit; }
    .bc-tab.on { background:#fff; color:var(--dm); box-shadow:0 1px 3px rgba(15,23,42,.12); }
    .bc-pane { display:none; }
    .bc-pane.on { display:block; }

    .bc-f { margin-bottom:16px; }
    .bc-f > label { display:block; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin-bottom:7px; }
    .bc-f .opt { font-weight:600; text-transform:none; letter-spacing:0; color:#cbd5e1; font-size:10.5px; }
    .bc-f input[type=text], .bc-f input[type=url], .bc-f input[type=datetime-local], .bc-f select, .bc-f textarea {
        width:100%; padding:11px 14px; border:1px solid #e6e8f2; border-radius:12px; font-size:13.5px; font-weight:600;
        color:#0f172a; background:#fff; outline:none; transition:.16s; font-family:inherit;
    }
    .bc-f input:focus, .bc-f select:focus, .bc-f textarea:focus { border-color:var(--dm); box-shadow:0 0 0 3px rgba(48,59,151,.12); }
    .bc-f textarea { resize:vertical; min-height:88px; line-height:1.55; }
    .bc-count { font-size:10.5px; font-weight:700; color:#a8b0c4; text-align:right; margin-top:5px; }
    .bc-count.warn { color:#d97706; } .bc-count.over { color:#e11d48; }
    .bc-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

    /* Recipients */
    .bc-rec { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
    @media (max-width:520px) { .bc-rec { grid-template-columns:1fr; } }
    .bc-rec input { position:absolute; opacity:0; width:0; height:0; }
    .bc-rec label { display:flex; align-items:center; gap:10px; padding:13px 14px; border:1px solid #e6e8f2; border-radius:13px;
                    cursor:pointer; font-size:13px; font-weight:700; color:#475569; transition:.16s; min-height:48px; }
    .bc-rec label i { width:18px; text-align:center; color:#94a3b8; }
    .bc-rec input:checked + label { border-color:var(--dm); background:var(--dm-soft); color:var(--dm); box-shadow:0 0 0 3px rgba(48,59,151,.08); }
    .bc-rec input:checked + label i { color:var(--dm); }

    .bc-chips { display:flex; flex-wrap:wrap; gap:7px; margin-top:12px; }
    .bc-chip { display:inline-flex; align-items:center; gap:7px; padding:6px 11px; border-radius:999px; background:var(--dm-soft);
               color:var(--dm); font-size:11.5px; font-weight:800; }
    .bc-chip span.lang { background:#fff; padding:1px 6px; border-radius:999px; font-size:9.5px; color:#64748b; }

    /* Drop zone */
    .bc-drop { border:2px dashed #d7dbe8; border-radius:14px; padding:26px 18px; text-align:center; cursor:pointer;
               position:relative; background:#fafbfd; transition:.18s; }
    .bc-drop:hover, .bc-drop.over { border-color:var(--dm); background:var(--dm-soft); }
    .bc-drop input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
    .bc-drop i { font-size:22px; color:#c3cadd; display:block; margin-bottom:9px; }
    .bc-drop p { margin:3px 0; font-size:12.5px; font-weight:600; color:#94a3b8; }
    .bc-drop b { color:var(--dm); }
    .bc-prev { margin-top:13px; position:relative; display:none; }
    .bc-prev.show { display:block; }
    .bc-prev img { width:100%; max-height:170px; object-fit:cover; border-radius:12px; border:1px solid #e6e8f2; display:block; }
    .bc-prev button { position:absolute; top:9px; right:9px; width:30px; height:30px; border-radius:999px; border:0;
                      background:rgba(15,19,40,.6); color:#fff; cursor:pointer; font-size:13px; }
    .bc-prev button:hover { background:#e11d48; }

    /* Phone preview */
    .bc-phone { width:216px; margin:20px auto 16px; background:#14172a; border-radius:32px; padding:13px 9px 17px;
                box-shadow:0 22px 50px -18px rgba(15,23,42,.6); }
    .bc-phone::before { content:''; display:block; width:54px; height:5px; background:#2a2f4a; border-radius:3px; margin:0 auto 11px; }
    .bc-scr { background:#eef0f6; border-radius:22px; overflow:hidden; min-height:300px; }
    .bc-bar { background:#14172a; color:rgba(255,255,255,.45); font-size:9px; font-weight:700; padding:6px 14px; display:flex; justify-content:space-between; }
    .bc-strip { background:#e2e5ee; padding:5px 11px; font-size:9.5px; font-weight:800; color:#7c869c; text-transform:uppercase; letter-spacing:.06em; }
    .bc-note { margin:10px 8px; background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(15,23,42,.13); overflow:hidden; }
    .bc-note-hd { display:flex; align-items:center; gap:6px; padding:9px 11px 5px; }
    .bc-note-ico { width:19px; height:19px; border-radius:6px; background:linear-gradient(135deg,var(--dm),var(--dm-light)); flex-shrink:0; }
    .bc-note-app { font-size:9px; font-weight:900; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; flex:1; }
    .bc-note-time { font-size:9px; color:#94a3b8; font-weight:700; }
    .bc-note-bd { padding:0 11px 10px; }
    .bc-note-img { width:100%; max-height:72px; object-fit:cover; border-radius:6px; margin-bottom:7px; display:none; }
    .bc-note-img.show { display:block; }
    .bc-note-t { font-size:11.5px; font-weight:900; color:#0f172a; margin-bottom:3px; line-height:1.3; }
    .bc-note-m { font-size:10.5px; color:#64748b; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .bc-note[dir=rtl] .bc-note-t, .bc-note[dir=rtl] .bc-note-m { text-align:right; }

    .bc-langbar { display:flex; gap:5px; justify-content:center; margin-bottom:4px; }
    .bc-langbtn { border:1px solid #e6e8f2; background:#fff; border-radius:999px; padding:4px 11px; font-size:10.5px;
                  font-weight:800; color:#94a3b8; cursor:pointer; font-family:inherit; transition:.16s; }
    .bc-langbtn.on { background:var(--dm); border-color:transparent; color:#fff; }

    .bc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:4px; padding:14px 16px; border-top:1px solid #f1f2f7; }
    .bc-stat { text-align:center; }
    .bc-stat b { display:block; font-size:20px; font-weight:900; color:var(--dm); line-height:1; }
    .bc-stat span { font-size:9.5px; font-weight:800; color:#a8b0c4; text-transform:uppercase; letter-spacing:.06em; }

    .bc-send { padding:15px 16px; border-top:1px solid #f1f2f7; }
    .bc-spin { width:16px; height:16px; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; border-radius:50%;
               animation:bcspin .7s linear infinite; display:none; }
    .bc-spin.on { display:block; }
    @keyframes bcspin { to { transform:rotate(360deg); } }

    .bc-result { display:none; padding:14px 16px; border-radius:14px; font-size:13px; font-weight:700; margin-bottom:18px; gap:11px; align-items:flex-start; }
    .bc-result.show { display:flex; }
    .bc-result.ok { background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
    .bc-result.err { background:#fef2f2; border:1px solid #fecdd3; color:#b91c1c; }
</style>

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Communication</p>
            <h1 class="page-ttl">Broadcast</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">
                Every person receives it in their own language — English, Arabic or Kurdish.
            </p>
        </div>
    </div>

    <div class="bc-result" id="bcRes">
        <i class="fas fa-circle-check mt-0.5" id="bcResIco"></i>
        <div class="flex-1"><div id="bcResMsg"></div><div id="bcResWarn" class="mt-2 text-[12px] font-semibold opacity-80"></div></div>
    </div>

    <form id="bcForm" enctype="multipart/form-data">
        @csrf
        @foreach($preUsers as $u)
            <input type="hidden" name="user_ids[]" value="{{ $u->id }}">
        @endforeach

        <div class="bc-grid">
            <div>
                {{-- ── MESSAGE ─────────────────────────────────────────── --}}
                <div class="card mb-5">
                    <div class="card-hd">
                        <div><p class="eyebrow mb-1">Content</p><h3 class="card-ttl">What are you sending?</h3></div>
                        <span class="badge b-plan">3 languages</span>
                    </div>
                    <div class="p-4 md:p-5">
                        <div class="bc-tabs">
                            <button type="button" class="bc-tab on" data-lang="en">English</button>
                            <button type="button" class="bc-tab" data-lang="ar">العربية</button>
                            <button type="button" class="bc-tab" data-lang="ku">کوردی</button>
                        </div>

                        {{-- English --}}
                        <div class="bc-pane on" id="pane-en">
                            <div class="bc-f">
                                <label>Title <span style="color:#e11d48">*</span></label>
                                <input type="text" name="title_en" id="t_en" maxlength="100"
                                       placeholder="New properties in your area" oninput="bcPreview();bcCount(this,100,'c_t_en')">
                                <div class="bc-count" id="c_t_en">0 / 100</div>
                            </div>
                            <div class="bc-f">
                                <label>Message <span style="color:#e11d48">*</span></label>
                                <textarea name="message_en" id="m_en" maxlength="500" rows="3"
                                          placeholder="Tell them what's new…" oninput="bcPreview();bcCount(this,500,'c_m_en')"></textarea>
                                <div class="bc-count" id="c_m_en">0 / 500</div>
                            </div>
                        </div>

                        {{-- Arabic --}}
                        <div class="bc-pane" id="pane-ar">
                            <div class="bc-f">
                                <label>Title <span class="opt">— falls back to English if empty</span></label>
                                <input type="text" name="title_ar" id="t_ar" maxlength="100" dir="rtl"
                                       placeholder="عقارات جديدة في منطقتك" oninput="bcPreview()">
                            </div>
                            <div class="bc-f">
                                <label>Message <span class="opt">— falls back to English</span></label>
                                <textarea name="message_ar" id="m_ar" maxlength="500" rows="3" dir="rtl"
                                          placeholder="وصف الإشعار…" oninput="bcPreview()"></textarea>
                            </div>
                        </div>

                        {{-- Kurdish --}}
                        <div class="bc-pane" id="pane-ku">
                            <div class="bc-f">
                                <label>Title <span class="opt">— falls back to English if empty</span></label>
                                <input type="text" name="title_ku" id="t_ku" maxlength="100" dir="rtl"
                                       placeholder="خانووی نوێ لە ناوچەکەتدا" oninput="bcPreview()">
                            </div>
                            <div class="bc-f">
                                <label>Message <span class="opt">— falls back to English</span></label>
                                <textarea name="message_ku" id="m_ku" maxlength="500" rows="3" dir="rtl"
                                          placeholder="ڕوونکردنەوەی ئاگادارکردنەوەکە…" oninput="bcPreview()"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── RECIPIENTS ──────────────────────────────────────── --}}
                <div class="card mb-5">
                    <div class="card-hd">
                        <div><p class="eyebrow mb-1">Audience</p><h3 class="card-ttl">Who receives it?</h3></div>
                        @if($preUsers->isNotEmpty())
                            <span class="badge b-warn">{{ $preUsers->count() }} pre-selected</span>
                        @endif
                    </div>
                    <div class="p-4 md:p-5">
                        <div class="bc-rec">
                            @php
                                $recipients = [
                                    ['all', 'Everyone', 'fa-earth-americas'],
                                    ['users', 'Users only', 'fa-user'],
                                    ['agents', 'Agents only', 'fa-user-tie'],
                                    ['offices', 'Offices only', 'fa-building'],
                                ];
                                $defaultPick = $preUsers->isNotEmpty() ? 'selected' : 'all';
                            @endphp
                            @foreach($recipients as [$val, $label, $icon])
                                <div style="position:relative">
                                    <input type="radio" name="recipient_type" id="r-{{ $val }}" value="{{ $val }}"
                                           {{ $defaultPick === $val ? 'checked' : '' }} onchange="bcCounts()">
                                    <label for="r-{{ $val }}"><i class="fas {{ $icon }}"></i> {{ $label }}</label>
                                </div>
                            @endforeach

                            @if($preUsers->isNotEmpty())
                                <div style="position:relative;grid-column:1/-1">
                                    <input type="radio" name="recipient_type" id="r-selected" value="selected" checked onchange="bcCounts()">
                                    <label for="r-selected">
                                        <i class="fas fa-user-check"></i>
                                        {{ $preset === 'winback' ? 'Win back these people' : 'Selected people' }}
                                        <span class="badge b-plan ml-auto">{{ $preUsers->count() }}</span>
                                    </label>
                                </div>
                            @endif
                        </div>

                        @if($preUsers->isNotEmpty())
                            <div class="bc-chips">
                                @foreach($preUsers as $u)
                                    <span class="bc-chip">
                                        {{ $u->username }}
                                        <span class="lang">{{ strtoupper($u->language ?: 'en') }}</span>
                                    </span>
                                @endforeach
                            </div>
                            @php $langMix = $preUsers->groupBy(fn($u) => $u->language ?: 'en')->map->count(); @endphp
                            <p class="text-[11.5px] font-bold text-slate-500 mt-3">
                                <i class="fas fa-language mr-1" style="color:var(--dm)"></i>
                                Language mix:
                                @foreach($langMix as $lang => $n)
                                    {{ strtoupper($lang) }} {{ $n }}@if(!$loop->last) · @endif
                                @endforeach
                                — each person gets their own version.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- ── IMAGE ───────────────────────────────────────────── --}}
                <div class="card mb-5">
                    <div class="card-hd">
                        <div><p class="eyebrow mb-1">Media</p><h3 class="card-ttl">Image</h3></div>
                        <span class="badge b-mute">Optional</span>
                    </div>
                    <div class="p-4 md:p-5">
                        <p class="text-[12.5px] text-slate-500 font-semibold mb-4">
                            Shows in the notification tray and lifts open rates.
                            Best at <b class="text-slate-700">1200 × 628</b>, under 2 MB.
                        </p>

                        <div class="bc-drop" id="bcDrop">
                            <input type="file" name="image" id="bcFile" accept="image/jpeg,image/png,image/webp" onchange="bcFile(this)">
                            <i class="fas fa-image"></i>
                            <p><b>Click to upload</b> or drag and drop</p>
                            <p style="font-size:11.5px">JPG · PNG · WEBP · max 2 MB</p>
                        </div>

                        <div class="bc-prev" id="bcPrevWrap">
                            <img id="bcPrevImg" src="" alt="">
                            <button type="button" onclick="bcClearImg()"><i class="fas fa-xmark"></i></button>
                        </div>

                        <div class="bc-f" style="margin-top:14px;margin-bottom:0">
                            <label>Or paste an image URL</label>
                            <div style="display:flex;gap:8px">
                                <input type="url" name="image_url" id="bcUrl" placeholder="https://dreammulk.com/storage/…" oninput="document.getElementById('bcFile').value=''">
                                <button type="button" class="btn-ghost" onclick="bcShowImg(document.getElementById('bcUrl').value.trim())">Preview</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SETTINGS ────────────────────────────────────────── --}}
                <div class="card">
                    <div class="card-hd"><div><p class="eyebrow mb-1">Delivery</p><h3 class="card-ttl">Settings</h3></div></div>
                    <div class="p-4 md:p-5">
                        <div class="bc-f bc-2">
                            <div>
                                <label>Type</label>
                                <select name="type">
                                    <option value="system">System</option>
                                    <option value="property">Property</option>
                                    <option value="promotion">Promotion</option>
                                    <option value="alert">Alert</option>
                                </select>
                            </div>
                            <div>
                                <label>Priority</label>
                                <select name="priority" id="bcPriority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="bc-f">
                            <label>Action URL <span class="opt">— where it opens on tap</span></label>
                            <input type="text" name="action_url" placeholder="/properties  or  https://dreammulk.com/…"
                                   value="{{ $preset === 'winback' ? '/properties' : '' }}">
                        </div>
                        <div class="bc-f bc-2" style="margin-bottom:0">
                            <div>
                                <label>Button text <span class="opt">optional</span></label>
                                <input type="text" name="action_text" maxlength="60" placeholder="View properties">
                            </div>
                            <div>
                                <label>Expires <span class="opt">blank = never</span></label>
                                <input type="datetime-local" name="expires_at">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── PREVIEW PANEL ───────────────────────────────────────── --}}
            <div>
                <div class="card" style="position:sticky;top:20px">
                    <div class="card-hd"><div><p class="eyebrow mb-1">Preview</p><h3 class="card-ttl">On the phone</h3></div></div>

                    <div class="px-4 pt-3">
                        <div class="bc-langbar">
                            <button type="button" class="bc-langbtn on" data-plang="en" onclick="bcSetPreviewLang('en')">EN</button>
                            <button type="button" class="bc-langbtn" data-plang="ar" onclick="bcSetPreviewLang('ar')">AR</button>
                            <button type="button" class="bc-langbtn" data-plang="ku" onclick="bcSetPreviewLang('ku')">KU</button>
                        </div>

                        <div class="bc-phone">
                            <div class="bc-scr">
                                <div class="bc-bar"><span>9:41</span><span>▪▪▪ ⌁ ▮</span></div>
                                <div class="bc-strip">Notifications</div>
                                <div class="bc-note" id="bcNote">
                                    <div class="bc-note-hd">
                                        <div class="bc-note-ico"></div>
                                        <span class="bc-note-app">Dream Mulk</span>
                                        <span class="bc-note-time">now</span>
                                    </div>
                                    <div class="bc-note-bd">
                                        <img id="bcNoteImg" class="bc-note-img" src="" alt="">
                                        <div class="bc-note-t" id="bcNoteT">Your title appears here</div>
                                        <div class="bc-note-m" id="bcNoteM">And your message shows underneath…</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bc-stats">
                        <div class="bc-stat"><b id="sU">—</b><span>Users</span></div>
                        <div class="bc-stat"><b id="sA">—</b><span>Agents</span></div>
                        <div class="bc-stat"><b id="sO">—</b><span>Offices</span></div>
                    </div>

                    <div class="bc-send">
                        <button type="submit" form="bcForm" class="btn-solid" style="width:100%" id="bcBtn">
                            <div class="bc-spin" id="bcSpin"></div>
                            <i class="fas fa-paper-plane" id="bcBtnIco"></i>
                            <span id="bcBtnTxt">Send broadcast</span>
                        </button>
                        <p class="text-[11px] font-semibold text-slate-400 text-center mt-2.5">
                            Sends immediately and can't be undone.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/* ── Language tabs ─────────────────────────────────────────────── */
document.querySelectorAll('.bc-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.bc-tab').forEach(t => t.classList.remove('on'));
        document.querySelectorAll('.bc-pane').forEach(p => p.classList.remove('on'));
        tab.classList.add('on');
        document.getElementById('pane-' + tab.dataset.lang).classList.add('on');
        bcSetPreviewLang(tab.dataset.lang);
    });
});

/* ── Preview ───────────────────────────────────────────────────── */
let bcPreviewLang = 'en';

function bcSetPreviewLang(lang) {
    bcPreviewLang = lang;
    document.querySelectorAll('.bc-langbtn').forEach(b => b.classList.toggle('on', b.dataset.plang === lang));
    bcPreview();
}

function bcPreview() {
    const val = id => (document.getElementById(id)?.value || '').trim();

    const title = val('t_' + bcPreviewLang) || val('t_en') || 'Your title appears here';
    const msg   = val('m_' + bcPreviewLang) || val('m_en') || 'And your message shows underneath…';

    document.getElementById('bcNoteT').textContent = title;
    document.getElementById('bcNoteM').textContent = msg;
    document.getElementById('bcNote').setAttribute('dir', bcPreviewLang === 'en' ? 'ltr' : 'rtl');
}

function bcCount(el, max, id) {
    const n = el.value.length;
    const d = document.getElementById(id);
    d.textContent = n + ' / ' + max;
    d.className = 'bc-count' + (n >= max ? ' over' : n > max * 0.88 ? ' warn' : '');
}

/* ── Image ─────────────────────────────────────────────────────── */
function bcFile(input) {
    const f = input.files[0];
    if (!f) return;
    if (f.size > 2097152) { alert('Image must be under 2 MB.'); input.value = ''; return; }
    const r = new FileReader();
    r.onload = e => { bcShowImg(e.target.result); document.getElementById('bcUrl').value = ''; };
    r.readAsDataURL(f);
}

function bcShowImg(src) {
    if (!src) return;
    document.getElementById('bcPrevImg').src = src;
    document.getElementById('bcPrevWrap').classList.add('show');
    const n = document.getElementById('bcNoteImg');
    n.src = src; n.classList.add('show');
}

function bcClearImg() {
    document.getElementById('bcFile').value = '';
    document.getElementById('bcUrl').value = '';
    document.getElementById('bcPrevWrap').classList.remove('show');
    const n = document.getElementById('bcNoteImg');
    n.src = ''; n.classList.remove('show');
}

const bcDrop = document.getElementById('bcDrop');
bcDrop.addEventListener('dragover', e => { e.preventDefault(); bcDrop.classList.add('over'); });
bcDrop.addEventListener('dragleave', () => bcDrop.classList.remove('over'));
bcDrop.addEventListener('drop', e => {
    e.preventDefault(); bcDrop.classList.remove('over');
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) {
        const dt = new DataTransfer(); dt.items.add(f);
        document.getElementById('bcFile').files = dt.files;
        bcFile(document.getElementById('bcFile'));
    }
});

/* ── Recipient counts ──────────────────────────────────────────── */
let bcCountTimer;
function bcCounts() {
    clearTimeout(bcCountTimer);
    bcCountTimer = setTimeout(async function () {
        const rt = document.querySelector('input[name=recipient_type]:checked')?.value || 'all';
        try {
            const r = await fetch('{{ route("admin.notifications.broadcast") }}?_counts=1&recipient_type=' + rt, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!r.ok) return;
            const j = await r.json();
            if (j.counts) {
                document.getElementById('sU').textContent = j.counts.users ?? '—';
                document.getElementById('sA').textContent = j.counts.agents ?? '—';
                document.getElementById('sO').textContent = j.counts.offices ?? '—';
            }
        } catch (e) {}
    }, 350);
}

@if($preUsers->isNotEmpty())
    document.getElementById('sU').textContent = {{ $preUsers->count() }};
    document.getElementById('sA').textContent = 0;
    document.getElementById('sO').textContent = 0;
@else
    bcCounts();
@endif

/* ── Submit ────────────────────────────────────────────────────── */
document.getElementById('bcForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!document.getElementById('t_en').value.trim() || !document.getElementById('m_en').value.trim()) {
        bcResult('err', 'English title and message are required — they are the fallback for everyone.');
        return;
    }

    bcLoading(true);

    const fd = new FormData(this);
    const url = document.getElementById('bcUrl').value.trim();
    if (!document.getElementById('bcFile').files.length && url) fd.set('image_url', url);

    try {
        const res = await fetch('{{ route("admin.notifications.broadcast") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: fd
        });
        const j = await res.json();

        if (res.ok && (j.status || j.success)) {
            const d = j.data ?? {};
            const byLang = d.by_language
                ? Object.entries(d.by_language).map(([l, n]) => n + ' in ' + l.toUpperCase()).join(' · ')
                : '';
            bcResult('ok', 'Sent to <b>' + (d.sent_to ?? '?') + '</b> people.' + (byLang ? '<br>' + byLang : ''));
            this.reset(); bcClearImg(); bcPreview();
        } else {
            const errs = j.errors ? '<br>' + Object.values(j.errors).flat().join('<br>') : '';
            bcResult('err', (j.message ?? 'Failed to send.') + errs);
        }
    } catch (err) {
        bcResult('err', 'Network error: ' + err.message);
    } finally {
        bcLoading(false);
    }
});

function bcLoading(on) {
    document.getElementById('bcBtn').disabled = on;
    document.getElementById('bcBtnIco').style.display = on ? 'none' : '';
    document.getElementById('bcSpin').classList.toggle('on', on);
    document.getElementById('bcBtnTxt').textContent = on ? 'Sending…' : 'Send broadcast';
}

function bcResult(type, html) {
    const el = document.getElementById('bcRes');
    el.className = 'bc-result show ' + type;
    document.getElementById('bcResIco').className = 'fas mt-0.5 ' + (type === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation');
    document.getElementById('bcResMsg').innerHTML = html;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

bcPreview();
</script>
@endsection

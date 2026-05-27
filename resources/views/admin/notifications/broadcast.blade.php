{{-- resources/views/admin/notifications/broadcast.blade.php --}}
@extends('layouts.admin-layout')

@section('title', 'Broadcast Notification')

@section('content')

{{-- ── ALL STYLES INLINED — works regardless of layout yield support ── --}}
<style>
:root {
    --p:   #6C3FC5;
    --pd:  #5A2FB0;
    --pl:  #F0EBFF;
    --pm:  #7C4FD4;
    --gold:#F5A623;
    --red: #E53E3E;
    --g50: #F9FAFB;
    --g100:#F3F4F6;
    --g200:#E5E7EB;
    --g300:#D1D5DB;
    --g400:#9CA3AF;
    --g500:#6B7280;
    --g600:#4B5563;
    --g700:#374151;
    --g900:#111827;
    --r:   12px;
    --sh:  0 4px 24px rgba(108,63,197,.10);
    --shl: 0 8px 40px rgba(108,63,197,.18);
}

.bc-page {
    background: var(--g50);
    min-height: 100vh;
    padding: 32px 24px 80px;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: var(--g700);
}

/* ── Header ──────────────────────────────────────────── */
.bc-hdr {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
}
.bc-hdr-icon {
    flex-shrink: 0;
    width: 54px; height: 54px;
    background: linear-gradient(135deg, var(--p), var(--pm));
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(108,63,197,.35);
}
.bc-hdr-icon svg { color: #fff; width: 26px; height: 26px; }
.bc-hdr-text h1 {
    font-size: 1.5rem; font-weight: 800;
    color: var(--g900); margin: 0 0 3px;
    letter-spacing: -.02em;
}
.bc-hdr-text p {
    font-size: .85rem; color: var(--g400); margin: 0;
}

/* ── Result banner ───────────────────────────────────── */
.bc-result {
    display: none;
    margin-bottom: 24px;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: .875rem; font-weight: 600;
    gap: 10px; align-items: flex-start;
}
.bc-result.show  { display: flex; }
.bc-result.ok  { background:#F0FFF4; border:1.5px solid #9AE6B4; color:#276749; }
.bc-result.err { background:#FFF5F5; border:1.5px solid #FEB2B2; color:#9B2C2C; }
.bc-warn-box {
    display: none;
    margin-top: 12px;
    padding: 10px 14px;
    background: #FFFBEB;
    border: 1.5px solid #F6E05E;
    border-radius: 8px;
    font-size: .8rem; color: #744210;
}
.bc-warn-box.show { display: block; }
.bc-warn-box ul { margin: 6px 0 0; padding-left: 18px; }

/* ── Two-column grid ─────────────────────────────────── */
.bc-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 22px;
    align-items: start;
    max-width: 1080px;
}
@media (max-width: 860px) { .bc-grid { grid-template-columns: 1fr; } }

/* ── Card ────────────────────────────────────────────── */
.bc-card {
    background: #fff;
    border-radius: var(--r);
    box-shadow: var(--sh);
    overflow: hidden;
    margin-bottom: 20px;
}
.bc-card:last-child { margin-bottom: 0; }
.bc-card-hd {
    padding: 16px 20px 13px;
    border-bottom: 1px solid var(--g100);
    display: flex; align-items: center; gap: 10px;
}
.bc-card-hd h2 {
    font-size: .8rem; font-weight: 800;
    color: var(--g600); margin: 0;
    text-transform: uppercase; letter-spacing: .07em;
    flex: 1;
}
.bc-badge {
    font-size: .65rem; font-weight: 700;
    padding: 2px 9px; border-radius: 20px;
    background: var(--pl); color: var(--p);
    text-transform: uppercase; letter-spacing: .05em;
}
.bc-card-bd { padding: 20px; }

/* ── Field ───────────────────────────────────────────── */
.bc-f { margin-bottom: 18px; }
.bc-f:last-child { margin-bottom: 0; }
.bc-f > label {
    display: block;
    font-size: .72rem; font-weight: 700;
    color: var(--g500);
    text-transform: uppercase; letter-spacing: .06em;
    margin-bottom: 6px;
}
.bc-f label .req  { color: var(--red); margin-left: 2px; }
.bc-f label .hint { font-weight: 400; text-transform: none; color: var(--g400); margin-left: 5px; letter-spacing: 0; font-size: .72rem; }

.bc-f input[type=text],
.bc-f input[type=url],
.bc-f input[type=datetime-local],
.bc-f select,
.bc-f textarea {
    width: 100%;
    padding: 10px 13px;
    border: 1.5px solid var(--g200);
    border-radius: 8px;
    font-size: .9rem;
    color: var(--g700);
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    font-family: inherit;
}
.bc-f input:focus,
.bc-f select:focus,
.bc-f textarea:focus {
    border-color: var(--p);
    box-shadow: 0 0 0 3px rgba(108,63,197,.12);
}
.bc-f textarea { resize: vertical; min-height: 78px; }

/* char counter */
.bc-cc {
    font-size: .7rem; color: var(--g400);
    text-align: right; margin-top: 4px;
}
.bc-cc.warn { color: var(--gold); }
.bc-cc.over { color: var(--red); font-weight: 700; }

/* ── Lang tabs ───────────────────────────────────────── */
.lang-tabs {
    display: flex; gap: 6px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.lang-tab {
    padding: 6px 15px;
    border-radius: 20px;
    border: 1.5px solid var(--g200);
    background: #fff;
    font-size: .78rem; font-weight: 700;
    color: var(--g500);
    cursor: pointer;
    transition: all .15s;
    font-family: inherit;
}
.lang-tab.active {
    background: var(--p);
    border-color: var(--p);
    color: #fff;
    box-shadow: 0 3px 10px rgba(108,63,197,.3);
}
.lang-tab:hover:not(.active) { border-color: var(--p); color: var(--p); }

.lang-pane { display: none; }
.lang-pane.on { display: block; }

/* ── Image zone ──────────────────────────────────────── */
.img-zone {
    border: 2px dashed var(--g300);
    border-radius: 10px;
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
    background: var(--g50);
}
.img-zone:hover, .img-zone.over {
    border-color: var(--p);
    background: var(--pl);
}
.img-zone input[type=file] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer;
    width: 100%; height: 100%;
}
.img-zone-ico { font-size: 2.2rem; margin-bottom: 8px; display: block; }
.img-zone p   { margin: 4px 0; font-size: .85rem; color: var(--g400); }
.img-zone strong { color: var(--p); }

.img-prev-wrap {
    margin-top: 12px;
    position: relative;
    display: none;
}
.img-prev-wrap.show { display: block; }
.img-prev-wrap img {
    width: 100%; max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--g200);
    display: block;
}
.img-rm-btn {
    position: absolute; top: 8px; right: 8px;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(0,0,0,.6);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .85rem;
    transition: background .15s;
    line-height: 1;
}
.img-rm-btn:hover { background: var(--red); }

.img-url-row {
    display: flex; gap: 8px; align-items: stretch;
    margin-top: 12px;
}
.img-url-row input {
    flex: 1;
    padding: 9px 13px;
    border: 1.5px solid var(--g200);
    border-radius: 8px;
    font-size: .875rem; color: var(--g700);
    outline: none;
    font-family: inherit;
    transition: border-color .15s;
}
.img-url-row input:focus { border-color: var(--p); }
.img-url-btn {
    padding: 9px 14px;
    border-radius: 8px;
    border: 1.5px solid var(--g200);
    background: #fff;
    font-size: .78rem; font-weight: 700;
    color: var(--g600); cursor: pointer;
    white-space: nowrap;
    transition: all .15s;
    font-family: inherit;
}
.img-url-btn:hover { border-color: var(--p); color: var(--p); background: var(--pl); }

/* ── Recipient chips ─────────────────────────────────── */
.rc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.rc-opt { position: relative; }
.rc-opt input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }
.rc-opt label {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px;
    border: 1.5px solid var(--g200);
    border-radius: 10px;
    cursor: pointer;
    transition: all .15s;
    font-size: .855rem; font-weight: 600;
    color: var(--g600);
    text-transform: none; letter-spacing: 0;
    font-family: inherit;
}
.rc-opt label .rci { font-size: 1.15rem; }
.rc-opt input:checked + label {
    border-color: var(--p);
    background: var(--pl);
    color: var(--p);
    box-shadow: 0 0 0 3px rgba(108,63,197,.08);
}
.rc-opt label:hover { border-color: var(--p); }

/* ── Select row ──────────────────────────────────────── */
.sel-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

/* ── Preview panel ───────────────────────────────────── */
.prev-panel {
    background: #fff;
    border-radius: var(--r);
    box-shadow: var(--sh);
    overflow: hidden;
    position: sticky;
    top: 24px;
}
.prev-panel-hd {
    padding: 15px 18px 12px;
    border-bottom: 1px solid var(--g100);
    display: flex; align-items: center; gap: 8px;
}
.prev-panel-hd svg { color: var(--g400); flex-shrink: 0; }
.prev-panel-hd h2 {
    font-size: .75rem; font-weight: 800;
    color: var(--g500); margin: 0;
    text-transform: uppercase; letter-spacing: .07em;
}

/* Phone */
.phone {
    width: 230px;
    margin: 18px auto 0;
    background: #1a1a2e;
    border-radius: 30px;
    padding: 12px 9px 16px;
    box-shadow: 0 20px 50px rgba(0,0,0,.30);
}
.phone::before {
    content: '';
    display: block;
    width: 55px; height: 5px;
    background: #2d2d4e;
    border-radius: 3px;
    margin: 0 auto 10px;
}
.phone-scr {
    background: #f8f8f8;
    border-radius: 20px;
    overflow: hidden;
    min-height: 380px;
}
.phone-bar {
    background: #1a1a2e;
    color: rgba(255,255,255,.7);
    font-size: .5rem;
    padding: 5px 14px;
    display: flex; justify-content: space-between;
}
.phone-ntf-bar {
    background: #ececec;
    padding: 5px 10px;
    font-size: .58rem;
    color: var(--g500);
    border-bottom: 1px solid #ddd;
    font-weight: 600;
}

/* Notif bubble */
.nb {
    margin: 10px 8px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 14px rgba(0,0,0,.13);
    overflow: hidden;
    border: 1px solid var(--g100);
}
.nb-hd {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 10px 4px;
}
.nb-app-ico {
    width: 18px; height: 18px;
    background: linear-gradient(135deg, var(--p), var(--pm));
    border-radius: 5px; flex-shrink: 0;
}
.nb-app-name {
    font-size: .52rem; font-weight: 800;
    color: var(--g400); text-transform: uppercase;
    letter-spacing: .06em; flex: 1;
}
.nb-time { font-size: .52rem; color: var(--g400); }
.nb-body { padding: 0 10px 9px; }
.nb-img {
    width: 100%; max-height: 72px;
    object-fit: cover;
    display: none;
    margin-bottom: 5px;
}
.nb-img.show { display: block; }
.nb-title {
    font-size: .68rem; font-weight: 800;
    color: var(--g900); margin-bottom: 3px;
    line-height: 1.3;
}
.nb-msg {
    font-size: .63rem;
    color: var(--g500);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.nb-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: .58rem; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
    margin-top: 6px;
}
.nb-badge.low     { background: #EBF8FF; color: #2B6CB0; }
.nb-badge.medium  { background: #FFFBEB; color: #B45309; }
.nb-badge.high    { background: #FFF5F5; color: var(--red); }
.nb-badge.urgent  { background: #FFF5F5; color: var(--red); animation: nb-pulse 1.2s infinite; }
@keyframes nb-pulse { 0%,100%{opacity:1} 50%{opacity:.55} }

/* Stats row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 6px;
    padding: 14px 16px;
    border-top: 1px solid var(--g100);
}
.stat-item { text-align: center; }
.stat-num { font-size: 1.25rem; font-weight: 900; color: var(--p); line-height: 1; }
.stat-lbl { font-size: .6rem; color: var(--g400); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-top: 3px; }

/* Submit */
.bc-sub-bar {
    padding: 16px 18px;
    border-top: 1px solid var(--g100);
}
.bc-btn-send {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--p) 0%, var(--pm) 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: .95rem; font-weight: 800;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 9px;
    transition: all .2s;
    box-shadow: 0 4px 16px rgba(108,63,197,.35);
    font-family: inherit;
    letter-spacing: -.01em;
}
.bc-btn-send:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(108,63,197,.45);
}
.bc-btn-send:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.bc-btn-send svg { flex-shrink: 0; }

.bc-spin {
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    display: none; flex-shrink: 0;
}
.bc-spin.show { display: block; }
@keyframes spin { to { transform: rotate(360deg); } }

.bc-sub-note {
    text-align: center;
    font-size: .72rem;
    color: var(--g400);
    margin: 9px 0 0;
}
</style>

<div class="bc-page">

    {{-- Header --}}
    <div class="bc-hdr">
        <div class="bc-hdr-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="bc-hdr-text">
            <h1>Broadcast Notification</h1>
            <p>Send push notifications to users, agents &amp; offices — full multilingual support</p>
        </div>
    </div>

    {{-- Result banner --}}
    <div class="bc-result" id="bcResult">
        <div>
            <div id="bcResultMsg"></div>
            <div class="bc-warn-box" id="bcWarnBox">
                <strong>⚠ Translation warnings:</strong>
                <ul id="bcWarnList"></ul>
            </div>
        </div>
    </div>

    <form id="bcForm" enctype="multipart/form-data">
        @csrf

        <div class="bc-grid">

            {{-- ── LEFT ── --}}
            <div>

                {{-- Content --}}
                <div class="bc-card">
                    <div class="bc-card-hd">
                        <h2>Notification Content</h2>
                        <span class="bc-badge">Multilingual</span>
                    </div>
                    <div class="bc-card-bd">

                        <div class="lang-tabs">
                            <button type="button" class="lang-tab on" data-lang="en">🇬🇧 English</button>
                            <button type="button" class="lang-tab" data-lang="ar">🇸🇦 Arabic</button>
                            <button type="button" class="lang-tab" data-lang="ku">🟢 Kurdish</button>
                        </div>

                        {{-- EN --}}
                        <div class="lang-pane on" id="lp-en">
                            <div class="bc-f">
                                <label>Title <span class="req">*</span></label>
                                <input type="text" name="title_en" id="title_en" maxlength="100"
                                    placeholder="e.g. New properties in your area!"
                                    oninput="livePreview();cc(this,100,'cc-ten')">
                                <div class="bc-cc" id="cc-ten">0 / 100</div>
                            </div>
                            <div class="bc-f">
                                <label>Message <span class="req">*</span></label>
                                <textarea name="message_en" id="message_en" maxlength="500" rows="3"
                                    placeholder="Describe what this notification is about…"
                                    oninput="livePreview();cc(this,500,'cc-men')"></textarea>
                                <div class="bc-cc" id="cc-men">0 / 500</div>
                            </div>
                        </div>

                        {{-- AR --}}
                        <div class="lang-pane" id="lp-ar">
                            <div class="bc-f">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ar" maxlength="100"
                                    placeholder="مثال: عقارات جديدة في منطقتك!" dir="rtl">
                            </div>
                            <div class="bc-f">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ar" maxlength="500" rows="3"
                                    placeholder="وصف الإشعار…" dir="rtl"></textarea>
                            </div>
                        </div>

                        {{-- KU --}}
                        <div class="lang-pane" id="lp-ku">
                            <div class="bc-f">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ku" maxlength="100"
                                    placeholder="نموونە: خانووی نوێ لە ناوچەکەتدا!" dir="rtl">
                            </div>
                            <div class="bc-f">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ku" maxlength="500" rows="3"
                                    placeholder="ڕوونکردنەوەی ئاگادارکردنەوەکە…" dir="rtl"></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Image --}}
                <div class="bc-card">
                    <div class="bc-card-hd">
                        <h2>Notification Image</h2>
                        <span class="bc-badge">Optional</span>
                    </div>
                    <div class="bc-card-bd">
                        <p style="font-size:.83rem;color:var(--g400);margin:0 0 14px;line-height:1.5">
                            Images appear in the notification tray and boost open rates.<br>
                            Recommended: <strong>1200 × 628 px</strong> · max 2 MB · JPG/PNG/WEBP
                        </p>

                        <div class="img-zone" id="imgZone">
                            <input type="file" name="image" id="imgFile"
                                accept="image/jpeg,image/png,image/webp"
                                onchange="onFileChange(this)">
                            <span class="img-zone-ico">🖼️</span>
                            <p><strong>Click to upload</strong> or drag &amp; drop</p>
                            <p style="font-size:.78rem">JPG · PNG · WEBP &nbsp;·&nbsp; max 2 MB</p>
                        </div>

                        <div class="img-prev-wrap" id="imgPrevWrap">
                            <img id="imgPrev" src="" alt="Preview">
                            <button type="button" class="img-rm-btn" onclick="rmImg()" title="Remove">✕</button>
                        </div>

                        <div style="margin-top:14px">
                            <label style="display:block;font-size:.72rem;font-weight:700;color:var(--g500);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">
                                Or paste an image URL
                            </label>
                            <div class="img-url-row">
                                <input type="url" name="image_url" id="imgUrl"
                                    placeholder="https://dreammulk.com/storage/notifications/…"
                                    oninput="onUrlInput()">
                                <button type="button" class="img-url-btn" onclick="prevFromUrl()">Preview</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Settings --}}
                <div class="bc-card">
                    <div class="bc-card-hd">
                        <h2>Settings</h2>
                    </div>
                    <div class="bc-card-bd">

                        <div class="bc-f">
                            <label>Recipients <span class="req">*</span></label>
                            <div class="rc-grid">
                                <div class="rc-opt">
                                    <input type="radio" name="recipient_type" id="r-all" value="all" checked onchange="fetchCounts()">
                                    <label for="r-all"><span class="rci">🌍</span> Everyone</label>
                                </div>
                                <div class="rc-opt">
                                    <input type="radio" name="recipient_type" id="r-users" value="users" onchange="fetchCounts()">
                                    <label for="r-users"><span class="rci">👤</span> Users only</label>
                                </div>
                                <div class="rc-opt">
                                    <input type="radio" name="recipient_type" id="r-agents" value="agents" onchange="fetchCounts()">
                                    <label for="r-agents"><span class="rci">🏷️</span> Agents only</label>
                                </div>
                                <div class="rc-opt">
                                    <input type="radio" name="recipient_type" id="r-offices" value="offices" onchange="fetchCounts()">
                                    <label for="r-offices"><span class="rci">🏢</span> Offices only</label>
                                </div>
                            </div>
                        </div>

                        <div class="bc-f sel-row">
                            <div>
                                <label>Type <span class="req">*</span></label>
                                <select name="type">
                                    <option value="system">🔧 System</option>
                                    <option value="property">🏠 Property</option>
                                    <option value="promotion">🎉 Promotion</option>
                                    <option value="alert">⚠️ Alert</option>
                                </select>
                            </div>
                            <div>
                                <label>Priority <span class="req">*</span></label>
                                <select name="priority" id="bcPriority" onchange="livePreview()">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">🚨 Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="bc-f">
                            <label>Action URL <span class="hint">(where to open on tap)</span></label>
                            <input type="text" name="action_url"
                                placeholder="/properties  or  https://dreammulk.com/…">
                        </div>

                        <div class="bc-f">
                            <label>Action Button Text <span class="hint">(optional)</span></label>
                            <input type="text" name="action_text" maxlength="60"
                                placeholder="e.g. View Properties">
                        </div>

                        <div class="bc-f">
                            <label>Expires At <span class="hint">(blank = never)</span></label>
                            <input type="datetime-local" name="expires_at">
                        </div>

                    </div>
                </div>

            </div>{{-- /left --}}

            {{-- ── RIGHT — preview + send ── --}}
            <div>
                <div class="prev-panel">
                    <div class="prev-panel-hd">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <h2>Live Preview</h2>
                    </div>

                    <div style="padding:0 16px">
                        <div class="phone">
                            <div class="phone-scr">
                                <div class="phone-bar">
                                    <span>9:41</span><span>●●● ▶ ☰</span>
                                </div>
                                <div class="phone-ntf-bar">Notifications · 3 new</div>
                                <div class="nb">
                                    <div class="nb-hd">
                                        <div class="nb-app-ico"></div>
                                        <span class="nb-app-name">Dream Mulk</span>
                                        <span class="nb-time">now</span>
                                    </div>
                                    <div class="nb-body">
                                        <img id="prevImg" class="nb-img" src="" alt="">
                                        <div class="nb-title" id="prevTitle">Your notification title</div>
                                        <div class="nb-msg" id="prevMsg">Your notification message will appear here…</div>
                                        <span class="nb-badge medium" id="prevBadge">● Medium</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-num" id="sUsers">—</div>
                            <div class="stat-lbl">Users</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num" id="sAgents">—</div>
                            <div class="stat-lbl">Agents</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num" id="sOffices">—</div>
                            <div class="stat-lbl">Offices</div>
                        </div>
                    </div>

                    <div class="bc-sub-bar">
                        <button type="submit" form="bcForm" class="bc-btn-send" id="bcBtn">
                            <div class="bc-spin" id="bcSpin"></div>
                            <svg id="bcBtnIco" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span id="bcBtnLbl">Send Broadcast</span>
                        </button>
                        <p class="bc-sub-note">This action is immediate and irreversible.</p>
                    </div>
                </div>
            </div>

        </div>{{-- /bc-grid --}}
    </form>

</div>{{-- /bc-page --}}

<script>
// ── Lang tabs ──────────────────────────────────────────
document.querySelectorAll('.lang-tab').forEach(t => {
    t.addEventListener('click', () => {
        const lang = t.dataset.lang;
        document.querySelectorAll('.lang-tab').forEach(x => x.classList.remove('on'));
        document.querySelectorAll('.lang-pane').forEach(x => x.classList.remove('on'));
        t.classList.add('on');
        document.getElementById('lp-' + lang).classList.add('on');
    });
});

// ── Live preview ───────────────────────────────────────
function livePreview() {
    const title    = document.getElementById('title_en').value    || 'Your notification title';
    const msg      = document.getElementById('message_en').value  || 'Your notification message will appear here…';
    const priority = document.getElementById('bcPriority').value  || 'medium';
    document.getElementById('prevTitle').textContent = title;
    document.getElementById('prevMsg').textContent   = msg;
    const badge  = document.getElementById('prevBadge');
    const labels = { low:'● Low', medium:'● Medium', high:'● High', urgent:'🚨 Urgent' };
    badge.textContent = labels[priority];
    badge.className   = 'nb-badge ' + priority;
}

// ── Char counter ───────────────────────────────────────
function cc(el, max, id) {
    const n = el.value.length;
    const d = document.getElementById(id);
    d.textContent = n + ' / ' + max;
    d.className   = 'bc-cc' + (n >= max ? ' over' : n > max * .88 ? ' warn' : '');
}

// ── Image ──────────────────────────────────────────────
function onFileChange(input) {
    const f = input.files[0];
    if (!f) return;
    if (f.size > 2097152) { alert('Image must be under 2 MB.'); input.value = ''; return; }
    const r = new FileReader();
    r.onload = e => { showPrev(e.target.result); document.getElementById('imgUrl').value = ''; };
    r.readAsDataURL(f);
}
function onUrlInput() {
    if (document.getElementById('imgUrl').value.trim()) {
        document.getElementById('imgFile').value = '';
    }
}
function prevFromUrl() {
    const u = document.getElementById('imgUrl').value.trim();
    if (u) showPrev(u);
}
function showPrev(src) {
    document.getElementById('imgPrev').src  = src;
    document.getElementById('imgPrevWrap').classList.add('show');
    const pi = document.getElementById('prevImg');
    pi.src = src; pi.classList.add('show');
}
function rmImg() {
    document.getElementById('imgFile').value = '';
    document.getElementById('imgUrl').value  = '';
    document.getElementById('imgPrevWrap').classList.remove('show');
    const pi = document.getElementById('prevImg');
    pi.src = ''; pi.classList.remove('show');
}
// Drag & drop
const zone = document.getElementById('imgZone');
zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('over');
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) {
        const dt = new DataTransfer(); dt.items.add(f);
        document.getElementById('imgFile').files = dt.files;
        onFileChange(document.getElementById('imgFile'));
    }
});

// ── Recipient counts ───────────────────────────────────
let _cTimer;
function fetchCounts() {
    clearTimeout(_cTimer);
    _cTimer = setTimeout(async () => {
        const rType = document.querySelector('input[name=recipient_type]:checked')?.value || 'all';
        try {
            const r = await fetch(`{{ route('admin.notifications.broadcast') }}?_counts=1&recipient_type=${rType}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!r.ok) return;
            const j = await r.json();
            if (j.counts) {
                document.getElementById('sUsers').textContent   = j.counts.users   ?? '—';
                document.getElementById('sAgents').textContent  = j.counts.agents  ?? '—';
                document.getElementById('sOffices').textContent = j.counts.offices ?? '—';
            }
        } catch (_) {}
    }, 400);
}
fetchCounts();

// ── Submit ─────────────────────────────────────────────
document.getElementById('bcForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!document.getElementById('title_en').value.trim() ||
        !document.getElementById('message_en').value.trim()) {
        showRes('err', '✕ English title and message are required.');
        return;
    }

    setLoad(true);
    const fd = new FormData(this);
    const urlVal = document.getElementById('imgUrl').value.trim();
    if (!document.getElementById('imgFile').files.length && urlVal) {
        fd.set('image_url', urlVal);
    }

    try {
        const res  = await fetch('{{ route("admin.notifications.broadcast") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: fd,
        });
        const json = await res.json();

        if (res.ok && (json.status || json.success)) {
            const d = json.data ?? {};
            showRes('ok', `✓ Broadcast sent to <strong>${d.sent_to ?? '?'}</strong> recipients — ${d.users ?? 0} users, ${d.agents ?? 0} agents, ${d.offices ?? 0} offices.`);
            if (d.warnings?.length) showWarns(d.warnings);
            this.reset(); rmImg(); livePreview();
        } else {
            const errs = json.errors ? '<br>' + Object.values(json.errors).flat().join('<br>') : '';
            showRes('err', '✕ ' + (json.message ?? 'Failed to send.') + errs);
        }
    } catch (err) {
        showRes('err', '✕ Network error: ' + err.message);
    } finally {
        setLoad(false);
    }
});

function setLoad(on) {
    document.getElementById('bcBtn').disabled      = on;
    document.getElementById('bcBtnIco').style.display = on ? 'none' : '';
    document.getElementById('bcSpin').classList.toggle('show', on);
    document.getElementById('bcBtnLbl').textContent = on ? 'Sending…' : 'Send Broadcast';
}
function showRes(type, html) {
    const el = document.getElementById('bcResult');
    el.className = 'bc-result show ' + type;
    document.getElementById('bcResultMsg').innerHTML = html;
    document.getElementById('bcWarnBox').classList.remove('show');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function showWarns(w) {
    const box = document.getElementById('bcWarnBox');
    document.getElementById('bcWarnList').innerHTML = w.map(x => `<li>${x}</li>`).join('');
    box.classList.add('show');
}

livePreview();
</script>

@endsection

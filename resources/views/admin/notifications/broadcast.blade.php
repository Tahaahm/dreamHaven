{{-- resources/views/admin/notifications/broadcast.blade.php --}}
@extends('layouts.admin')

@section('title', 'Broadcast Notification')

@section('styles')
<style>
    /* ── Tokens ─────────────────────────────────────────── */
    :root {
        --dm-purple:   #6C3FC5;
        --dm-purple-d: #5A2FB0;
        --dm-purple-l: #F0EBFF;
        --dm-purple-m: #7C4FD4;
        --dm-gold:     #F5A623;
        --dm-red:      #E53E3E;
        --dm-green:    #38A169;
        --dm-gray-50:  #F9FAFB;
        --dm-gray-100: #F3F4F6;
        --dm-gray-200: #E5E7EB;
        --dm-gray-400: #9CA3AF;
        --dm-gray-600: #4B5563;
        --dm-gray-700: #374151;
        --dm-gray-900: #111827;
        --radius:      12px;
        --shadow:      0 4px 24px rgba(108,63,197,.10);
        --shadow-lg:   0 8px 40px rgba(108,63,197,.18);
    }

    * { box-sizing: border-box; }

    body { background: var(--dm-gray-50); font-family: 'Segoe UI', system-ui, sans-serif; }

    /* ── Page layout ─────────────────────────────────────── */
    .bc-wrapper {
        max-width: 1100px;
        margin: 0 auto;
        padding: 32px 20px 80px;
    }

    .bc-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 32px;
    }
    .bc-header-icon {
        width: 52px; height: 52px;
        background: linear-gradient(135deg, var(--dm-purple), var(--dm-purple-m));
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        box-shadow: var(--shadow);
    }
    .bc-header-icon svg { color: #fff; }
    .bc-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--dm-gray-900); margin: 0; }
    .bc-header p  { font-size: .875rem; color: var(--dm-gray-400); margin: 2px 0 0; }

    /* ── Two-column grid ─────────────────────────────────── */
    .bc-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 820px) { .bc-grid { grid-template-columns: 1fr; } }

    /* ── Cards ───────────────────────────────────────────── */
    .bc-card {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .bc-card-head {
        padding: 18px 22px 14px;
        border-bottom: 1px solid var(--dm-gray-100);
        display: flex; align-items: center; gap: 10px;
    }
    .bc-card-head h2 {
        font-size: .95rem; font-weight: 700;
        color: var(--dm-gray-700); margin: 0;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .bc-card-head .badge {
        font-size: .7rem; font-weight: 700;
        padding: 2px 8px; border-radius: 20px;
        background: var(--dm-purple-l); color: var(--dm-purple);
    }
    .bc-card-body { padding: 22px; }

    /* ── Form elements ───────────────────────────────────── */
    .bc-field { margin-bottom: 20px; }
    .bc-field:last-child { margin-bottom: 0; }

    label {
        display: block;
        font-size: .8rem; font-weight: 600;
        color: var(--dm-gray-600);
        text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: 6px;
    }
    label .req { color: var(--dm-red); margin-left: 2px; }
    label .hint { font-weight: 400; text-transform: none; color: var(--dm-gray-400); margin-left: 6px; letter-spacing: 0; }

    input[type=text],
    input[type=url],
    input[type=datetime-local],
    select,
    textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--dm-gray-200);
        border-radius: 8px;
        font-size: .925rem;
        color: var(--dm-gray-700);
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
        outline: none;
    }
    input:focus, select:focus, textarea:focus {
        border-color: var(--dm-purple);
        box-shadow: 0 0 0 3px rgba(108,63,197,.12);
    }
    textarea { resize: vertical; min-height: 80px; }

    /* ── Language tabs ───────────────────────────────────── */
    .lang-tabs { display: flex; gap: 6px; margin-bottom: 16px; }
    .lang-tab {
        padding: 6px 14px;
        border-radius: 20px;
        border: 1.5px solid var(--dm-gray-200);
        background: #fff;
        font-size: .8rem; font-weight: 600;
        color: var(--dm-gray-600);
        cursor: pointer; transition: all .15s;
    }
    .lang-tab.active {
        background: var(--dm-purple);
        border-color: var(--dm-purple);
        color: #fff;
    }
    .lang-tab:hover:not(.active) { border-color: var(--dm-purple); color: var(--dm-purple); }

    .lang-pane { display: none; }
    .lang-pane.active { display: block; }

    /* ── Image upload zone ───────────────────────────────── */
    .image-zone {
        border: 2px dashed var(--dm-gray-200);
        border-radius: 10px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        position: relative;
        background: var(--dm-gray-50);
    }
    .image-zone:hover, .image-zone.dragover {
        border-color: var(--dm-purple);
        background: var(--dm-purple-l);
    }
    .image-zone input[type=file] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer;
        width: 100%; height: 100%;
    }
    .image-zone-icon { font-size: 2rem; margin-bottom: 8px; }
    .image-zone p { margin: 0; font-size: .875rem; color: var(--dm-gray-400); }
    .image-zone strong { color: var(--dm-purple); }

    .image-preview-wrap {
        margin-top: 14px;
        position: relative;
        display: none;
    }
    .image-preview-wrap.show { display: block; }
    .image-preview-wrap img {
        width: 100%; max-height: 160px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--dm-gray-200);
    }
    .image-remove-btn {
        position: absolute; top: 8px; right: 8px;
        width: 26px; height: 26px;
        border-radius: 50%;
        background: rgba(0,0,0,.55);
        border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .8rem;
        transition: background .15s;
    }
    .image-remove-btn:hover { background: var(--dm-red); }

    .image-url-row {
        display: flex; gap: 8px; align-items: center;
        margin-top: 10px;
    }
    .image-url-row input { flex: 1; }
    .image-url-btn {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1.5px solid var(--dm-gray-200);
        background: #fff;
        font-size: .8rem; font-weight: 600;
        color: var(--dm-gray-600); cursor: pointer;
        white-space: nowrap; transition: all .15s;
    }
    .image-url-btn:hover { border-color: var(--dm-purple); color: var(--dm-purple); }

    /* ── Recipient chips ─────────────────────────────────── */
    .recipient-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .recipient-opt {
        position: relative;
    }
    .recipient-opt input[type=radio] {
        position: absolute; opacity: 0; width: 0;
    }
    .recipient-opt label {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 14px;
        border: 1.5px solid var(--dm-gray-200);
        border-radius: 10px;
        cursor: pointer;
        transition: all .15s;
        text-transform: none;
        letter-spacing: 0;
        font-size: .875rem;
        font-weight: 600;
        color: var(--dm-gray-600);
    }
    .recipient-opt input:checked + label {
        border-color: var(--dm-purple);
        background: var(--dm-purple-l);
        color: var(--dm-purple);
    }
    .recipient-opt label .rc-icon {
        font-size: 1.2rem;
    }

    /* ── Priority & Type selects ─────────────────────────── */
    .select-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    /* ── Preview card ────────────────────────────────────── */
    .preview-card {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: sticky;
        top: 24px;
    }
    .preview-card-head {
        padding: 16px 18px 12px;
        border-bottom: 1px solid var(--dm-gray-100);
        display: flex; align-items: center; gap: 10px;
    }
    .preview-card-head h2 {
        font-size: .85rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--dm-gray-600); margin: 0;
    }

    /* Phone mockup */
    .phone-mockup {
        width: 240px;
        margin: 20px auto;
        background: var(--dm-gray-900);
        border-radius: 32px;
        padding: 14px 10px;
        box-shadow: 0 16px 48px rgba(0,0,0,.25);
        position: relative;
    }
    .phone-mockup::before {
        content: '';
        display: block;
        width: 60px; height: 6px;
        background: #333;
        border-radius: 3px;
        margin: 0 auto 12px;
    }
    .phone-screen {
        background: #fff;
        border-radius: 22px;
        overflow: hidden;
        min-height: 420px;
    }
    .phone-status {
        background: var(--dm-gray-900);
        color: #fff;
        font-size: .55rem;
        padding: 4px 14px;
        display: flex; justify-content: space-between;
    }
    .phone-notif-bar {
        background: #f1f1f1;
        padding: 6px 8px;
        font-size: .6rem;
        color: var(--dm-gray-600);
        border-bottom: 1px solid var(--dm-gray-200);
    }

    /* Notification bubble in preview */
    .notif-bubble {
        margin: 10px 8px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.12);
        overflow: hidden;
        border: 1px solid var(--dm-gray-100);
    }
    .notif-bubble-header {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 10px 4px;
    }
    .notif-bubble-app {
        width: 18px; height: 18px;
        background: linear-gradient(135deg, var(--dm-purple), var(--dm-purple-m));
        border-radius: 5px;
    }
    .notif-bubble-appname {
        font-size: .55rem; font-weight: 700;
        color: var(--dm-gray-400); text-transform: uppercase;
        letter-spacing: .05em; flex: 1;
    }
    .notif-bubble-time { font-size: .55rem; color: var(--dm-gray-400); }
    .notif-bubble-content { padding: 0 10px 8px; }
    .notif-bubble-img {
        width: 100%; max-height: 80px;
        object-fit: cover;
        display: none;
        margin-bottom: 6px;
        border-radius: 0;
    }
    .notif-bubble-img.show { display: block; }
    .notif-bubble-title {
        font-size: .7rem; font-weight: 700;
        color: var(--dm-gray-900); margin-bottom: 3px;
    }
    .notif-bubble-msg {
        font-size: .65rem;
        color: var(--dm-gray-600);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Priority badge */
    .priority-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: .6rem; font-weight: 700;
        padding: 2px 8px; border-radius: 20px;
        margin-top: 5px;
    }
    .priority-badge.low     { background: #EBF8FF; color: #2B6CB0; }
    .priority-badge.medium  { background: #FFFBEB; color: #B45309; }
    .priority-badge.high    { background: #FFF5F5; color: var(--dm-red); }
    .priority-badge.urgent  { background: #FFF5F5; color: var(--dm-red); animation: pulse 1.2s infinite; }
    @keyframes pulse {
        0%, 100% { opacity: 1; } 50% { opacity: .6; }
    }

    /* ── Stats preview ───────────────────────────────────── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 14px 16px;
        border-top: 1px solid var(--dm-gray-100);
    }
    .stat-item { text-align: center; }
    .stat-item .num { font-size: 1.2rem; font-weight: 800; color: var(--dm-purple); }
    .stat-item .lbl { font-size: .65rem; color: var(--dm-gray-400); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }

    /* ── Submit button ───────────────────────────────────── */
    .bc-submit-bar {
        padding: 18px 22px;
        border-top: 1px solid var(--dm-gray-100);
    }
    .bc-btn-primary {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--dm-purple), var(--dm-purple-m));
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: .95rem; font-weight: 700;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: all .2s;
        box-shadow: 0 4px 16px rgba(108,63,197,.3);
    }
    .bc-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(108,63,197,.4); }
    .bc-btn-primary:active { transform: none; }
    .bc-btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* ── Result banner ───────────────────────────────────── */
    .result-banner {
        display: none;
        margin-bottom: 24px;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: .9rem;
        font-weight: 600;
        gap: 10px;
        align-items: flex-start;
    }
    .result-banner.show { display: flex; }
    .result-banner.success { background: #F0FFF4; border: 1.5px solid #9AE6B4; color: #276749; }
    .result-banner.error   { background: #FFF5F5; border: 1.5px solid #FEB2B2; color: #9B2C2C; }

    /* ── Warnings ────────────────────────────────────────── */
    .warnings-box {
        display: none;
        margin-top: 14px;
        padding: 12px 14px;
        background: #FFFBEB;
        border: 1.5px solid #F6E05E;
        border-radius: 8px;
        font-size: .8rem;
        color: #744210;
    }
    .warnings-box.show { display: block; }
    .warnings-box ul { margin: 6px 0 0; padding-left: 16px; }
    .warnings-box li { margin-bottom: 4px; }

    /* ── Spinner ─────────────────────────────────────────── */
    .spinner {
        width: 18px; height: 18px;
        border: 2px solid rgba(255,255,255,.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
        display: none;
    }
    .spinner.show { display: block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Char counter ────────────────────────────────────── */
    .char-count {
        font-size: .72rem;
        color: var(--dm-gray-400);
        text-align: right;
        margin-top: 4px;
    }
    .char-count.warn { color: var(--dm-gold); }
    .char-count.over { color: var(--dm-red); font-weight: 700; }
</style>
@endsection

@section('content')
<div class="bc-wrapper">

    {{-- Header --}}
    <div class="bc-header">
        <div class="bc-header-icon">
            <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div>
            <h1>Broadcast Notification</h1>
            <p>Send push notifications to users, agents, and offices — with full multilingual support</p>
        </div>
    </div>

    {{-- Result banner --}}
    <div class="result-banner" id="resultBanner">
        <span id="resultIcon"></span>
        <div>
            <div id="resultMsg"></div>
            <div class="warnings-box" id="warningsBox">
                <strong>⚠ Translation warnings:</strong>
                <ul id="warningsList"></ul>
            </div>
        </div>
    </div>

    <form id="broadcastForm" enctype="multipart/form-data">
        @csrf

        <div class="bc-grid">
            {{-- LEFT COLUMN --}}
            <div>

                {{-- Content card --}}
                <div class="bc-card" style="margin-bottom:24px">
                    <div class="bc-card-head">
                        <h2>Notification Content</h2>
                        <span class="badge">Multilingual</span>
                    </div>
                    <div class="bc-card-body">

                        {{-- Language tabs --}}
                        <div class="lang-tabs">
                            <button type="button" class="lang-tab active" data-lang="en">🇬🇧 English</button>
                            <button type="button" class="lang-tab" data-lang="ar">🇸🇦 Arabic</button>
                            <button type="button" class="lang-tab" data-lang="ku">🟢 Kurdish</button>
                        </div>

                        {{-- EN --}}
                        <div class="lang-pane active" id="pane-en">
                            <div class="bc-field">
                                <label>Title <span class="req">*</span></label>
                                <input type="text" name="title_en" id="title_en" maxlength="100" placeholder="e.g. New properties in your area!" oninput="updatePreview();updateCount(this,100,'cnt-title-en')">
                                <div class="char-count" id="cnt-title-en">0 / 100</div>
                            </div>
                            <div class="bc-field">
                                <label>Message <span class="req">*</span></label>
                                <textarea name="message_en" id="message_en" maxlength="500" rows="3" placeholder="Describe what this notification is about…" oninput="updatePreview();updateCount(this,500,'cnt-msg-en')"></textarea>
                                <div class="char-count" id="cnt-msg-en">0 / 500</div>
                            </div>
                        </div>

                        {{-- AR --}}
                        <div class="lang-pane" id="pane-ar">
                            <div class="bc-field">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ar" id="title_ar" maxlength="100" placeholder="مثال: عقارات جديدة في منطقتك!" dir="rtl">
                            </div>
                            <div class="bc-field">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ar" id="message_ar" maxlength="500" rows="3" placeholder="وصف الإشعار…" dir="rtl"></textarea>
                            </div>
                        </div>

                        {{-- KU --}}
                        <div class="lang-pane" id="pane-ku">
                            <div class="bc-field">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ku" id="title_ku" maxlength="100" placeholder="نموونە: خانووی نوێ لە ناوچەکەتدا!" dir="rtl">
                            </div>
                            <div class="bc-field">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ku" id="message_ku" maxlength="500" rows="3" placeholder="ڕوونکردنەوەی ئاگادارکردنەوەکە…" dir="rtl"></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Image card --}}
                <div class="bc-card" style="margin-bottom:24px">
                    <div class="bc-card-head">
                        <h2>Notification Image</h2>
                        <span class="badge">Optional</span>
                    </div>
                    <div class="bc-card-body">
                        <p style="font-size:.85rem;color:var(--dm-gray-400);margin:0 0 16px">
                            An image makes your notification stand out in the tray. Recommended: 1200×628 px, max 2 MB.
                        </p>

                        {{-- Drop zone --}}
                        <div class="image-zone" id="imageZone">
                            <input type="file" name="image" id="imageFile" accept="image/jpeg,image/png,image/webp" onchange="handleFileSelect(this)">
                            <div class="image-zone-icon">🖼</div>
                            <p><strong>Click to upload</strong> or drag & drop</p>
                            <p>JPG, PNG, WEBP · max 2 MB</p>
                        </div>

                        <div class="image-preview-wrap" id="imgPreviewWrap">
                            <img id="imgPreview" src="" alt="Preview">
                            <button type="button" class="image-remove-btn" onclick="removeImage()" title="Remove image">✕</button>
                        </div>

                        <div style="margin-top:16px">
                            <label>Or paste an image URL</label>
                            <div class="image-url-row">
                                <input type="url" name="image_url" id="imageUrl" placeholder="https://dreammulk.com/storage/..." oninput="handleUrlInput()">
                                <button type="button" class="image-url-btn" onclick="previewFromUrl()">Preview</button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Settings card --}}
                <div class="bc-card">
                    <div class="bc-card-head">
                        <h2>Settings</h2>
                    </div>
                    <div class="bc-card-body">

                        <div class="bc-field">
                            <label>Recipients <span class="req">*</span></label>
                            <div class="recipient-grid">
                                <div class="recipient-opt">
                                    <input type="radio" name="recipient_type" id="r-all" value="all" checked onchange="updatePreview()">
                                    <label for="r-all"><span class="rc-icon">🌍</span> Everyone</label>
                                </div>
                                <div class="recipient-opt">
                                    <input type="radio" name="recipient_type" id="r-users" value="users" onchange="updatePreview()">
                                    <label for="r-users"><span class="rc-icon">👤</span> Users only</label>
                                </div>
                                <div class="recipient-opt">
                                    <input type="radio" name="recipient_type" id="r-agents" value="agents" onchange="updatePreview()">
                                    <label for="r-agents"><span class="rc-icon">🏷</span> Agents only</label>
                                </div>
                                <div class="recipient-opt">
                                    <input type="radio" name="recipient_type" id="r-offices" value="offices" onchange="updatePreview()">
                                    <label for="r-offices"><span class="rc-icon">🏢</span> Offices only</label>
                                </div>
                            </div>
                        </div>

                        <div class="select-row bc-field">
                            <div>
                                <label>Type <span class="req">*</span></label>
                                <select name="type" id="notifType" onchange="updatePreview()">
                                    <option value="system">🔧 System</option>
                                    <option value="property">🏠 Property</option>
                                    <option value="promotion">🎉 Promotion</option>
                                    <option value="alert">⚠ Alert</option>
                                </select>
                            </div>
                            <div>
                                <label>Priority <span class="req">*</span></label>
                                <select name="priority" id="notifPriority" onchange="updatePreview()">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">🚨 Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="bc-field">
                            <label>Action URL <span class="hint">(where to open on tap)</span></label>
                            <input type="text" name="action_url" id="actionUrl" placeholder="/properties or https://dreammulk.com/...">
                        </div>

                        <div class="bc-field">
                            <label>Action Button Text <span class="hint">(optional)</span></label>
                            <input type="text" name="action_text" id="actionText" maxlength="60" placeholder="e.g. View Properties">
                        </div>

                        <div class="bc-field">
                            <label>Expires At <span class="hint">(leave blank = never)</span></label>
                            <input type="datetime-local" name="expires_at" id="expiresAt">
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN — preview + submit --}}
            <div>
                <div class="preview-card">
                    <div class="preview-card-head">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <h2>Live Preview</h2>
                    </div>

                    <div style="padding:16px 16px 0">
                        <div class="phone-mockup">
                            <div class="phone-screen">
                                <div class="phone-status">
                                    <span>9:41 AM</span>
                                    <span>▶ ● ☰</span>
                                </div>
                                <div class="phone-notif-bar">Notifications · 3 new</div>

                                <div class="notif-bubble">
                                    <div class="notif-bubble-header">
                                        <div class="notif-bubble-app"></div>
                                        <span class="notif-bubble-appname">Dream Mulk</span>
                                        <span class="notif-bubble-time">now</span>
                                    </div>
                                    <div class="notif-bubble-content">
                                        <img id="previewImg" class="notif-bubble-img" src="" alt="">
                                        <div class="notif-bubble-title" id="previewTitle">Your notification title</div>
                                        <div class="notif-bubble-msg" id="previewMsg">Your notification message will appear here…</div>
                                        <span class="priority-badge medium" id="previewBadge">● Medium</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="num" id="statUsers">—</div>
                            <div class="lbl">Users</div>
                        </div>
                        <div class="stat-item">
                            <div class="num" id="statAgents">—</div>
                            <div class="lbl">Agents</div>
                        </div>
                        <div class="stat-item">
                            <div class="num" id="statOffices">—</div>
                            <div class="lbl">Offices</div>
                        </div>
                    </div>

                    <div class="bc-submit-bar">
                        <button type="submit" class="bc-btn-primary" id="submitBtn">
                            <div class="spinner" id="submitSpinner"></div>
                            <svg id="submitIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span id="submitLabel">Send Broadcast</span>
                        </button>
                        <p style="text-align:center;font-size:.75rem;color:var(--dm-gray-400);margin:10px 0 0">
                            This action is immediate and irreversible.
                        </p>
                    </div>
                </div>
            </div>

        </div>{{-- /bc-grid --}}
    </form>
</div>
@endsection

@section('scripts')
<script>
// ── Lang tabs ──────────────────────────────────────────────────
document.querySelectorAll('.lang-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const lang = tab.dataset.lang;
        document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.lang-pane').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('pane-' + lang).classList.add('active');
    });
});

// ── Live preview ───────────────────────────────────────────────
function updatePreview() {
    const title    = document.getElementById('title_en').value || 'Your notification title';
    const msg      = document.getElementById('message_en').value || 'Your notification message will appear here…';
    const priority = document.getElementById('notifPriority').value;

    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewMsg').textContent   = msg;

    const badge = document.getElementById('previewBadge');
    const labels = { low: '● Low', medium: '● Medium', high: '● High', urgent: '🚨 Urgent' };
    badge.textContent = labels[priority] || '● Medium';
    badge.className   = 'priority-badge ' + priority;
}

// ── Char counter ───────────────────────────────────────────────
function updateCount(el, max, countId) {
    const len   = el.value.length;
    const el2   = document.getElementById(countId);
    el2.textContent = len + ' / ' + max;
    el2.className   = 'char-count' + (len > max * .9 ? (len >= max ? ' over' : ' warn') : '');
}

// ── Image handling ─────────────────────────────────────────────
let currentImgSrc = null;

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be under 2 MB.');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        currentImgSrc = e.target.result;
        showImagePreview(currentImgSrc);
        // Clear URL field when file is chosen
        document.getElementById('imageUrl').value = '';
    };
    reader.readAsDataURL(file);
}

function handleUrlInput() {
    // Clear file input if user types a URL
    const urlVal = document.getElementById('imageUrl').value.trim();
    if (urlVal) {
        document.getElementById('imageFile').value = '';
    }
}

function previewFromUrl() {
    const url = document.getElementById('imageUrl').value.trim();
    if (!url) return;
    currentImgSrc = url;
    showImagePreview(url);
}

function showImagePreview(src) {
    document.getElementById('imgPreview').src = src;
    document.getElementById('imgPreviewWrap').classList.add('show');

    const previewImg = document.getElementById('previewImg');
    previewImg.src = src;
    previewImg.classList.add('show');
}

function removeImage() {
    currentImgSrc = null;
    document.getElementById('imageFile').value = '';
    document.getElementById('imageUrl').value  = '';
    document.getElementById('imgPreviewWrap').classList.remove('show');
    const p = document.getElementById('previewImg');
    p.src = ''; p.classList.remove('show');
}

// Drag & drop
const zone = document.getElementById('imageZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('imageFile').files = dt.files;
        handleFileSelect(document.getElementById('imageFile'));
    }
});

// ── Recipient counts (live via AJAX) ───────────────────────────
let countDebounce;
function fetchCounts() {
    clearTimeout(countDebounce);
    countDebounce = setTimeout(async () => {
        const rType = document.querySelector('input[name=recipient_type]:checked')?.value || 'all';
        try {
            const res = await fetch(`{{ route('admin.notifications.broadcast') }}?_counts=1&recipient_type=${rType}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const json = await res.json();
            if (json.counts) {
                document.getElementById('statUsers').textContent   = json.counts.users   ?? '—';
                document.getElementById('statAgents').textContent  = json.counts.agents  ?? '—';
                document.getElementById('statOffices').textContent = json.counts.offices ?? '—';
            }
        } catch (_) {}
    }, 400);
}
document.querySelectorAll('input[name=recipient_type]').forEach(r => r.addEventListener('change', fetchCounts));
fetchCounts();

// ── Form submit ────────────────────────────────────────────────
document.getElementById('broadcastForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const titleEn = document.getElementById('title_en').value.trim();
    const msgEn   = document.getElementById('message_en').value.trim();
    if (!titleEn || !msgEn) {
        showResult('error', '✕  English title and message are required.');
        return;
    }

    setLoading(true);

    const formData = new FormData(this);

    // If URL was provided but no file, ensure image_url is in the payload
    const urlVal = document.getElementById('imageUrl').value.trim();
    const fileInput = document.getElementById('imageFile');
    if (!fileInput.files.length && urlVal) {
        formData.set('image_url', urlVal);
    }

    try {
        const res = await fetch('{{ route("admin.notifications.broadcast") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const json = await res.json();

        if (res.ok && (json.status || json.success)) {
            const d = json.data ?? {};
            showResult('success', `✓  Broadcast sent to <strong>${d.sent_to ?? '?'}</strong> recipients — ${d.users ?? 0} users, ${d.agents ?? 0} agents, ${d.offices ?? 0} offices.`);
            if (d.warnings && d.warnings.length) showWarnings(d.warnings);
            this.reset();
            removeImage();
            updatePreview();
        } else {
            const errMsg = json.message ?? 'Failed to send broadcast. Please try again.';
            const errors = json.errors ? '<br>' + Object.values(json.errors).flat().join('<br>') : '';
            showResult('error', '✕  ' + errMsg + errors);
        }
    } catch (err) {
        showResult('error', '✕  Network error: ' + err.message);
    } finally {
        setLoading(false);
    }
});

function setLoading(on) {
    const btn    = document.getElementById('submitBtn');
    const icon   = document.getElementById('submitIcon');
    const lbl    = document.getElementById('submitLabel');
    const spin   = document.getElementById('submitSpinner');
    btn.disabled = on;
    icon.style.display = on ? 'none' : '';
    spin.classList.toggle('show', on);
    lbl.textContent = on ? 'Sending…' : 'Send Broadcast';
}

function showResult(type, html) {
    const el  = document.getElementById('resultBanner');
    const msg = document.getElementById('resultMsg');
    el.className   = 'result-banner show ' + type;
    msg.innerHTML  = html;
    document.getElementById('warningsBox').classList.remove('show');
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showWarnings(warnings) {
    const box  = document.getElementById('warningsBox');
    const list = document.getElementById('warningsList');
    list.innerHTML = warnings.map(w => `<li>${w}</li>`).join('');
    box.classList.add('show');
}

// Init preview
updatePreview();
</script>
@endsection

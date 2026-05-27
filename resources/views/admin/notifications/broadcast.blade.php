{{-- resources/views/admin/notifications/broadcast.blade.php --}}
@extends('layouts.admin-layout')

@section('title', 'Broadcast Notification')

@section('content')
<style>
.dmbc * { box-sizing: border-box; }
.dmbc { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

.dmbc-wrap {
    max-width: 1020px;
    margin: 0 auto;
    padding: 36px 28px 100px;
}

/* Header */
.dmbc-hdr { display:flex; align-items:center; gap:18px; margin-bottom:36px; padding-bottom:28px; border-bottom:1px solid #E5E7EB; }
.dmbc-hdr-ico { width:56px; height:56px; flex-shrink:0; background:linear-gradient(145deg,#7C4FD4,#6C3FC5); border-radius:18px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(108,63,197,.38); }
.dmbc-hdr-ico svg { width:26px; height:26px; color:#fff; }
.dmbc-hdr-txt h1 { font-size:1.45rem; font-weight:800; color:#111827; margin:0 0 4px; letter-spacing:-.025em; }
.dmbc-hdr-txt p { font-size:.83rem; color:#9CA3AF; margin:0; }

/* Result banner */
.dmbc-result { display:none; padding:14px 18px; border-radius:12px; font-size:.875rem; font-weight:600; margin-bottom:28px; gap:12px; align-items:flex-start; animation:dmbcfi .2s ease; }
@keyframes dmbcfi { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:none} }
.dmbc-result.show { display:flex; }
.dmbc-result.ok  { background:#F0FFF4; border:1.5px solid #86EFAC; color:#166534; }
.dmbc-result.err { background:#FFF5F5; border:1.5px solid #FECACA; color:#991B1B; }
.dmbc-warn { display:none; margin-top:10px; padding:10px 14px; background:#FFFBEB; border:1.5px solid #FCD34D; border-radius:8px; font-size:.8rem; color:#92400E; font-weight:500; }
.dmbc-warn.show { display:block; }
.dmbc-warn ul { margin:6px 0 0; padding-left:18px; }
.dmbc-warn li  { margin-bottom:3px; }

/* Grid */
.dmbc-grid { display:grid; grid-template-columns:1fr 330px; gap:20px; align-items:start; }
@media(max-width:820px){ .dmbc-grid{grid-template-columns:1fr;} }

/* Card */
.dmbc-card { background:#fff; border-radius:14px; border:1px solid #F3F4F6; box-shadow:0 2px 16px rgba(0,0,0,.06); overflow:hidden; margin-bottom:18px; }
.dmbc-card:last-child { margin-bottom:0; }
.dmbc-card-hd { padding:16px 20px; border-bottom:1px solid #F3F4F6; display:flex; align-items:center; gap:10px; background:#FAFAFA; }
.dmbc-card-hd h2 { font-size:.72rem; font-weight:800; color:#6B7280; margin:0; flex:1; text-transform:uppercase; letter-spacing:.08em; }
.dmbc-pill { font-size:.62rem; font-weight:700; padding:3px 10px; border-radius:20px; text-transform:uppercase; letter-spacing:.05em; }
.dmbc-pill-p { background:#F0EBFF; color:#6C3FC5; }
.dmbc-pill-g { background:#F3F4F6; color:#6B7280; }
.dmbc-card-bd { padding:22px; }

/* Fields */
.dmbc-f { margin-bottom:16px; }
.dmbc-f:last-child { margin-bottom:0; }
.dmbc-f>label { display:block; font-size:.72rem; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:.07em; margin-bottom:7px; }
.req  { color:#EF4444; margin-left:2px; }
.hint { font-weight:400; text-transform:none; color:#9CA3AF; font-size:.72rem; margin-left:5px; letter-spacing:0; }
.dmbc-f input[type=text],.dmbc-f input[type=url],.dmbc-f input[type=datetime-local],.dmbc-f select,.dmbc-f textarea { width:100%; padding:10px 14px; border:1.5px solid #E5E7EB; border-radius:9px; font-size:.9rem; color:#374151; background:#fff; transition:border-color .15s,box-shadow .15s; outline:none; font-family:inherit; }
.dmbc-f input:focus,.dmbc-f select:focus,.dmbc-f textarea:focus { border-color:#6C3FC5; box-shadow:0 0 0 3px rgba(108,63,197,.1); }
.dmbc-f textarea { resize:vertical; min-height:80px; line-height:1.5; }
.dmbc-cc { font-size:.7rem; color:#9CA3AF; text-align:right; margin-top:5px; transition:color .15s; }
.dmbc-cc.warn { color:#F59E0B; }
.dmbc-cc.over { color:#EF4444; font-weight:700; }
.dmbc-2col { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

/* Lang tabs */
.dmbc-ltabs { display:flex; gap:0; margin-bottom:20px; border-bottom:2px solid #F3F4F6; }
.dmbc-ltab { padding:8px 18px 10px; border:none; background:transparent; font-size:.82rem; font-weight:700; color:#9CA3AF; cursor:pointer; transition:all .15s; border-bottom:2px solid transparent; margin-bottom:-2px; font-family:inherit; }
.dmbc-ltab.on { color:#6C3FC5; border-bottom-color:#6C3FC5; }
.dmbc-ltab:hover:not(.on) { color:#6C3FC5; }
.dmbc-lpane { display:none; }
.dmbc-lpane.on { display:block; }

/* Image drop */
.dmbc-drop { border:2px dashed #D1D5DB; border-radius:12px; padding:30px 20px; text-align:center; cursor:pointer; transition:all .2s; position:relative; background:#FAFAFA; }
.dmbc-drop:hover,.dmbc-drop.over { border-color:#6C3FC5; background:#F5F0FF; }
.dmbc-drop input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.dmbc-drop-ico { font-size:2rem; display:block; margin-bottom:10px; }
.dmbc-drop p { margin:4px 0; font-size:.84rem; color:#9CA3AF; }
.dmbc-drop strong { color:#6C3FC5; }
.dmbc-prev-img { margin-top:14px; position:relative; display:none; }
.dmbc-prev-img.show { display:block; }
.dmbc-prev-img img { width:100%; max-height:160px; object-fit:cover; border-radius:10px; border:1px solid #E5E7EB; display:block; }
.dmbc-rm-btn { position:absolute; top:8px; right:8px; width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,.55); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.85rem; transition:background .15s; }
.dmbc-rm-btn:hover { background:#EF4444; }
.dmbc-url-row { display:flex; gap:8px; margin-top:14px; }
.dmbc-url-row input { flex:1; padding:10px 14px; border:1.5px solid #E5E7EB; border-radius:9px; font-size:.875rem; color:#374151; outline:none; font-family:inherit; transition:border-color .15s; }
.dmbc-url-row input:focus { border-color:#6C3FC5; }
.dmbc-url-btn { padding:10px 16px; border-radius:9px; border:1.5px solid #E5E7EB; background:#fff; font-size:.8rem; font-weight:700; color:#6B7280; cursor:pointer; white-space:nowrap; transition:all .15s; font-family:inherit; }
.dmbc-url-btn:hover { border-color:#6C3FC5; color:#6C3FC5; background:#F5F0FF; }

/* Recipient chips */
.dmbc-rc-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.dmbc-rc { position:relative; }
.dmbc-rc input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
.dmbc-rc label { display:flex; align-items:center; gap:10px; padding:12px 14px; border:1.5px solid #E5E7EB; border-radius:11px; cursor:pointer; transition:all .15s; font-size:.875rem; font-weight:600; color:#4B5563; font-family:inherit; }
.dmbc-rc label .rci { font-size:1.1rem; }
.dmbc-rc input:checked+label { border-color:#6C3FC5; background:#F5F0FF; color:#6C3FC5; box-shadow:0 0 0 3px rgba(108,63,197,.08); }
.dmbc-rc label:hover { border-color:#6C3FC5; }

/* Right panel */
.dmbc-panel { background:#fff; border-radius:14px; border:1px solid #F3F4F6; box-shadow:0 2px 16px rgba(0,0,0,.06); overflow:hidden; position:sticky; top:24px; }
.dmbc-panel-hd { padding:15px 18px; border-bottom:1px solid #F3F4F6; background:#FAFAFA; display:flex; align-items:center; gap:8px; }
.dmbc-panel-hd svg { color:#9CA3AF; width:15px; height:15px; }
.dmbc-panel-hd h2 { font-size:.72rem; font-weight:800; color:#6B7280; margin:0; text-transform:uppercase; letter-spacing:.08em; }

/* Phone */
.dmbc-phone { width:210px; margin:22px auto 18px; background:#18181B; border-radius:30px; padding:14px 9px 18px; box-shadow:0 20px 48px rgba(0,0,0,.28),0 0 0 1px rgba(255,255,255,.04); }
.dmbc-phone::before { content:''; display:block; width:52px; height:5px; background:#27272A; border-radius:3px; margin:0 auto 12px; }
.dmbc-phone-scr { background:#F4F4F5; border-radius:20px; overflow:hidden; min-height:320px; }
.dmbc-ph-bar { background:#18181B; color:rgba(255,255,255,.5); font-size:.5rem; font-weight:600; padding:5px 14px; display:flex; justify-content:space-between; letter-spacing:.03em; }
.dmbc-ph-ntf { background:#E4E4E7; padding:5px 10px; font-size:.58rem; font-weight:700; color:#71717A; border-bottom:1px solid #D4D4D8; text-transform:uppercase; letter-spacing:.03em; }

/* Notif bubble */
.dmbc-nb { margin:10px 7px; background:#fff; border-radius:13px; box-shadow:0 2px 12px rgba(0,0,0,.12); overflow:hidden; border:1px solid #F3F4F6; }
.dmbc-nb-hd { display:flex; align-items:center; gap:6px; padding:8px 10px 4px; }
.dmbc-nb-ico { width:18px; height:18px; flex-shrink:0; background:linear-gradient(135deg,#6C3FC5,#7C4FD4); border-radius:5px; }
.dmbc-nb-app { font-size:.52rem; font-weight:800; color:#9CA3AF; text-transform:uppercase; letter-spacing:.06em; flex:1; }
.dmbc-nb-time { font-size:.52rem; color:#9CA3AF; }
.dmbc-nb-body { padding:0 10px 9px; }
.dmbc-nb-img { width:100%; max-height:68px; object-fit:cover; display:none; margin-bottom:6px; border-radius:4px; }
.dmbc-nb-img.show { display:block; }
.dmbc-nb-title { font-size:.68rem; font-weight:800; color:#111827; margin-bottom:3px; line-height:1.3; }
.dmbc-nb-msg { font-size:.62rem; color:#6B7280; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.dmbc-nb-badge { display:inline-flex; align-items:center; gap:3px; font-size:.57rem; font-weight:700; padding:2px 8px; border-radius:20px; margin-top:6px; }
.dmbc-nb-badge.low    { background:#EFF6FF; color:#1D4ED8; }
.dmbc-nb-badge.medium { background:#FFFBEB; color:#B45309; }
.dmbc-nb-badge.high   { background:#FFF5F5; color:#DC2626; }
.dmbc-nb-badge.urgent { background:#FFF5F5; color:#DC2626; animation:dmbcp 1.2s infinite; }
@keyframes dmbcp { 0%,100%{opacity:1} 50%{opacity:.5} }

/* Stats */
.dmbc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; padding:14px 16px; border-top:1px solid #F3F4F6; }
.dmbc-stat { text-align:center; }
.dmbc-stat-n { font-size:1.3rem; font-weight:900; color:#6C3FC5; line-height:1; }
.dmbc-stat-l { font-size:.6rem; font-weight:700; color:#9CA3AF; text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }

/* Submit */
.dmbc-sub { padding:16px 18px; border-top:1px solid #F3F4F6; }
.dmbc-btn { width:100%; padding:14px; background:linear-gradient(135deg,#6C3FC5 0%,#7C4FD4 100%); color:#fff; border:none; border-radius:11px; font-size:.95rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:9px; transition:all .2s; box-shadow:0 4px 18px rgba(108,63,197,.38); font-family:inherit; letter-spacing:-.01em; }
.dmbc-btn:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 28px rgba(108,63,197,.48); }
.dmbc-btn:active:not(:disabled) { transform:translateY(0); }
.dmbc-btn:disabled { opacity:.55; cursor:not-allowed; transform:none; }
.dmbc-spin { width:18px; height:18px; flex-shrink:0; border:2.5px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:dmbcsp .7s linear infinite; display:none; }
.dmbc-spin.on { display:block; }
@keyframes dmbcsp { to{transform:rotate(360deg)} }
.dmbc-sub-note { text-align:center; font-size:.7rem; color:#9CA3AF; margin:9px 0 0; }
</style>

<div class="dmbc">
<div class="dmbc-wrap">

    <div class="dmbc-hdr">
        <div class="dmbc-hdr-ico">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="dmbc-hdr-txt">
            <h1>Broadcast Notification</h1>
            <p>Send push notifications to users, agents &amp; offices — full multilingual support</p>
        </div>
    </div>

    <div class="dmbc-result" id="bcRes">
        <div>
            <div id="bcResMsg"></div>
            <div class="dmbc-warn" id="bcWarn">
                <strong>⚠ Translation warnings:</strong>
                <ul id="bcWarnList"></ul>
            </div>
        </div>
    </div>

    <form id="bcForm" enctype="multipart/form-data">
        @csrf
        <div class="dmbc-grid">

            <div>
                {{-- Content --}}
                <div class="dmbc-card">
                    <div class="dmbc-card-hd">
                        <h2>Notification Content</h2>
                        <span class="dmbc-pill dmbc-pill-p">Multilingual</span>
                    </div>
                    <div class="dmbc-card-bd">
                        <div class="dmbc-ltabs">
                            <button type="button" class="dmbc-ltab on" data-lang="en">🇬🇧 English</button>
                            <button type="button" class="dmbc-ltab" data-lang="ar">🇸🇦 Arabic</button>
                            <button type="button" class="dmbc-ltab" data-lang="ku">🟢 Kurdish</button>
                        </div>
                        <div class="dmbc-lpane on" id="lp-en">
                            <div class="dmbc-f">
                                <label>Title <span class="req">*</span></label>
                                <input type="text" name="title_en" id="title_en" maxlength="100" placeholder="e.g. New properties in your area!" oninput="lp();dmCC(this,100,'cten')">
                                <div class="dmbc-cc" id="cten">0 / 100</div>
                            </div>
                            <div class="dmbc-f">
                                <label>Message <span class="req">*</span></label>
                                <textarea name="message_en" id="message_en" maxlength="500" rows="3" placeholder="Describe what this notification is about…" oninput="lp();dmCC(this,500,'cmen')"></textarea>
                                <div class="dmbc-cc" id="cmen">0 / 500</div>
                            </div>
                        </div>
                        <div class="dmbc-lpane" id="lp-ar">
                            <div class="dmbc-f">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ar" maxlength="100" placeholder="مثال: عقارات جديدة في منطقتك!" dir="rtl">
                            </div>
                            <div class="dmbc-f">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ar" maxlength="500" rows="3" placeholder="وصف الإشعار…" dir="rtl"></textarea>
                            </div>
                        </div>
                        <div class="dmbc-lpane" id="lp-ku">
                            <div class="dmbc-f">
                                <label>Title <span class="hint">(optional)</span></label>
                                <input type="text" name="title_ku" maxlength="100" placeholder="نموونە: خانووی نوێ لە ناوچەکەتدا!" dir="rtl">
                            </div>
                            <div class="dmbc-f">
                                <label>Message <span class="hint">(optional)</span></label>
                                <textarea name="message_ku" maxlength="500" rows="3" placeholder="ڕوونکردنەوەی ئاگادارکردنەوەکە…" dir="rtl"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image --}}
                <div class="dmbc-card">
                    <div class="dmbc-card-hd">
                        <h2>Notification Image</h2>
                        <span class="dmbc-pill dmbc-pill-g">Optional</span>
                    </div>
                    <div class="dmbc-card-bd">
                        <p style="font-size:.83rem;color:#9CA3AF;margin:0 0 16px;line-height:1.6">
                            Images appear in the notification tray and boost open rates.<br>
                            Recommended: <strong style="color:#374151">1200 × 628 px</strong> · max 2 MB · JPG/PNG/WEBP
                        </p>
                        <div class="dmbc-drop" id="dmDrop">
                            <input type="file" name="image" id="dmFile" accept="image/jpeg,image/png,image/webp" onchange="dmFileChange(this)">
                            <span class="dmbc-drop-ico">🖼️</span>
                            <p><strong>Click to upload</strong> or drag &amp; drop</p>
                            <p style="font-size:.76rem">JPG · PNG · WEBP &nbsp;·&nbsp; max 2 MB</p>
                        </div>
                        <div class="dmbc-prev-img" id="dmPrevWrap">
                            <img id="dmPrevImg" src="" alt="Preview">
                            <button type="button" class="dmbc-rm-btn" onclick="dmRmImg()" title="Remove">✕</button>
                        </div>
                        <div style="margin-top:16px">
                            <label style="display:block;font-size:.72rem;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px">Or paste an image URL</label>
                            <div class="dmbc-url-row">
                                <input type="url" name="image_url" id="dmImgUrl" placeholder="https://dreammulk.com/storage/notifications/…" oninput="dmUrlInput()">
                                <button type="button" class="dmbc-url-btn" onclick="dmPrevUrl()">Preview</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Settings --}}
                <div class="dmbc-card">
                    <div class="dmbc-card-hd"><h2>Settings</h2></div>
                    <div class="dmbc-card-bd">
                        <div class="dmbc-f">
                            <label>Recipients <span class="req">*</span></label>
                            <div class="dmbc-rc-grid">
                                <div class="dmbc-rc">
                                    <input type="radio" name="recipient_type" id="r-all" value="all" checked onchange="fetchCounts()">
                                    <label for="r-all"><span class="rci">🌍</span> Everyone</label>
                                </div>
                                <div class="dmbc-rc">
                                    <input type="radio" name="recipient_type" id="r-users" value="users" onchange="fetchCounts()">
                                    <label for="r-users"><span class="rci">👤</span> Users only</label>
                                </div>
                                <div class="dmbc-rc">
                                    <input type="radio" name="recipient_type" id="r-agents" value="agents" onchange="fetchCounts()">
                                    <label for="r-agents"><span class="rci">🏷️</span> Agents only</label>
                                </div>
                                <div class="dmbc-rc">
                                    <input type="radio" name="recipient_type" id="r-offices" value="offices" onchange="fetchCounts()">
                                    <label for="r-offices"><span class="rci">🏢</span> Offices only</label>
                                </div>
                            </div>
                        </div>
                        <div class="dmbc-f dmbc-2col">
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
                                <select name="priority" id="dmPriority" onchange="lp()">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">🚨 Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="dmbc-f">
                            <label>Action URL <span class="hint">(where to open on tap)</span></label>
                            <input type="text" name="action_url" placeholder="/properties  or  https://dreammulk.com/…">
                        </div>
                        <div class="dmbc-f">
                            <label>Action Button Text <span class="hint">(optional)</span></label>
                            <input type="text" name="action_text" maxlength="60" placeholder="e.g. View Properties">
                        </div>
                        <div class="dmbc-f">
                            <label>Expires At <span class="hint">(blank = never)</span></label>
                            <input type="datetime-local" name="expires_at">
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div>
                <div class="dmbc-panel">
                    <div class="dmbc-panel-hd">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <h2>Live Preview</h2>
                    </div>
                    <div style="padding:0 18px">
                        <div class="dmbc-phone">
                            <div class="dmbc-phone-scr">
                                <div class="dmbc-ph-bar"><span>9:41</span><span>●●● ▶ ☰</span></div>
                                <div class="dmbc-ph-ntf">Notifications · 3 new</div>
                                <div class="dmbc-nb">
                                    <div class="dmbc-nb-hd">
                                        <div class="dmbc-nb-ico"></div>
                                        <span class="dmbc-nb-app">Dream Mulk</span>
                                        <span class="dmbc-nb-time">now</span>
                                    </div>
                                    <div class="dmbc-nb-body">
                                        <img id="dmPrevNbImg" class="dmbc-nb-img" src="" alt="">
                                        <div class="dmbc-nb-title" id="dmPrevTitle">Your notification title</div>
                                        <div class="dmbc-nb-msg"   id="dmPrevMsg">Your notification message will appear here…</div>
                                        <span class="dmbc-nb-badge medium" id="dmPrevBadge">● Medium</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dmbc-stats">
                        <div class="dmbc-stat"><div class="dmbc-stat-n" id="sU">—</div><div class="dmbc-stat-l">Users</div></div>
                        <div class="dmbc-stat"><div class="dmbc-stat-n" id="sA">—</div><div class="dmbc-stat-l">Agents</div></div>
                        <div class="dmbc-stat"><div class="dmbc-stat-n" id="sO">—</div><div class="dmbc-stat-l">Offices</div></div>
                    </div>
                    <div class="dmbc-sub">
                        <button type="submit" form="bcForm" class="dmbc-btn" id="dmBtn">
                            <div class="dmbc-spin" id="dmSpin"></div>
                            <svg id="dmBtnIco" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span id="dmBtnLbl">Send Broadcast</span>
                        </button>
                        <p class="dmbc-sub-note">This action is immediate and irreversible.</p>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
</div>

<script>
document.querySelectorAll('.dmbc-ltab').forEach(t=>{
    t.addEventListener('click',()=>{
        const l=t.dataset.lang;
        document.querySelectorAll('.dmbc-ltab').forEach(x=>x.classList.remove('on'));
        document.querySelectorAll('.dmbc-lpane').forEach(x=>x.classList.remove('on'));
        t.classList.add('on');
        document.getElementById('lp-'+l).classList.add('on');
    });
});
function lp(){
    const t=document.getElementById('title_en').value||'Your notification title';
    const m=document.getElementById('message_en').value||'Your notification message will appear here…';
    const p=document.getElementById('dmPriority').value||'medium';
    document.getElementById('dmPrevTitle').textContent=t;
    document.getElementById('dmPrevMsg').textContent=m;
    const b=document.getElementById('dmPrevBadge');
    b.textContent={low:'● Low',medium:'● Medium',high:'● High',urgent:'🚨 Urgent'}[p];
    b.className='dmbc-nb-badge '+p;
}
function dmCC(el,max,id){
    const n=el.value.length,d=document.getElementById(id);
    d.textContent=n+' / '+max;
    d.className='dmbc-cc'+(n>=max?' over':n>max*.88?' warn':'');
}
function dmFileChange(i){
    const f=i.files[0];if(!f)return;
    if(f.size>2097152){alert('Image must be under 2 MB.');i.value='';return;}
    const r=new FileReader();
    r.onload=e=>{dmShow(e.target.result);document.getElementById('dmImgUrl').value='';};
    r.readAsDataURL(f);
}
function dmUrlInput(){if(document.getElementById('dmImgUrl').value.trim())document.getElementById('dmFile').value='';}
function dmPrevUrl(){const u=document.getElementById('dmImgUrl').value.trim();if(u)dmShow(u);}
function dmShow(s){
    document.getElementById('dmPrevImg').src=s;
    document.getElementById('dmPrevWrap').classList.add('show');
    const n=document.getElementById('dmPrevNbImg');n.src=s;n.classList.add('show');
}
function dmRmImg(){
    document.getElementById('dmFile').value='';
    document.getElementById('dmImgUrl').value='';
    document.getElementById('dmPrevWrap').classList.remove('show');
    const n=document.getElementById('dmPrevNbImg');n.src='';n.classList.remove('show');
}
const dz=document.getElementById('dmDrop');
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('over');});
dz.addEventListener('dragleave',()=>dz.classList.remove('over'));
dz.addEventListener('drop',e=>{
    e.preventDefault();dz.classList.remove('over');
    const f=e.dataTransfer.files[0];
    if(f&&f.type.startsWith('image/')){
        const dt=new DataTransfer();dt.items.add(f);
        document.getElementById('dmFile').files=dt.files;
        dmFileChange(document.getElementById('dmFile'));
    }
});
let _cT;
function fetchCounts(){
    clearTimeout(_cT);
    _cT=setTimeout(async()=>{
        const rt=document.querySelector('input[name=recipient_type]:checked')?.value||'all';
        try{
            const r=await fetch(`{{ route('admin.notifications.broadcast') }}?_counts=1&recipient_type=${rt}`,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            if(!r.ok)return;
            const j=await r.json();
            if(j.counts){
                document.getElementById('sU').textContent=j.counts.users??'—';
                document.getElementById('sA').textContent=j.counts.agents??'—';
                document.getElementById('sO').textContent=j.counts.offices??'—';
            }
        }catch(_){}
    },400);
}
fetchCounts();
document.getElementById('bcForm').addEventListener('submit',async function(e){
    e.preventDefault();
    if(!document.getElementById('title_en').value.trim()||!document.getElementById('message_en').value.trim()){showRes('err','✕ English title and message are required.');return;}
    setLoad(true);
    const fd=new FormData(this);
    const uv=document.getElementById('dmImgUrl').value.trim();
    if(!document.getElementById('dmFile').files.length&&uv)fd.set('image_url',uv);
    try{
        const res=await fetch('{{ route("admin.notifications.broadcast") }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},body:fd});
        const j=await res.json();
        if(res.ok&&(j.status||j.success)){
            const d=j.data??{};
            showRes('ok',`✓ Broadcast sent to <strong>${d.sent_to??'?'}</strong> recipients — ${d.users??0} users, ${d.agents??0} agents, ${d.offices??0} offices.`);
            if(d.warnings?.length)showWarns(d.warnings);
            this.reset();dmRmImg();lp();
        }else{
            const errs=j.errors?'<br>'+Object.values(j.errors).flat().join('<br>'):'';
            showRes('err','✕ '+(j.message??'Failed to send.')+errs);
        }
    }catch(err){showRes('err','✕ Network error: '+err.message);}
    finally{setLoad(false);}
});
function setLoad(on){
    document.getElementById('dmBtn').disabled=on;
    document.getElementById('dmBtnIco').style.display=on?'none':'';
    document.getElementById('dmSpin').classList.toggle('on',on);
    document.getElementById('dmBtnLbl').textContent=on?'Sending…':'Send Broadcast';
}
function showRes(type,html){
    const el=document.getElementById('bcRes');
    el.className='dmbc-result show '+type;
    document.getElementById('bcResMsg').innerHTML=html;
    document.getElementById('bcWarn').classList.remove('show');
    el.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function showWarns(w){
    const box=document.getElementById('bcWarn');
    document.getElementById('bcWarnList').innerHTML=w.map(x=>`<li>${x}</li>`).join('');
    box.classList.add('show');
}
lp();
</script>
@endsection

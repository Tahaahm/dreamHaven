@extends('layouts.agent-layout')
@section('title', 'Add Property — Dream Mulk')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Epilogue:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
:root {
    --P: #434eaa;
    --PD: #343e8a;
    --PL: #eef0fb;
    --PLL: #f6f7fd;
    --ink: #111827;
    --sub: #6b7280;
    --brd: #e5e7eb;
    --bg: #f9fafb;
    --white: #ffffff;
    --green: #10b981;
    --red: #ef4444;
    --amber: #f59e0b;
    --purple: #8b5cf6;
    --purple-light: #f5f3ff;
    --radius: 16px;
    --shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
    --shadow-lg: 0 8px 32px rgba(67,78,170,.18);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Epilogue', 'IBM Plex Sans Arabic', sans-serif;
    background: var(--bg);
    color: var(--ink);
}

/* TOP HEADER */
.apf-header {
    background: var(--white);
    border-bottom: 1px solid var(--brd);
    padding: 0 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 68px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.apf-logo { font-size: 20px; font-weight: 800; color: var(--P); display: flex; align-items: center; gap: 10px; }
.apf-logo span { color: var(--ink); }
.apf-back {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 600; color: var(--sub);
    text-decoration: none; padding: 8px 16px;
    border-radius: 10px; border: 1px solid var(--brd);
    background: var(--white); transition: all .2s;
}
.apf-back:hover { background: var(--bg); color: var(--ink); }

/* WIZARD STEPS BAR */
.wizard-bar {
    background: var(--white); border-bottom: 1px solid var(--brd);
    padding: 0 32px; display: flex; align-items: center;
    gap: 0; overflow-x: auto; scrollbar-width: none;
}
.wizard-bar::-webkit-scrollbar { display: none; }
.wstep {
    display: flex; align-items: center; gap: 12px;
    padding: 18px 20px; cursor: pointer;
    border-bottom: 3px solid transparent; transition: all .25s;
    white-space: nowrap; flex-shrink: 0;
}
.wstep.active { border-bottom-color: var(--P); }
.wstep.done .wnum { background: var(--green); border-color: var(--green); color: white; }
.wstep.active .wnum { background: var(--P); border-color: var(--P); color: white; }
.wstep.active .wlabel { color: var(--P); font-weight: 700; }
.wstep.done .wlabel { color: var(--green); font-weight: 600; }
.wstep .wlabel { color: var(--sub); font-size: 13.5px; font-weight: 500; }
.wnum {
    width: 32px; height: 32px; border-radius: 50%;
    border: 2px solid var(--brd);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: var(--sub);
    background: var(--white); transition: all .25s; flex-shrink: 0;
}
.wdivider { width: 32px; height: 1px; background: var(--brd); flex-shrink: 0; margin: 0 4px; }

/* MAIN LAYOUT */
.apf-body { max-width: 1100px; margin: 0 auto; padding: 40px 24px 120px; }

/* STEP PANELS */
.step-panel { display: none; animation: fadeUp .35s ease; }
.step-panel.active { display: block; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.step-header { margin-bottom: 32px; }
.step-tag {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: var(--P); background: var(--PL);
    padding: 5px 14px; border-radius: 50px; margin-bottom: 12px;
}
.step-title { font-size: 28px; font-weight: 800; color: var(--ink); line-height: 1.2; margin-bottom: 6px; }
.step-sub   { font-size: 15px; color: var(--sub); font-weight: 400; }

/* CARDS */
.card {
    background: var(--white); border: 1px solid var(--brd);
    border-radius: var(--radius); padding: 32px; margin-bottom: 20px;
    box-shadow: var(--shadow);
}
.card-title { font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.card-title i {
    width: 34px; height: 34px; background: var(--PL);
    border-radius: 10px; display: flex; align-items: center;
    justify-content: center; color: var(--P); font-size: 14px;
}

/* LANGUAGE PILL SWITCHER */
.lang-switch { display: inline-flex; background: var(--bg); border: 1px solid var(--brd); border-radius: 12px; padding: 4px; gap: 2px; margin-bottom: 20px; }
.lpill {
    padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .2s; color: var(--sub); border: none;
    background: transparent; display: flex; align-items: center; gap: 6px;
}
.lpill.active { background: var(--P); color: white; box-shadow: 0 2px 8px rgba(67,78,170,.3); }
.lang-pane { display: none; }
.lang-pane.active { display: block; }

/* FORM ELEMENTS */
.frow { display: grid; gap: 18px; margin-bottom: 18px; }
.frow-2 { grid-template-columns: 1fr 1fr; }
.frow-3 { grid-template-columns: 1fr 1fr 1fr; }
.frow-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
@media(max-width: 700px) { .frow-2, .frow-3, .frow-4 { grid-template-columns: 1fr; } }

.fgroup { display: flex; flex-direction: column; gap: 7px; }
.flabel { font-size: 13px; font-weight: 600; color: var(--ink); display: flex; align-items: center; gap: 5px; }
.flabel .req  { color: var(--red); }
.flabel .hint { color: var(--sub); font-weight: 400; font-size: 12px; }

.finput, .fselect, .ftextarea {
    width: 100%; padding: 12px 16px; border: 1.5px solid var(--brd);
    border-radius: 12px; font-size: 14.5px; font-family: inherit;
    color: var(--ink); background: var(--white); transition: all .2s;
    outline: none; appearance: none; -webkit-appearance: none;
}
.finput:focus, .fselect:focus, .ftextarea:focus { border-color: var(--P); box-shadow: 0 0 0 3px rgba(67,78,170,.1); }
.finput::placeholder, .ftextarea::placeholder { color: #bcc0cc; }
.fselect {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px;
}
.ftextarea { min-height: 100px; resize: vertical; line-height: 1.6; }

.finput-prefix {
    display: flex; align-items: center; border: 1.5px solid var(--brd);
    border-radius: 12px; overflow: hidden; transition: all .2s; background: var(--white);
}
.finput-prefix:focus-within { border-color: var(--P); box-shadow: 0 0 0 3px rgba(67,78,170,.1); }
.prefix-tag {
    padding: 0 14px; background: var(--bg); border-right: 1.5px solid var(--brd);
    color: var(--sub); font-size: 13px; font-weight: 700; height: 100%;
    display: flex; align-items: center; white-space: nowrap; min-height: 48px;
}
.finput-prefix input {
    flex: 1; border: none; outline: none; padding: 12px 16px;
    font-size: 14.5px; font-family: inherit; background: transparent; color: var(--ink); min-width: 0;
}

/* TYPE SELECTOR CARDS */
.type-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media(max-width: 600px) { .type-grid { grid-template-columns: repeat(2, 1fr); } }
.tcard {
    border: 2px solid var(--brd); border-radius: 14px; padding: 20px 16px;
    text-align: center; cursor: pointer; transition: all .2s; background: var(--white); position: relative;
}
.tcard:hover    { border-color: var(--P); background: var(--PLL); }
.tcard.selected { border-color: var(--P); background: var(--PL); box-shadow: 0 0 0 3px rgba(67,78,170,.12); }
.tcard input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.tcard-icon  { font-size: 28px; margin-bottom: 8px; }
.tcard-label { font-size: 13px; font-weight: 600; color: var(--ink); }
.tcard.selected .tcard-label { color: var(--P); }
.tcard-check {
    position: absolute; top: 10px; right: 10px;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--P); color: white; font-size: 10px;
    display: none; align-items: center; justify-content: center;
}
.tcard.selected .tcard-check { display: flex; }

/* TOGGLE CHECKS */
.toggle-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
@media(max-width: 500px) { .toggle-group { grid-template-columns: 1fr; } }
.toggle-item {
    display: flex; align-items: center; gap: 12px; padding: 14px 16px;
    border: 1.5px solid var(--brd); border-radius: 12px; cursor: pointer;
    transition: all .2s; user-select: none; background: var(--white);
}
.toggle-item:hover   { border-color: var(--P); background: var(--PLL); }
.toggle-item.checked { border-color: var(--P); background: var(--PL); }
.toggle-item input   { display: none; }
.toggle-box {
    width: 22px; height: 22px; border: 2px solid var(--brd); border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s; flex-shrink: 0; background: var(--white);
}
.toggle-item.checked .toggle-box { background: var(--P); border-color: var(--P); color: white; }
.toggle-icon  { font-size: 18px; }
.toggle-label { font-size: 14px; font-weight: 600; color: var(--ink); }

/* MAP TOGGLE ROW */
.map-toggle-row {
    display: flex; align-items: center; gap: 12px; margin-bottom: 18px;
    padding: 14px 18px; background: var(--PLL); border: 1.5px solid var(--PL);
    border-radius: 12px; cursor: pointer; user-select: none;
}
.map-toggle-row input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--P); cursor: pointer; flex-shrink: 0; }
.map-toggle-label { font-size: 14px; font-weight: 600; color: var(--ink); display: flex; align-items: center; gap: 8px; }
.map-toggle-label i { color: var(--P); }
.map-wrapper { transition: all .3s ease; }
.map-wrapper.hidden { display: none; }

/* MAP SEARCH */
.map-search-bar { display: flex; gap: 10px; margin-bottom: 12px; }
.map-search-bar input {
    flex: 1; padding: 12px 16px; border: 1.5px solid var(--brd);
    border-radius: 12px; font-size: 14px; font-family: inherit; outline: none; transition: all .2s;
}
.map-search-bar input:focus { border-color: var(--P); box-shadow: 0 0 0 3px rgba(67,78,170,.1); }
.map-search-bar button {
    padding: 12px 20px; background: var(--P); color: white; border: none;
    border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .2s; white-space: nowrap;
}
.map-search-bar button:hover { background: var(--PD); }
.map-frame { width: 100%; height: 360px; border-radius: 14px; overflow: hidden; border: 1.5px solid var(--brd); }
#leaflet-map { width: 100%; height: 100%; }
.map-hint { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--sub); margin-top: 10px; padding: 10px 14px; background: var(--PLL); border-radius: 10px; }
.map-hint i { color: var(--P); }
.coord-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; }

/* VIDEO UPLOAD (AI) */
.video-section {
    border: 2px dashed var(--purple); border-radius: var(--radius);
    padding: 28px; background: var(--purple-light); margin-bottom: 4px;
}
.video-section-header { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
.video-ai-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--purple), #a78bfa);
    border-radius: 14px; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 22px; flex-shrink: 0;
}
.video-ai-title    { font-size: 17px; font-weight: 800; color: var(--ink); }
.video-ai-subtitle { font-size: 13px; color: var(--sub); margin-top: 2px; }
.video-drop-area {
    border: 2.5px dashed #d1d5db; border-radius: 14px; padding: 36px 20px;
    text-align: center; background: var(--white); cursor: pointer; transition: all .3s ease;
}
.video-drop-area:hover { border-color: var(--purple); background: var(--purple-light); }
.video-drop-area.processing { border-color: var(--purple); pointer-events: none; }
.video-drop-icon { font-size: 44px; color: var(--purple); margin-bottom: 10px; }
.video-drop-text { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 5px; }
.video-drop-hint { font-size: 13px; color: var(--sub); }
.video-status { display: none; margin-top: 16px; background: var(--white); border-radius: 12px; padding: 18px; }
.video-status.show { display: block; }
.vs-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.vs-spinner {
    width: 36px; height: 36px; border: 4px solid #f3f4f6;
    border-top-color: var(--purple); border-radius: 50%;
    animation: spin .9s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
.vs-title    { font-size: 14px; font-weight: 700; color: var(--ink); }
.vs-subtitle { font-size: 12px; color: var(--sub); margin-top: 2px; }
.vs-bar-wrap { width: 100%; height: 8px; background: #f3f4f6; border-radius: 4px; overflow: hidden; }
.vs-bar { height: 100%; background: linear-gradient(90deg, var(--purple), #a78bfa); border-radius: 4px; transition: width .4s ease; width: 0%; }

/* IMAGE UPLOAD */
.img-dropzone {
    border: 2.5px dashed var(--brd); border-radius: var(--radius);
    padding: 48px 24px; text-align: center; cursor: pointer; transition: all .25s; background: var(--bg);
}
.img-dropzone:hover, .img-dropzone.dragover { border-color: var(--P); background: var(--PLL); }
.dz-icon {
    width: 64px; height: 64px; background: var(--PL); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 26px; color: var(--P);
}
.dz-title { font-size: 17px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
.dz-sub   { font-size: 13px; color: var(--sub); }
.dz-formats { display: flex; gap: 8px; justify-content: center; margin-top: 14px; flex-wrap: wrap; }
.fmt-tag {
    padding: 4px 12px; border-radius: 50px; background: var(--white); border: 1px solid var(--brd);
    font-size: 11px; font-weight: 700; color: var(--sub); text-transform: uppercase; letter-spacing: 1px;
}
.img-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 14px; margin-top: 20px; }
.img-thumb {
    position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden;
    border: 2px solid var(--brd); background: var(--bg); cursor: grab; transition: all .2s;
}
.img-thumb:first-child { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
.img-thumb img { width: 100%; height: 100%; object-fit: cover; pointer-events: none; display: block; }
.img-thumb-del {
    position: absolute; top: 7px; right: 7px; width: 28px; height: 28px;
    background: rgba(239,68,68,.9); border: none; border-radius: 7px;
    color: white; font-size: 12px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .15s; z-index: 5;
}
.img-thumb:hover .img-thumb-del { opacity: 1; }
.img-thumb-badge {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(245,158,11,.92); color: white; font-size: 9px;
    font-weight: 800; text-align: center; padding: 5px; letter-spacing: 2px; text-transform: uppercase;
}
.img-count-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; background: var(--PLL); border-radius: 10px; margin-top: 14px; font-size: 13px;
}
.img-count-bar strong { color: var(--P); }

/* REVIEW SUMMARY */
.review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media(max-width: 600px) { .review-grid { grid-template-columns: 1fr; } }
.rv-item { background: var(--bg); border: 1px solid var(--brd); border-radius: 12px; padding: 16px; }
.rv-label { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--sub); margin-bottom: 5px; }
.rv-value { font-size: 15px; font-weight: 700; color: var(--ink); }
.review-images { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.review-img-thumb { width: 72px; height: 72px; border-radius: 10px; object-fit: cover; border: 2px solid var(--brd); }

/* NAV BUTTONS */
.step-nav { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-top: 1px solid var(--brd); margin-top: 24px; }
.btn-prev {
    display: inline-flex; align-items: center; gap: 8px; padding: 13px 26px;
    border: 1.5px solid var(--brd); border-radius: 12px; background: var(--white);
    color: var(--sub); font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; font-family: inherit;
}
.btn-prev:hover { background: var(--bg); color: var(--ink); }
.btn-next {
    display: inline-flex; align-items: center; gap: 8px; padding: 13px 30px;
    border: none; border-radius: 12px; background: var(--P); color: white;
    font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; font-family: inherit;
    box-shadow: 0 4px 14px rgba(67,78,170,.3);
}
.btn-next:hover { background: var(--PD); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(67,78,170,.38); }
.btn-submit { background: var(--green); box-shadow: 0 4px 14px rgba(16,185,129,.3); }
.btn-submit:hover { background: #059669; box-shadow: 0 6px 18px rgba(16,185,129,.38); }

/* UPLOAD PROGRESS DIALOG */
.upload-overlay {
    position: fixed; inset: 0; background: rgba(6,9,30,.55); backdrop-filter: blur(6px);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden; transition: all .3s;
}
.upload-overlay.show { opacity: 1; visibility: visible; }
.upload-dialog {
    background: var(--white); border-radius: 24px; padding: 48px 40px;
    width: 460px; max-width: 90vw; text-align: center;
    box-shadow: 0 24px 80px rgba(0,0,0,.22);
    transform: scale(.92) translateY(16px); transition: all .35s cubic-bezier(.16,1,.3,1);
}
.upload-overlay.show .upload-dialog { transform: scale(1) translateY(0); }
.ud-anim { width: 80px; height: 80px; margin: 0 auto 24px; position: relative; }
.ud-ring { width: 80px; height: 80px; border-radius: 50%; border: 4px solid var(--PL); border-top-color: var(--P); animation: spin .9s linear infinite; position: absolute; inset: 0; }
.ud-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 28px; color: var(--P); }
.ud-title { font-size: 21px; font-weight: 800; color: var(--ink); margin-bottom: 8px; }
.ud-sub   { font-size: 14px; color: var(--sub); margin-bottom: 28px; line-height: 1.6; }
.ud-progress { background: var(--bg); border-radius: 50px; height: 8px; overflow: hidden; margin-bottom: 12px; }
.ud-bar { height: 100%; background: linear-gradient(90deg, var(--P), #6c77cc); border-radius: 50px; transition: width .4s ease; width: 0%; }
.ud-pct { font-size: 22px; font-weight: 800; color: var(--P); margin-bottom: 6px; }
.ud-steps { display: flex; flex-direction: column; gap: 8px; margin-top: 22px; text-align: left; }
.ud-step { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: var(--sub); padding: 10px 14px; border-radius: 10px; background: var(--bg); transition: all .3s; }
.ud-step.active { color: var(--P); background: var(--PL); font-weight: 600; }
.ud-step.done   { color: var(--green); background: rgba(16,185,129,.07); }
.ud-step i { width: 18px; text-align: center; flex-shrink: 0; }
.ud-success { display: none; }
.ud-success.show { display: block; }
.success-circle { width: 80px; height: 80px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 36px; color: white; animation: popIn .4s cubic-bezier(.16,1,.3,1); }
@keyframes popIn { from { transform: scale(.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }

/* LISTING TYPE SWITCHER */
.list-type-sw { display: flex; background: var(--bg); border: 1.5px solid var(--brd); border-radius: 12px; padding: 4px; gap: 4px; width: fit-content; }
.lt-btn { padding: 10px 28px; border-radius: 9px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: transparent; color: var(--sub); transition: all .2s; font-family: inherit; }
.lt-btn.active { background: var(--P); color: white; box-shadow: 0 2px 8px rgba(67,78,170,.3); }

/* RTL SUPPORT */
[dir="rtl"] .fselect { background-position: left 14px center; padding-right: 16px; padding-left: 40px; }
[dir="rtl"] .prefix-tag { border-right: none; border-left: 1.5px solid var(--brd); }
</style>
@endsection

@section('content')

{{-- UPLOAD PROGRESS DIALOG --}}
<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-dialog">
        <div id="udLoading">
            <div class="ud-anim">
                <div class="ud-ring"></div>
                <div class="ud-icon"><i class="fas fa-home"></i></div>
            </div>
            <div class="ud-pct" id="udPct">0%</div>
            <div class="ud-title">Publishing Your Property</div>
            <div class="ud-sub" id="udSub">Please wait while we upload your property details and images...</div>
            <div class="ud-progress"><div class="ud-bar" id="udBar"></div></div>
            <div class="ud-steps">
                <div class="ud-step active" id="udStep1"><i class="fas fa-info-circle"></i> Saving property details</div>
                <div class="ud-step"         id="udStep2"><i class="fas fa-images"></i> Uploading images</div>
                <div class="ud-step"         id="udStep3"><i class="fas fa-map-marker-alt"></i> Setting location</div>
                <div class="ud-step"         id="udStep4"><i class="fas fa-check-circle"></i> Finalizing listing</div>
            </div>
        </div>
        <div class="ud-success" id="udSuccess">
            <div class="success-circle"><i class="fas fa-check"></i></div>
            <div class="ud-title" style="color: var(--green)">Property Listed!</div>
            <div class="ud-sub">Your property has been successfully published and is now live on Dream Mulk.</div>
            <a href="{{ route('agent.properties') }}" class="btn-next btn-submit" style="margin-top:20px;display:inline-flex;text-decoration:none;">
                <i class="fas fa-list"></i> View My Listings
            </a>
        </div>
    </div>
</div>

{{-- HEADER --}}
<div class="apf-header">
    <div class="apf-logo"><i class="fas fa-building" style="color:var(--P)"></i> Dream <span>Mulk</span></div>
    <a href="{{ route('agent.properties') }}" class="apf-back"><i class="fas fa-arrow-left"></i> My Properties</a>
</div>

{{-- WIZARD BAR --}}
<div class="wizard-bar" id="wizardBar">
    <div class="wstep active" data-step="1" onclick="goToStep(1)"><div class="wnum">1</div><span class="wlabel">Basic Info</span></div>
    <div class="wdivider"></div>
    <div class="wstep" data-step="2" onclick="goToStep(2)"><div class="wnum">2</div><span class="wlabel">Location</span></div>
    <div class="wdivider"></div>
    <div class="wstep" data-step="3" onclick="goToStep(3)"><div class="wnum">3</div><span class="wlabel">Details</span></div>
    <div class="wdivider"></div>
    <div class="wstep" data-step="4" onclick="goToStep(4)"><div class="wnum">4</div><span class="wlabel">Photos</span></div>
    <div class="wdivider"></div>
    <div class="wstep" data-step="5" onclick="goToStep(5)"><div class="wnum">5</div><span class="wlabel">Review</span></div>
</div>

{{-- FORM --}}
<form action="{{ route('agent.property.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm">
@csrf

<div class="apf-body">

{{-- ════ STEP 1 — BASIC INFO ════ --}}
<div class="step-panel active" id="step1">
    <div class="step-header">
        <div class="step-tag"><i class="fas fa-pencil"></i> Step 1 of 5</div>
        <div class="step-title">What are you listing?</div>
        <div class="step-sub">Start with the basics — type, listing purpose and price</div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-tag"></i> Listing Purpose</div>
        <div class="list-type-sw">
            <button type="button" class="lt-btn active" onclick="setListType(this,'sell')">🏷️ For Sale</button>
            <button type="button" class="lt-btn" onclick="setListType(this,'rent')">🔑 For Rent</button>
        </div>
        <input type="hidden" name="listing_type" id="listing_type" value="sell">
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-home"></i> Property Type <span style="color:var(--red);margin-left:4px">*</span></div>
        <div class="type-grid">
            @php
                $types = [
                    ['val'=>'apartment','icon'=>'🏢','label'=>'Apartment'],
                    ['val'=>'villa',    'icon'=>'🏰','label'=>'Villa'],
                    ['val'=>'house',    'icon'=>'🏠','label'=>'House'],
                    ['val'=>'land',     'icon'=>'🌍','label'=>'Land'],
                    ['val'=>'commercial','icon'=>'🏪','label'=>'Commercial'],
                    ['val'=>'office',   'icon'=>'💼','label'=>'Office'],
                ];
            @endphp
            @foreach($types as $t)
            <label class="tcard" onclick="selectType(this)">
                <input type="radio" name="property_type" value="{{ $t['val'] }}" required>
                <div class="tcard-check"><i class="fas fa-check"></i></div>
                <div class="tcard-icon">{{ $t['icon'] }}</div>
                <div class="tcard-label">{{ $t['label'] }}</div>
            </label>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-align-left"></i> Property Name & Description</div>
        <div class="lang-switch">
            <button type="button" class="lpill active" onclick="switchLang(this,'en')">🇬🇧 English</button>
            <button type="button" class="lpill" onclick="switchLang(this,'ar')">🇮🇶 عربی</button>
            <button type="button" class="lpill" onclick="switchLang(this,'ku')">🏔️ کوردی</button>
        </div>
        <div class="lang-pane active" id="lpane-en">
            <div class="frow" style="margin-bottom:14px">
                <div class="fgroup">
                    <label class="flabel">Title in English <span class="req">*</span></label>
                    <input type="text" name="title_en" class="finput" placeholder="e.g. Luxury Villa in Erbil">
                </div>
            </div>
            <div class="fgroup">
                <label class="flabel">Description in English</label>
                <textarea name="description_en" class="ftextarea" placeholder="Describe the property — location highlights, features, condition..."></textarea>
            </div>
        </div>
        <div class="lang-pane" id="lpane-ar" dir="rtl">
            <div class="frow" style="margin-bottom:14px">
                <div class="fgroup">
                    <label class="flabel">العنوان بالعربية</label>
                    <input type="text" name="title_ar" class="finput" placeholder="مثال: فيلا فاخرة في أربيل">
                </div>
            </div>
            <div class="fgroup">
                <label class="flabel">الوصف بالعربية</label>
                <textarea name="description_ar" class="ftextarea" placeholder="صِف العقار بالتفصيل..."></textarea>
            </div>
        </div>
        <div class="lang-pane" id="lpane-ku" dir="rtl">
            <div class="frow" style="margin-bottom:14px">
                <div class="fgroup">
                    <label class="flabel">ناونیشان بە کوردی</label>
                    <input type="text" name="title_ku" class="finput" placeholder="نموونە: ڤیلای بەرز لە هەولێر">
                </div>
            </div>
            <div class="fgroup">
                <label class="flabel">وەسف بە کوردی</label>
                <textarea name="description_ku" class="ftextarea" placeholder="خانووبەرەکەت بە وردی باس بکە..."></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-dollar-sign"></i> Price</div>
        <div class="frow frow-2">
            <div class="fgroup">
                <label class="flabel">Price in USD <span class="req">*</span></label>
                <div class="finput-prefix">
                    <span class="prefix-tag">$</span>
                    <input type="text" name="price_usd" id="price_usd" placeholder="e.g. 150,000" required>
                </div>
            </div>
            <div class="fgroup">
                <label class="flabel">Price in IQD <span class="hint">(optional)</span></label>
                <div class="finput-prefix">
                    <span class="prefix-tag">IQD</span>
                    <input type="text" name="price" id="price_iqd" placeholder="e.g. 200,000,000">
                </div>
            </div>
        </div>
        <div class="frow frow-2" style="margin-top:6px">
            <div class="fgroup">
                <label class="flabel">Area (m²) <span class="req">*</span></label>
                <input type="number" name="area" class="finput" placeholder="e.g. 250" required>
            </div>
            <div class="fgroup">
                <label class="flabel">Status</label>
                <select name="status" class="fselect">
                    <option value="available">✅ Available</option>
                    <option value="sold">❌ Sold</option>
                    <option value="rented">🔑 Rented</option>
                    <option value="pending">⏳ Pending</option>
                </select>
            </div>
        </div>
    </div>

    <div class="step-nav">
        <a href="{{ route('agent.properties') }}" class="btn-prev"><i class="fas fa-times"></i> Cancel</a>
        <button type="button" class="btn-next" onclick="nextStep(1)">Location <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

{{-- ════ STEP 2 — LOCATION ════ --}}
<div class="step-panel" id="step2">
    <div class="step-header">
        <div class="step-tag"><i class="fas fa-map-marker-alt"></i> Step 2 of 5</div>
        <div class="step-title">Where is the property?</div>
        <div class="step-sub">Select city, area and pin the exact location on the map</div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-city"></i> City & Area</div>
        <div class="frow frow-2">
            <div class="fgroup">
                <label class="flabel">City <span class="req">*</span></label>
                <select id="location-city-select" class="fselect" required>
                    <option value="">Loading cities...</option>
                </select>
            </div>
            <div class="fgroup">
                <label class="flabel">District / Area <span class="req">*</span></label>
                <select id="location-area-select" class="fselect" disabled required>
                    <option value="">Select City First</option>
                </select>
            </div>
        </div>
        <div class="frow" style="margin-top:6px">
            <div class="fgroup">
                <label class="flabel">Street / Full Address <span class="req">*</span></label>
                <input type="text" name="address" class="finput" placeholder="Building name, street, floor, nearest landmark..." required>
            </div>
        </div>
        <input type="hidden" name="city_en"     id="city_en">
        <input type="hidden" name="city_ar"     id="city_ar">
        <input type="hidden" name="city_ku"     id="city_ku">
        <input type="hidden" name="district_en" id="district_en">
        <input type="hidden" name="district_ar" id="district_ar">
        <input type="hidden" name="district_ku" id="district_ku">
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-map-marked-alt"></i> Pin on Map</div>

        {{-- Map toggle --}}
        <label class="map-toggle-row" for="has_map_toggle">
            <input type="checkbox" name="has_map" id="has_map_toggle" value="1" checked>
            <span class="map-toggle-label">
                <i class="fas fa-map-marked-alt"></i>
                Enable map pin (recommended — uncheck to skip)
            </span>
        </label>

        <div id="map_content_wrapper" class="map-wrapper">
            <div class="map-search-bar">
                <input type="text" id="mapSearchInput" placeholder="Search address or place name...">
                <button type="button" onclick="searchMapAddress()"><i class="fas fa-search"></i> Search</button>
            </div>
            <div class="map-frame"><div id="leaflet-map"></div></div>
            <div class="map-hint"><i class="fas fa-info-circle"></i> Click anywhere on the map or drag the pin to set the exact location.</div>
            <div class="coord-row">
                <div class="fgroup">
                    <label class="flabel">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="finput" readonly placeholder="Auto from map">
                </div>
                <div class="fgroup">
                    <label class="flabel">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="finput" readonly placeholder="Auto from map">
                </div>
            </div>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-next" onclick="nextStep(2)">Property Details <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

{{-- ════ STEP 3 — DETAILS ════ --}}
<div class="step-panel" id="step3">
    <div class="step-header">
        <div class="step-tag"><i class="fas fa-list-check"></i> Step 3 of 5</div>
        <div class="step-title">Property Details</div>
        <div class="step-sub">Rooms, features and amenities</div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-door-open"></i> Rooms</div>
        <div class="frow frow-4">
            <div class="fgroup"><label class="flabel">🛏 Bedrooms</label><input type="number" name="bedrooms" class="finput" placeholder="0" min="0"></div>
            <div class="fgroup"><label class="flabel">🚿 Bathrooms</label><input type="number" name="bathrooms" class="finput" placeholder="0" min="0"></div>
            <div class="fgroup"><label class="flabel">🏗 Floors</label><input type="number" name="floor_number" class="finput" placeholder="1" min="1"></div>
            <div class="fgroup"><label class="flabel">📅 Year Built</label><input type="number" name="year_built" class="finput" placeholder="{{ date('Y') }}" min="1900" max="{{ date('Y') + 5 }}"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-sparkles"></i> Features & Amenities</div>
        <div class="toggle-group">
            @php
                $toggles = [
                    ['name'=>'furnished',   'icon'=>'🛋',  'label'=>'Furnished'],
                    ['name'=>'electricity', 'icon'=>'⚡',  'label'=>'Electricity 24/7', 'checked'=>true],
                    ['name'=>'water',       'icon'=>'💧',  'label'=>'Water System',      'checked'=>true],
                    ['name'=>'internet',    'icon'=>'🌐',  'label'=>'Internet / Fiber',   'checked'=>true],
                    ['name'=>'parking',     'icon'=>'🅿️',  'label'=>'Parking'],
                    ['name'=>'security',    'icon'=>'🔒',  'label'=>'Security'],
                    ['name'=>'elevator',    'icon'=>'🛗',  'label'=>'Elevator'],
                    ['name'=>'generator',   'icon'=>'🔋',  'label'=>'Generator'],
                    ['name'=>'garden',      'icon'=>'🌳',  'label'=>'Garden'],
                    ['name'=>'pool',        'icon'=>'🏊',  'label'=>'Swimming Pool'],
                ];
            @endphp
            @foreach($toggles as $tog)
            <label class="toggle-item {{ isset($tog['checked']) ? 'checked' : '' }}" onclick="toggleCheck(this)">
                <input type="checkbox" name="{{ $tog['name'] }}" value="1" {{ isset($tog['checked']) ? 'checked' : '' }}>
                <div class="toggle-box">@if(isset($tog['checked']))<i class="fas fa-check" style="font-size:11px"></i>@endif</div>
                <span class="toggle-icon">{{ $tog['icon'] }}</span>
                <span class="toggle-label">{{ $tog['label'] }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-prev" onclick="prevStep(3)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-next" onclick="nextStep(3)">Add Photos <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

{{-- ════ STEP 4 — PHOTOS ════ --}}
<div class="step-panel" id="step4">
    <div class="step-header">
        <div class="step-tag"><i class="fas fa-camera"></i> Step 4 of 5</div>
        <div class="step-title">Add Photos</div>
        <div class="step-sub">Upload photos manually or let AI extract the best frames from a video tour.</div>
    </div>

    {{-- AI VIDEO FRAME EXTRACTION --}}
    <div class="card">
        <div class="card-title">
            <i class="fas fa-video" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:white;"></i>
            AI Video Frame Extraction
            <span style="font-size:12px;font-weight:400;color:var(--sub);margin-left:6px">(optional)</span>
        </div>

        <div class="video-section">
            <div class="video-section-header">
                <div class="video-ai-icon"><i class="fas fa-robot"></i></div>
                <div>
                    <div class="video-ai-title">Upload Property Tour Video</div>
                    <div class="video-ai-subtitle">AI will automatically extract the 10 best-quality frames and add them to your gallery below</div>
                </div>
            </div>

            <div class="video-drop-area" id="videoDropArea" onclick="document.getElementById('videoInput').click()">
                <div class="video-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="video-drop-text">Click to Upload Property Video</div>
                <div class="video-drop-hint">MP4, MOV, AVI — Max 500 MB &nbsp;•&nbsp; AI selects 10 best frames automatically</div>
                <input type="file" id="videoInput" accept="video/mp4,video/quicktime,video/x-msvideo" hidden onchange="handleVideoUpload(event)">
            </div>

            <div class="video-status" id="videoStatus">
                <div class="vs-row">
                    <div class="vs-spinner"></div>
                    <div>
                        <div class="vs-title"    id="vsTitle">Uploading video...</div>
                        <div class="vs-subtitle" id="vsSubtitle">Please wait</div>
                    </div>
                </div>
                <div class="vs-bar-wrap"><div class="vs-bar" id="vsBar"></div></div>
            </div>
        </div>
    </div>

    {{-- MANUAL IMAGE UPLOAD --}}
    <div class="card">
        <div class="card-title"><i class="fas fa-images"></i> Property Photos <span class="req">*</span></div>

        <div class="img-dropzone" id="imgDropzone" onclick="document.getElementById('imageInput').click()">
            <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="dz-title">Click to upload or drag & drop</div>
            <div class="dz-sub">Upload multiple photos at once. Drag to reorder. First photo = Cover image.</div>
            <div class="dz-formats">
                <span class="fmt-tag">JPG</span>
                <span class="fmt-tag">PNG</span>
                <span class="fmt-tag">WEBP</span>
                <span class="fmt-tag">Max 30MB each</span>
            </div>
            <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
        </div>

        <div class="img-preview-grid" id="imgGrid"></div>

        <div class="img-count-bar" id="imgCountBar" style="display:none">
            <span><strong id="imgCount">0</strong> photos selected</span>
            <span style="color:var(--sub);font-size:12px">First photo = Cover image</span>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-prev" onclick="prevStep(4)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-next" onclick="nextStep(4)">Review & Publish <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

{{-- ════ STEP 5 — REVIEW ════ --}}
<div class="step-panel" id="step5">
    <div class="step-header">
        <div class="step-tag"><i class="fas fa-check-double"></i> Step 5 of 5</div>
        <div class="step-title">Review & Publish</div>
        <div class="step-sub">Everything looks good? Submit your listing.</div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-eye"></i> Summary</div>
        <div class="review-grid" id="reviewGrid"></div>
        <div style="margin-top:16px">
            <div class="rv-label" style="margin-bottom:8px">PHOTOS</div>
            <div class="review-images" id="reviewImages"></div>
        </div>
    </div>

    <div class="card" style="border-color:rgba(67,78,170,.25);background:var(--PLL)">
        <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="font-size:24px;margin-top:2px">✅</div>
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--ink);margin-bottom:4px">Ready to publish</div>
                <div style="font-size:13.5px;color:var(--sub);line-height:1.6">Your property will be visible to buyers and renters on Dream Mulk immediately after submission. You can always edit or remove it from your dashboard.</div>
            </div>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-prev" onclick="prevStep(5)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="submit" class="btn-next btn-submit" id="submitBtn"><i class="fas fa-paper-plane"></i> Publish Property</button>
    </div>
</div>

</div>{{-- /apf-body --}}
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ═══════════════════════════════════════════
// WIZARD STATE
// ═══════════════════════════════════════════
let currentStep = 1;
const totalSteps = 5;
let leafletMap = null, leafletMarker = null;
let selectedFiles = [];
let dragSrc = null;

function goToStep(n) {
    if (n < 1 || n > totalSteps) return;
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    document.querySelectorAll('.wstep').forEach(s => {
        const sn = parseInt(s.dataset.step);
        s.classList.remove('active','done');
        if (sn === n) s.classList.add('active');
        else if (sn < n) s.classList.add('done');
        const wnum = s.querySelector('.wnum');
        if (sn < n) wnum.innerHTML = '<i class="fas fa-check" style="font-size:11px"></i>';
        else wnum.textContent = sn;
    });
    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (n === 2) initMap();
    if (n === 5) buildReview();
}

function nextStep(from) { if (!validateStep(from)) return; goToStep(from + 1); }
function prevStep(from) { goToStep(from - 1); }

// ═══════════════════════════════════════════
// VALIDATION
// ═══════════════════════════════════════════
function validateStep(step) {
    if (step === 1) {
        const type = document.querySelector('input[name="property_type"]:checked');
        if (!type) { alert('Please select a property type.'); return false; }

        const titleEn = document.querySelector('input[name="title_en"]').value.trim();
        const titleAr = document.querySelector('input[name="title_ar"]').value.trim();
        const titleKu = document.querySelector('input[name="title_ku"]').value.trim();
        if (!titleEn && !titleAr && !titleKu) { alert('Please enter a title in at least one language.'); return false; }
        if (!titleEn) document.querySelector('input[name="title_en"]').value = titleAr || titleKu;
        if (!titleAr) document.querySelector('input[name="title_ar"]').value = titleEn || titleKu;
        if (!titleKu) document.querySelector('input[name="title_ku"]').value = titleEn || titleAr;

        const priceUsd = document.querySelector('input[name="price_usd"]').value.trim();
        if (!priceUsd) { alert('Please enter the price in USD.'); return false; }

        // IQD price optional - default to 0 if empty
        const priceIqdEl = document.querySelector('input[name="price"]');
        if (!priceIqdEl.value.trim()) priceIqdEl.value = '0';

        const area = document.querySelector('input[name="area"]').value.trim();
        if (!area) { alert('Please enter the area.'); return false; }
    }
    if (step === 2) {
        const address = document.querySelector('input[name="address"]').value.trim();
        if (!address) { alert('Please enter the address details.'); return false; }
    }
    if (step === 4) {
        if (selectedFiles.length === 0) { alert('Please upload at least one photo.'); return false; }
    }
    return true;
}

// ═══════════════════════════════════════════
// LISTING TYPE
// ═══════════════════════════════════════════
function setListType(btn, val) {
    document.querySelectorAll('.lt-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('listing_type').value = val;
}

function selectType(label) {
    document.querySelectorAll('.tcard').forEach(c => c.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input').checked = true;
}

function switchLang(btn, lang) {
    document.querySelectorAll('.lpill').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.lang-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('lpane-' + lang).classList.add('active');
}

function toggleCheck(label) {
    label.classList.toggle('checked');
    const cb  = label.querySelector('input');
    cb.checked = !cb.checked;
    const box = label.querySelector('.toggle-box');
    box.innerHTML = cb.checked ? '<i class="fas fa-check" style="font-size:11px"></i>' : '';
}

// ═══════════════════════════════════════════
// MAP TOGGLE
// ═══════════════════════════════════════════
function toggleMapVisibility() {
    const toggle  = document.getElementById('has_map_toggle');
    const wrapper = document.getElementById('map_content_wrapper');
    const lat     = document.getElementById('latitude');
    const lng     = document.getElementById('longitude');
    if (toggle.checked) {
        wrapper.classList.remove('hidden');
        if (leafletMap) setTimeout(() => leafletMap.invalidateSize(), 150);
    } else {
        wrapper.classList.add('hidden');
        lat.value = '0';
        lng.value = '0';
    }
}

// ═══════════════════════════════════════════
// LEAFLET MAP
// ═══════════════════════════════════════════
function initMap() {
    if (leafletMap) return;
    const defaultLat = 36.1911, defaultLng = 44.0091;
    leafletMap = L.map('leaflet-map', { center: [defaultLat, defaultLng], zoom: 13 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(leafletMap);

    const icon = L.divIcon({
        className: '',
        html: `<div style="width:38px;height:38px;background:var(--P,#434eaa);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 14px rgba(67,78,170,.45);display:flex;align-items:center;justify-content:center;"><i class='fas fa-home' style='transform:rotate(45deg);color:white;font-size:13px;'></i></div>`,
        iconSize: [38, 38], iconAnchor: [19, 38], popupAnchor: [0, -42]
    });

    leafletMarker = L.marker([defaultLat, defaultLng], { icon, draggable: true }).addTo(leafletMap);
    leafletMarker.on('dragend', e => { const p = e.target.getLatLng(); updateCoords(p.lat, p.lng); });
    leafletMap.on('click', e => { leafletMarker.setLatLng(e.latlng); updateCoords(e.latlng.lat, e.latlng.lng); });
    updateCoords(defaultLat, defaultLng);
}

function updateCoords(lat, lng) {
    document.getElementById('latitude').value  = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}

function moveMapTo(lat, lng, zoom) {
    if (!leafletMap) return;
    const pos = [parseFloat(lat), parseFloat(lng)];
    leafletMap.setView(pos, zoom || 13);
    leafletMarker.setLatLng(pos);
    updateCoords(pos[0], pos[1]);
}

async function searchMapAddress() {
    const q = document.getElementById('mapSearchInput').value.trim();
    if (!q) return;
    try {
        const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`);
        const data = await res.json();
        if (data.length > 0) moveMapTo(data[0].lat, data[0].lon, 15);
        else alert('Location not found. Try a different search.');
    } catch(e) { console.error(e); }
}

// ═══════════════════════════════════════════
// LOCATION SELECTOR
// ═══════════════════════════════════════════
async function loadCities() {
    try {
        const res  = await fetch('/v1/api/location/branches', { headers: {'Accept-Language':'en'} });
        const data = await res.json();
        if (!data.success) return;
        const sel = document.getElementById('location-city-select');
        sel.innerHTML = '<option value="">— Select City —</option>';
        data.data.sort((a,b) => a.city_name_en.localeCompare(b.city_name_en)).forEach(c => {
            const o = document.createElement('option');
            o.value       = c.id;
            o.textContent = c.city_name_en;
            o.dataset.en  = c.city_name_en;
            o.dataset.ar  = c.city_name_ar;
            o.dataset.ku  = c.city_name_ku;
            o.dataset.lat = c.coordinates?.lat || c.latitude  || '';
            o.dataset.lng = c.coordinates?.lng || c.longitude || '';
            sel.appendChild(o);
        });
        sel.addEventListener('change', async () => {
            const opt = sel.options[sel.selectedIndex];
            document.getElementById('city_en').value = opt.dataset.en || '';
            document.getElementById('city_ar').value = opt.dataset.ar || '';
            document.getElementById('city_ku').value = opt.dataset.ku || '';
            if (opt.dataset.lat && leafletMap) moveMapTo(opt.dataset.lat, opt.dataset.lng, 12);
            await loadAreas(sel.value);
        });
    } catch(e) { console.error('City load error', e); }
}

async function loadAreas(cityId) {
    const sel = document.getElementById('location-area-select');
    if (!cityId) { sel.innerHTML = '<option value="">Select City First</option>'; sel.disabled = true; return; }
    sel.innerHTML = '<option value="">Loading...</option>';
    sel.disabled  = true;
    try {
        const res  = await fetch(`/v1/api/location/branches/${cityId}/areas`, { headers: {'Accept-Language':'en'} });
        const data = await res.json();
        sel.innerHTML = '<option value="">— Select Area —</option>';
        (data.data || []).sort((a,b) => a.area_name_en.localeCompare(b.area_name_en)).forEach(a => {
            const o = document.createElement('option');
            o.value       = a.id;
            o.textContent = a.area_name_en;
            o.dataset.en  = a.area_name_en;
            o.dataset.ar  = a.area_name_ar;
            o.dataset.ku  = a.area_name_ku;
            o.dataset.lat = a.coordinates?.lat || a.latitude  || '';
            o.dataset.lng = a.coordinates?.lng || a.longitude || '';
            sel.appendChild(o);
        });
        sel.disabled = false;
        sel.addEventListener('change', () => {
            const opt = sel.options[sel.selectedIndex];
            document.getElementById('district_en').value = opt.dataset.en || '';
            document.getElementById('district_ar').value = opt.dataset.ar || '';
            document.getElementById('district_ku').value = opt.dataset.ku || '';
            if (opt.dataset.lat && leafletMap) moveMapTo(opt.dataset.lat, opt.dataset.lng, 14);
        });
    } catch(e) { sel.innerHTML = '<option value="">Error loading areas</option>'; sel.disabled = false; }
}

// ═══════════════════════════════════════════
// VIDEO UPLOAD & AI FRAME EXTRACTION
// ═══════════════════════════════════════════
// ═══════════════════════════════════════════
// VIDEO UPLOAD & AI FRAME EXTRACTION (MOBILE OPTIMIZED)
// ═══════════════════════════════════════════
async function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 500 * 1024 * 1024) { alert('Video file too large! Maximum 500MB.'); event.target.value = ''; return; }
    const allowed = ['video/mp4','video/quicktime','video/x-msvideo'];
    if (!allowed.includes(file.type)) { alert('Unsupported format! Use MP4, MOV, or AVI.'); event.target.value = ''; return; }

    const dropArea = document.getElementById('videoDropArea');
    const statusDiv = document.getElementById('videoStatus');
    const vsBar     = document.getElementById('vsBar');
    const vsTitle   = document.getElementById('vsTitle');
    const vsSub     = document.getElementById('vsSubtitle');

    dropArea.classList.add('processing');
    statusDiv.classList.add('show');
    vsBar.style.width = '10%';
    vsTitle.textContent = 'Uploading video to AI service...';
    vsSub.textContent   = 'This may take a few minutes on mobile data';

    const formData = new FormData();
    formData.append('video', file);
    formData.append('num_frames', 10);

    try {
        vsBar.style.width   = '30%';
        vsTitle.textContent = 'Processing video with AI...';
        vsSub.textContent   = 'Analyzing frames and selecting best quality images';

        const controller = new AbortController();
        // FIX 1: Increased timeout to 10 minutes (600,000 ms) for mobile networks
        const timeoutId  = setTimeout(() => controller.abort(), 600000);

        const response = await fetch('/api/video/extract-frames', {
            method: 'POST', body: formData, signal: controller.signal,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        });
        clearTimeout(timeoutId);

        vsBar.style.width = '60%';
        if (!response.ok) throw new Error('AI service error: ' + response.status);

        const result = await response.json();
        if (!result.success || !result.data?.frames) throw new Error(result.message || 'Frame extraction failed');

        vsBar.style.width   = '80%';
        vsTitle.textContent = 'Downloading extracted frames...';
        vsSub.textContent   = `Got ${result.data.frames.length} high-quality frames`;

        // FIX 2: Download all 10 images in parallel instead of one by one
        const framePromises = result.data.frames.map(async (frameUrl, i) => {
            const proxyUrl = frameUrl.replace('http://127.0.0.1:8001/', '/api/video/');
            const fetchResponse = await fetch(proxyUrl);
            if (!fetchResponse.ok) throw new Error(`Failed to fetch image ${i+1}`);
            const blob = await fetchResponse.blob();
            return new File([blob], `ai_frame_${i+1}.jpg`, { type: 'image/jpeg' });
        });

        // Wait for all downloads to finish simultaneously
        const frameFiles = await Promise.all(framePromises);

        vsBar.style.width = '90%';
        // Append the new AI files to any existing manual files selected
        selectedFiles = [...selectedFiles, ...frameFiles];
        renderThumbs();
        syncFiles();

        vsBar.style.width   = '100%';
        vsTitle.textContent = '✅ Extracted ' + frameFiles.length + ' frames successfully!';
        vsSub.textContent   = 'Frames added to your photo gallery below';

        setTimeout(() => { statusDiv.classList.remove('show'); dropArea.classList.remove('processing'); vsBar.style.width = '0%'; }, 3000);

    } catch (error) {
        console.error('Video processing error:', error);

        // Handle the AbortController timeout specifically to inform the user
        if (error.name === 'AbortError') {
            vsTitle.textContent = '❌ Upload Timed Out';
            vsSub.textContent   = 'Your mobile connection is too slow for this video size.';
        } else {
            vsTitle.textContent = '❌ Error processing video';
            vsSub.textContent   = error.message || 'Failed to extract frames';
        }

        vsBar.style.width   = '0%';
        setTimeout(() => { statusDiv.classList.remove('show'); dropArea.classList.remove('processing'); }, 4000);
        alert('Error processing video: ' + (error.name === 'AbortError' ? 'Connection timed out' : error.message));
    }
}

// ═══════════════════════════════════════════
// IMAGE UPLOAD
// ═══════════════════════════════════════════
function setupImages() {
    const dz  = document.getElementById('imgDropzone');
    const inp = document.getElementById('imageInput');
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', ()  => dz.classList.remove('dragover'));
    dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('dragover'); handleFiles(e.dataTransfer.files); });
    inp.addEventListener('change', e => handleFiles(e.target.files));
}

function handleFiles(files) {
    for (const f of files) {
        if (!f.type.match('image.*')) { alert(f.name + ' is not an image'); continue; }
        if (f.size > 30 * 1024 * 1024) { alert(f.name + ' exceeds 30MB limit'); continue; }
        selectedFiles.push(f);
    }
    renderThumbs();
    syncFiles();
}

function renderThumbs() {
    const grid = document.getElementById('imgGrid');
    grid.innerHTML = '';
    selectedFiles.forEach((f, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'img-thumb';
            div.draggable  = true;
            div.dataset.idx = i;
            div.innerHTML = `
                <img src="${e.target.result}" alt="">
                <button type="button" class="img-thumb-del" onclick="removeImg(${i})"><i class="fas fa-times"></i></button>
                ${i === 0 ? '<div class="img-thumb-badge">Cover</div>' : ''}
            `;
            div.addEventListener('dragstart', onDragStart);
            div.addEventListener('dragover',  onDragOver);
            div.addEventListener('drop',      onDrop);
            div.addEventListener('dragend',   onDragEnd);
            grid.appendChild(div);
        };
        reader.readAsDataURL(f);
    });
    const bar = document.getElementById('imgCountBar');
    bar.style.display = selectedFiles.length > 0 ? 'flex' : 'none';
    document.getElementById('imgCount').textContent = selectedFiles.length;
}

function removeImg(idx) { selectedFiles.splice(idx, 1); renderThumbs(); syncFiles(); }

function syncFiles() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    document.getElementById('imageInput').files = dt.files;
}

function onDragStart(e) { dragSrc = this; e.dataTransfer.effectAllowed = 'move'; this.style.opacity = '.4'; }
function onDragOver(e)  { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; return false; }
function onDrop(e) {
    e.stopPropagation();
    if (dragSrc !== this) {
        const fi = parseInt(dragSrc.dataset.idx), ti = parseInt(this.dataset.idx);
        const tmp = selectedFiles[fi]; selectedFiles[fi] = selectedFiles[ti]; selectedFiles[ti] = tmp;
        renderThumbs(); syncFiles();
    }
    return false;
}
function onDragEnd() { this.style.opacity = '1'; }

// ═══════════════════════════════════════════
// REVIEW BUILDER
// ═══════════════════════════════════════════
function buildReview() {
    const get = name => { const el = document.querySelector(`[name="${name}"]`); return el ? el.value : '—'; };
    const typeEl   = document.querySelector('input[name="property_type"]:checked');
    const listType = document.getElementById('listing_type').value;

    const items = [
        { label: 'Listing Type',  value: listType === 'sell' ? '🏷️ For Sale' : '🔑 For Rent' },
        { label: 'Property Type', value: typeEl ? typeEl.value.toUpperCase() : '—' },
        { label: 'Title',         value: get('title_en') || '—' },
        { label: 'Price USD',     value: '$' + (get('price_usd') || '0') },
        { label: 'Price IQD',     value: 'IQD ' + (get('price') || '0') },
        { label: 'Area',          value: get('area') + ' m²' },
        { label: 'City',          value: get('city_en') || '—' },
        { label: 'District',      value: get('district_en') || '—' },
        { label: 'Address',       value: get('address') || '—' },
        { label: 'Bedrooms',      value: get('bedrooms') || '—' },
        { label: 'Bathrooms',     value: get('bathrooms') || '—' },
        { label: 'Status',        value: get('status') || 'available' },
        { label: 'Photos',        value: selectedFiles.length + ' uploaded' },
    ];

    document.getElementById('reviewGrid').innerHTML = items.map(it =>
        `<div class="rv-item"><div class="rv-label">${it.label}</div><div class="rv-value">${it.value}</div></div>`
    ).join('');

    const imgDiv = document.getElementById('reviewImages');
    imgDiv.innerHTML = '';
    selectedFiles.slice(0, 6).forEach(f => {
        const reader = new FileReader();
        reader.onload = e => { const img = document.createElement('img'); img.src = e.target.result; img.className = 'review-img-thumb'; imgDiv.appendChild(img); };
        reader.readAsDataURL(f);
    });
    if (selectedFiles.length > 6) {
        const more = document.createElement('div');
        more.style.cssText = 'width:72px;height:72px;border-radius:10px;background:var(--PL);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--P)';
        more.textContent = '+' + (selectedFiles.length - 6);
        imgDiv.appendChild(more);
    }
}

// ═══════════════════════════════════════════
// UPLOAD PROGRESS DIALOG
// ═══════════════════════════════════════════
function showUploadDialog() {
    document.getElementById('uploadOverlay').classList.add('show');
    const steps = [
        { id: 'udStep1', delay: 0    },
        { id: 'udStep2', delay: 1800 },
        { id: 'udStep3', delay: 3200 },
        { id: 'udStep4', delay: 4400 },
    ];
    let pct = 5;
    const bar   = document.getElementById('udBar');
    const pctEl = document.getElementById('udPct');
    const subEl = document.getElementById('udSub');
    const totalImgs = selectedFiles.length;
    bar.style.width = pct + '%'; pctEl.textContent = pct + '%';

    steps.forEach((s, i) => {
        setTimeout(() => {
            document.querySelectorAll('.ud-step').forEach(el => el.classList.remove('active'));
            document.getElementById(s.id).classList.add('active');
            if (i > 0) document.getElementById(steps[i-1].id).classList.add('done');
            const targetPct = [25,60,80,95][i];
            animatePct(pct, targetPct, bar, pctEl);
            pct = targetPct;
            if (i === 1 && totalImgs > 0) subEl.textContent = 'Uploading ' + totalImgs + ' photo' + (totalImgs > 1 ? 's' : '') + '...';
        }, s.delay);
    });
}

function animatePct(from, to, bar, el) {
    let v = from;
    const step = () => { if (v >= to) return; v = Math.min(v+1, to); bar.style.width = v+'%'; el.textContent = v+'%'; requestAnimationFrame(step); };
    requestAnimationFrame(step);
}

// ═══════════════════════════════════════════
// FORM SUBMIT
// ═══════════════════════════════════════════
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    if (!validateStep(4)) { e.preventDefault(); return; }
    showUploadDialog();
});

// ═══════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    loadCities();
    setupImages();

    const mapToggle = document.getElementById('has_map_toggle');
    if (mapToggle) mapToggle.addEventListener('change', toggleMapVisibility);

    const mi = document.getElementById('mapSearchInput');
    if (mi) mi.addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); searchMapAddress(); } });
});
</script>

@endsection

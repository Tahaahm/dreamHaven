{{--
    resources/views/admin/partials/ui-kit.blade.php

    Shared design system for every admin index page.
    Each page includes it once inside its styles stack.
--}}

<style>
    :root {
        --dm: #303b97;
        --dm-light: #4b56b2;
        --dm-soft: #f2f3fb;
        --dm-gold: #C9A961;
        --dm-gold-soft: #FBF6EA;
    }

    .num { font-variant-numeric: tabular-nums; letter-spacing: -.02em; }
    .dm { color: var(--dm); }
    .bg-dm { background: var(--dm); }
    .bg-dm-soft { background: var(--dm-soft); }
    .dm-gold { color: var(--dm-gold); }
    .bg-dm-gold-soft { background: var(--dm-gold-soft); }

    .page-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:14px; margin-bottom:18px; }
    .eyebrow { font-size:9.5px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; color:#94a3b8; }
    .page-ttl { font-size:23px; font-weight:900; color:#0f172a; letter-spacing:-.02em; line-height:1.1; }
    @media (min-width:768px) { .page-ttl { font-size:29px; } }

    .card { background:#fff; border:1px solid #e8eaf0; border-radius:18px; }
    .card-hd { padding:14px 17px; border-bottom:1px solid #f1f2f7; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .card-ttl { font-weight:800; color:#0f172a; font-size:14.5px; }

    .chip { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:800; padding:3px 8px; border-radius:999px; }
    .chip-up{background:#ecfdf5;color:#047857} .chip-down{background:#fef2f2;color:#b91c1c} .chip-flat{background:#f1f5f9;color:#475569}

    .btn-solid { display:inline-flex; align-items:center; justify-content:center; gap:7px; font-size:12.5px; font-weight:800; padding:11px 16px; border-radius:12px;
                 color:#fff; background:linear-gradient(135deg,var(--dm),var(--dm-light)); box-shadow:0 10px 22px -12px rgba(48,59,151,.9); min-height:44px; transition:.16s; }
    .btn-solid:active { transform:scale(.97); }

    .btn-ghost { display:inline-flex; align-items:center; justify-content:center; gap:6px; font-size:12px; font-weight:800; padding:10px 14px; border-radius:12px;
                 border:1px solid #e4e6ef; color:#475569; background:#fff; min-height:42px; transition:.16s; }
    .btn-ghost:hover { border-color:var(--dm); color:var(--dm); background:var(--dm-soft); }

    /* Stat strip */
    .stat-row { display:flex; gap:10px; overflow-x:auto; padding-bottom:4px; scrollbar-width:none; margin-bottom:18px; }
    .stat-row::-webkit-scrollbar { display:none; }
    .stat { flex:0 0 auto; width:150px; background:#fff; border:1px solid #e8eaf0; border-radius:16px; padding:15px; }
    .stat b { display:block; font-size:24px; font-weight:900; color:#0f172a; line-height:1; margin-bottom:4px; }
    @media (min-width:768px) { .stat-row { display:grid; grid-template-columns:repeat(5,1fr); overflow:visible; } .stat { width:auto; } }
    .stat-row.cols-4 { grid-template-columns:repeat(4,1fr); }

    /* Filter pills */
    .pill-row { display:flex; gap:8px; overflow-x:auto; padding-bottom:4px; scrollbar-width:none; }
    .pill-row::-webkit-scrollbar { display:none; }
    .pill { flex:0 0 auto; padding:9px 14px; border-radius:999px; font-size:11.5px; font-weight:800; border:1px solid #e6e8f2; background:#fff; color:#64748b; white-space:nowrap; transition:.16s; min-height:38px; display:inline-flex; align-items:center; gap:6px; }
    .pill:hover { border-color:#c9cee8; color:var(--dm); }
    .pill.on { background:var(--dm); border-color:transparent; color:#fff; }
    .pill.on-alert { background:#e11d48; border-color:transparent; color:#fff; }

    .search-wrap { position:relative; margin-bottom:12px; }
    .search-wrap i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px; }
    .search-wrap input { width:100%; padding:13px 15px 13px 40px; border-radius:14px; border:1px solid #e6e8f2; background:#fff;
                         font-size:13.5px; font-weight:600; color:#0f172a; outline:none; transition:.16s; }
    .search-wrap input:focus { border-color:var(--dm); box-shadow:0 0 0 3px rgba(48,59,151,.12); }
    .search-wrap input::placeholder { color:#a8b0c4; }

    /* Table */
    .tbl { width:100%; text-align:left; border-collapse:collapse; }
    .tbl thead tr { background:#fafbfd; border-bottom:1px solid #eceef5; }
    .tbl th { padding:13px 18px; font-size:9.5px; font-weight:900; color:#94a3b8; text-transform:uppercase; letter-spacing:.12em; }
    .tbl td { padding:13px 18px; border-bottom:1px solid #f4f5f9; vertical-align:middle; }
    .tbl tbody tr:hover { background:#fafbff; }
    .tbl tbody tr:last-child td { border-bottom:0; }

    .avatar { width:40px; height:40px; border-radius:12px; object-fit:cover; background:var(--dm-soft); flex-shrink:0; }
    .avatar-fb { display:grid; place-items:center; font-size:12px; font-weight:900; color:#fff; background:var(--dm); }

    .badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; font-size:10.5px; font-weight:800; }
    .b-ok{background:#ecfdf5;color:#047857} .b-warn{background:#fffbeb;color:#b45309}
    .b-bad{background:#fef2f2;color:#b91c1c} .b-mute{background:#f1f5f9;color:#64748b}
    .b-plan{background:var(--dm-gold-soft);color:#8a6d24}
    .dotlet { width:6px; height:6px; border-radius:999px; background:currentColor; }

    .iact { width:34px; height:34px; border-radius:10px; display:inline-grid; place-items:center; font-size:11.5px;
            border:1px solid #e6e8f2; background:#fff; color:#64748b; transition:.16s; }
    .iact:hover { background:var(--dm); border-color:transparent; color:#fff; }
    .iact.danger:hover { background:#e11d48; }
    .iact.good:hover { background:#059669; }

    /* Mobile cards */
    .mcard { background:#fff; border:1px solid #e8eaf0; border-radius:18px; overflow:hidden; margin-bottom:12px; }
    .mcard-strip { padding:7px 15px; display:flex; align-items:center; justify-content:space-between; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
    .mcard-body { padding:15px; }
    .mgrid { display:grid; gap:8px; }
    .mbtn { display:flex; align-items:center; justify-content:center; gap:6px; padding:11px 0; border-radius:12px; font-size:11.5px; font-weight:800; width:100%; min-height:42px; transition:.16s; }
    .mbtn:active { transform:scale(.96); }
    .mbtn-p{background:var(--dm);color:#fff} .mbtn-s{background:#f1f2f7;color:#475569}
    .mbtn-g{background:#059669;color:#fff} .mbtn-d{background:#fef2f2;color:#b91c1c}

    .empty-state { padding:52px 20px; text-align:center; }
    .empty-state i { font-size:26px; color:#dbe0ee; display:block; margin-bottom:12px; }
    .empty-state h3 { font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px; }
    .empty-state p { font-size:13px; color:#94a3b8; font-weight:600; margin-bottom:14px; }

    .pager { padding:14px 18px; border-top:1px solid #f1f2f7; background:#fafbfd; }

    /* Expiry ring */
    .ring { --pct:0; --rc:#22c55e; width:38px; height:38px; border-radius:999px; flex-shrink:0; display:grid; place-items:center;
            background:conic-gradient(var(--rc) calc(var(--pct) * 1%), #e6e9f2 0); }
    .ring > span { width:28px; height:28px; border-radius:999px; background:#fff; display:grid; place-items:center; font-size:9.5px; font-weight:900; }

    /* Radar strip */
    .radar { border-radius:18px; padding:15px; margin-bottom:18px; position:relative; overflow:hidden;
             background:linear-gradient(150deg,#1a1d2e,#242a49 60%,#303b97 150%); }
    .radar-scroll { display:flex; gap:10px; overflow-x:auto; padding-bottom:3px; scrollbar-width:none; }
    .radar-scroll::-webkit-scrollbar { display:none; }
    .radar-card { flex:0 0 auto; width:206px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.1); border-radius:14px; padding:12px; transition:.16s; }
    .radar-card:active { transform:scale(.97); }

    @media (prefers-reduced-motion: reduce) { *, .animate-ping, .animate-pulse { animation:none !important; transition:none !important; } }
</style>

<script>
/* Export any table on the page to CSV — no backend route needed. */
function dmExportTable(tableSelector, filename) {
    const table = document.querySelector(tableSelector);
    if (!table) return;

    const rows = [];
    table.querySelectorAll('tr').forEach(function (tr) {
        const cells = [];
        tr.querySelectorAll('th, td').forEach(function (cell) {
            if (cell.dataset.noexport !== undefined) return;
            const text = (cell.innerText || '').replace(/\s+/g, ' ').trim();
            cells.push('"' + text.replace(/"/g, '""') + '"');
        });
        if (cells.length) rows.push(cells.join(','));
    });

    const blob = new Blob(["\uFEFF" + rows.join("\n")], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '-' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    URL.revokeObjectURL(a.href);
}

/* Submit a filter form when a select or input changes. */
function dmAutoSubmit(el) { el.form.submit(); }
</script>

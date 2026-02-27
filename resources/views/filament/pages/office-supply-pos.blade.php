{{-- filament.pages.office-supply-pos.blade.php --}}
<x-filament-panels::page>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    .pos-root * { font-family: 'DM Sans', sans-serif; }
    .pos-mono { font-family: 'DM Mono', monospace; }

    :root {
        --ink: #0f0f14;
        --ink-2: #3a3a4a;
        --ink-3: #7b7b92;
        --surface: #f8f8fb;
        --surface-2: #ffffff;
        --border: #e4e4ee;
        --accent: #4f46e5;
        --accent-hover: #4338ca;
        --accent-soft: #ede9fe;
        --success: #059669;
        --success-soft: #d1fae5;
        --danger: #dc2626;
        --danger-soft: #fee2e2;
        --warning: #d97706;
        --warning-soft: #fef3c7;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
        --shadow-lg: 0 12px 40px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06);
        --shadow-xl: 0 24px 64px rgba(0,0,0,0.14);
        --radius: 14px;
        --radius-lg: 20px;
        --radius-xl: 28px;
    }

    .pos-root {
        background: var(--surface);
        min-height: 100vh;
        color: var(--ink);
    }

    /* ── Topbar ── */
    .pos-topbar {
        position: sticky; top: 0; z-index: 40;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border);
        padding: 16px 24px;
    }
    .pos-topbar-inner {
        max-width: 1440px; margin: 0 auto;
        display: flex; align-items: center; justify-content: space-between;
        gap: 20px; flex-wrap: wrap;
    }
    .pos-brand { display: flex; align-items: center; gap: 12px; }
    .pos-brand-icon {
        width: 40px; height: 40px; background: var(--accent);
        border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;
    }
    .pos-brand h1 { font-size: 1.2rem; font-weight: 700; color: var(--ink); line-height: 1.1; margin: 0; }
    .pos-brand p  { font-size: 0.72rem; color: var(--ink-3); margin: 0; font-weight: 400; }
    .pos-date-badge {
        display: flex; align-items: center; gap: 8px;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 10px; padding: 8px 14px;
        font-size: 0.8rem; color: var(--ink-2); font-weight: 500;
    }

    /* ── Main Layout ── */
    .pos-body {
        max-width: 1440px; margin: 0 auto;
        padding: 28px 24px 180px;
        display: grid; grid-template-columns: 1fr 380px;
        gap: 24px; align-items: start;
    }
    @media (max-width: 1100px) {
        .pos-body { grid-template-columns: 1fr; }
        .pos-cart-panel { display: none; }
    }

    .pos-products { min-width: 0; }

    /* ── Filter Bar ── */
    .filter-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .search-wrap { flex: 1; min-width: 200px; position: relative; display: flex; align-items: center; }
    .search-icon { position: absolute; left: 14px; color: var(--ink-3); pointer-events: none; }
    .search-input {
        width: 100%; padding: 10px 14px 10px 42px;
        border: 1.5px solid var(--border); border-radius: var(--radius);
        font-size: 0.88rem; font-family: 'DM Sans', sans-serif;
        background: var(--surface-2); color: var(--ink);
        outline: none; transition: border-color .15s, box-shadow .15s;
        box-shadow: var(--shadow-sm);
    }
    .search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
    .search-input::placeholder { color: var(--ink-3); }
    .cat-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .cat-pill {
        padding: 7px 16px; border-radius: 999px; font-size: 0.82rem; font-weight: 500;
        cursor: pointer; border: 1.5px solid var(--border); background: white;
        color: var(--ink-2); transition: all .15s; white-space: nowrap;
        font-family: 'DM Sans', sans-serif;
    }
    .cat-pill:hover { border-color: var(--accent); color: var(--accent); }
    .cat-pill.active { background: var(--accent); border-color: var(--accent); color: white; box-shadow: 0 2px 8px rgba(79,70,229,0.3); }

    /* ── Items Grid ── */
    .items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }

    /* ── Item Card ── */
    .item-card {
        background: white; border: 1.5px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden;
        transition: transform .18s, box-shadow .18s, border-color .18s;
        cursor: pointer; position: relative;
    }
    .item-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: #c7d2fe; }
    .item-card.in-cart { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.15), var(--shadow-sm); }
    .item-card.out-of-stock { opacity: 0.65; cursor: default; }
    .item-card.out-of-stock:hover { transform: none; box-shadow: none; border-color: var(--border); }
    .item-card-img {
        height: 140px;
        background: linear-gradient(135deg, #f0f0f8 0%, #e8e8f4 100%);
        display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }
    .item-card-img::after {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 70% 30%, rgba(79,70,229,0.06), transparent 60%);
    }
    .item-card-img svg { color: #a5b4fc; opacity: 0.7; }
    .item-card-img .item-icon-label {
        position: absolute; bottom: 10px; left: 10px;
        font-size: 0.65rem; font-weight: 600; letter-spacing: .06em;
        text-transform: uppercase; color: var(--ink-3);
    }
    .out-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--danger); color: white;
        font-size: 0.65rem; font-weight: 700; padding: 3px 8px;
        border-radius: 999px; letter-spacing: .04em; text-transform: uppercase; z-index: 2;
    }
    .low-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--warning-soft); color: var(--warning);
        font-size: 0.65rem; font-weight: 700; padding: 3px 8px;
        border-radius: 999px; letter-spacing: .04em; text-transform: uppercase; z-index: 2;
    }
    .cart-qty-badge {
        position: absolute; top: 10px; left: 10px;
        background: var(--accent); color: white;
        font-size: 0.7rem; font-weight: 700;
        width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 6px rgba(79,70,229,0.4); z-index: 2;
    }
    .item-card-body { padding: 14px 14px 16px; }
    .item-name {
        font-size: 0.88rem; font-weight: 600; color: var(--ink); line-height: 1.35;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; min-height: 2.4em; margin-bottom: 6px;
    }
    .item-stock {
        font-size: 0.76rem; color: var(--ink-3); font-weight: 500;
        display: flex; align-items: center; gap: 5px; margin-bottom: 12px;
    }
    .item-stock .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--success); flex-shrink: 0; }
    .item-stock .dot.low { background: var(--warning); }
    .item-stock .dot.out { background: var(--danger); }
    .item-variant-select {
        width: 100%; padding: 7px 10px; font-size: 0.8rem;
        border: 1.5px solid var(--border); border-radius: 8px;
        font-family: 'DM Sans', sans-serif; color: var(--ink);
        background: var(--surface); outline: none; margin-bottom: 10px;
        cursor: pointer; transition: border-color .15s;
    }
    .item-variant-select:focus { border-color: var(--accent); }

    /* ── Add/Qty Controls ── */
    .add-btn {
        width: 100%; padding: 9px; background: var(--accent); color: white;
        border: none; border-radius: 10px; font-size: 0.84rem; font-weight: 600;
        font-family: 'DM Sans', sans-serif; cursor: pointer;
        transition: background .15s, transform .1s, box-shadow .15s;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .add-btn:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(79,70,229,0.35); transform: translateY(-1px); }
    .add-btn:disabled { background: #e4e4ee; color: var(--ink-3); cursor: not-allowed; transform: none; box-shadow: none; }
    .qty-controls { display: flex; align-items: center; gap: 4px; background: var(--surface); border-radius: 10px; padding: 4px; }
    .qty-btn {
        width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
        border: none; border-radius: 8px; background: none; cursor: pointer;
        font-size: 1.1rem; font-weight: 700; color: var(--ink-2);
        transition: background .12s, color .12s; font-family: 'DM Sans', sans-serif;
    }
    .qty-btn.minus:hover { background: var(--danger-soft); color: var(--danger); }
    .qty-btn.plus:hover  { background: var(--success-soft); color: var(--success); }
    .qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .qty-num { flex: 1; text-align: center; font-size: 1rem; font-weight: 700; color: var(--ink); }
    .remove-btn {
        width: 100%; margin-top: 8px; padding: 6px; background: none; border: none;
        font-size: 0.76rem; color: var(--ink-3); cursor: pointer; border-radius: 8px;
        font-family: 'DM Sans', sans-serif; font-weight: 500;
        display: flex; align-items: center; justify-content: center; gap: 4px;
        transition: background .12s, color .12s;
    }
    .remove-btn:hover { background: var(--danger-soft); color: var(--danger); }
    .stock-limit-note {
        font-size: 0.72rem; color: var(--warning); text-align: center; margin-top: 6px;
        display: flex; align-items: center; justify-content: center; gap: 4px; font-weight: 500;
    }

    /* ── Cart Panel ── */
    .pos-cart-panel {
        background: white; border: 1.5px solid var(--border);
        border-radius: var(--radius-xl); box-shadow: var(--shadow-md);
        position: sticky; top: 88px; overflow: hidden;
        display: flex; flex-direction: column; max-height: calc(100vh - 110px);
    }
    .cart-header {
        padding: 20px 22px 16px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .cart-header h2 { font-size: 1rem; font-weight: 700; color: var(--ink); margin: 0; display: flex; align-items: center; gap: 8px; }
    .cart-count-badge { background: var(--accent); color: white; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
    .cart-clear-btn {
        font-size: 0.78rem; color: var(--ink-3); cursor: pointer;
        background: none; border: none; font-family: 'DM Sans', sans-serif;
        font-weight: 500; padding: 4px 8px; border-radius: 6px;
        transition: background .12s, color .12s;
    }
    .cart-clear-btn:hover { background: var(--danger-soft); color: var(--danger); }
    .cart-items { overflow-y: auto; flex: 1; padding: 14px 22px; }
    .cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 20px; color: var(--ink-3); text-align: center; }
    .cart-empty svg { opacity: 0.3; margin-bottom: 12px; }
    .cart-empty p { font-size: 0.88rem; font-weight: 500; margin: 0; }
    .cart-empty small { font-size: 0.78rem; opacity: 0.7; margin-top: 4px; display: block; }
    .cart-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--surface); }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-info { flex: 1; min-width: 0; }
    .cart-item-name { font-size: 0.84rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-item-meta { font-size: 0.74rem; color: var(--ink-3); margin-top: 2px; }
    .cart-item-controls { display: flex; align-items: center; gap: 4px; }
    .cart-mini-btn {
        width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
        border-radius: 6px; border: 1.5px solid var(--border); background: none; cursor: pointer;
        font-size: 0.85rem; font-weight: 700; color: var(--ink-2);
        transition: all .12s; font-family: 'DM Mono', monospace;
    }
    .cart-mini-btn:hover { background: var(--accent-soft); border-color: var(--accent); color: var(--accent); }
    .cart-mini-btn:disabled { opacity: 0.35; cursor: not-allowed; background: none; border-color: var(--border); color: var(--ink-3); }
    .cart-item-qty { font-size: 0.84rem; font-weight: 700; width: 28px; text-align: center; color: var(--ink); font-family: 'DM Mono', monospace; }

    /* ── Cart User Block ── */
    .cart-user-block {
        margin-bottom: 14px; padding: 12px 14px;
        background: var(--accent-soft); border-radius: 12px;
        border: 1.5px solid #c7d2fe; display: flex; align-items: center; gap: 10px;
    }
    .cart-user-avatar {
        width: 34px; height: 34px; border-radius: 50%; background: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; color: white; flex-shrink: 0; letter-spacing: -.02em;
    }
    .cart-user-info small { display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--accent); line-height: 1; }
    .cart-user-info strong { display: block; font-size: 0.88rem; font-weight: 700; color: var(--ink); line-height: 1.3; margin-top: 2px; }

    /* ── Cart Footer ── */
    .cart-footer { padding: 16px 22px; border-top: 1px solid var(--border); }
    .cart-summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .cart-summary-label { font-size: 0.82rem; color: var(--ink-3); font-weight: 500; }
    .cart-summary-val { font-size: 0.88rem; font-weight: 700; color: var(--ink); font-family: 'DM Mono', monospace; }
    .submit-btn {
        width: 100%; padding: 14px;
        background: linear-gradient(135deg, var(--accent) 0%, #7c3aed 100%);
        color: white; border: none; border-radius: var(--radius);
        font-size: 0.94rem; font-weight: 700; font-family: 'DM Sans', sans-serif;
        cursor: pointer; transition: opacity .15s, transform .1s, box-shadow .15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 16px rgba(79,70,229,0.35); letter-spacing: -.01em;
    }
    .submit-btn:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 24px rgba(79,70,229,0.45); }
    .submit-btn:disabled { background: #e4e4ee; color: var(--ink-3); cursor: not-allowed; transform: none; box-shadow: none; }

    /* ── Floating Bar ── */
    .pos-floating-bar {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
        background: white; border-top: 1px solid var(--border);
        box-shadow: 0 -8px 32px rgba(0,0,0,0.1); padding: 14px 20px; display: none;
    }
    @media (max-width: 1100px) { .pos-floating-bar { display: block; } }
    .floating-bar-inner { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .floating-bar-info h3 { font-size: 1rem; font-weight: 700; color: var(--ink); margin: 0; }
    .floating-bar-info p  { font-size: 0.78rem; color: var(--ink-3); margin: 0; }
    .floating-bar-actions { display: flex; gap: 10px; }
    .fbar-clear { padding: 10px 18px; background: var(--surface); border: none; border-radius: 10px; font-size: 0.84rem; font-weight: 600; color: var(--ink-2); cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .12s; }
    .fbar-clear:hover { background: var(--danger-soft); color: var(--danger); }
    .fbar-submit { padding: 10px 24px; background: linear-gradient(135deg, var(--accent), #7c3aed); color: white; border: none; border-radius: 10px; font-size: 0.84rem; font-weight: 700; font-family: 'DM Sans', sans-serif; cursor: pointer; box-shadow: 0 2px 10px rgba(79,70,229,0.3); transition: opacity .15s, box-shadow .15s; }
    .fbar-submit:hover { opacity: 0.92; box-shadow: 0 4px 16px rgba(79,70,229,0.4); }

    /* ── Empty States ── */
    .no-results { grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--ink-3); }
    .no-results .emoji { font-size: 3rem; margin-bottom: 12px; opacity: 0.5; }
    .no-results h3 { font-size: 1.1rem; font-weight: 600; color: var(--ink-2); margin: 0 0 6px; }
    .no-results p  { font-size: 0.84rem; margin: 0; }

    /* ── Skeleton ── */
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    .skeleton { background: linear-gradient(90deg, #f0f0f5 25%, #e8e8f0 50%, #f0f0f5 75%); background-size: 800px 100%; animation: shimmer 1.4s infinite linear; border-radius: 8px; }

    /* ── Modals ── */
    @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.92) translateY(12px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.55);
        backdrop-filter: blur(6px); z-index: 200;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; animation: fadeIn .2s ease;
    }
    .modal-card {
        background: white; border-radius: var(--radius-xl);
        padding: 48px 40px; max-width: 480px; width: 100%;
        text-align: center; box-shadow: var(--shadow-xl); animation: scaleIn .25s ease;
    }
    .modal-icon-wrap { width: 80px; height: 80px; margin: 0 auto 24px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .modal-icon-wrap svg { color: var(--success); }
    .modal-card h2 { font-size: 1.6rem; font-weight: 800; color: var(--ink); margin: 0 0 10px; letter-spacing: -.02em; }
    .modal-card p  { font-size: 0.94rem; color: var(--ink-2); line-height: 1.6; margin: 0 0 28px; }
    .modal-ref { display: inline-flex; align-items: center; gap: 8px; background: var(--accent-soft); border-radius: 10px; padding: 10px 20px; font-family: 'DM Mono', monospace; font-size: 0.88rem; font-weight: 600; color: var(--accent); margin-bottom: 28px; }
    .modal-new-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 36px; background: var(--accent); color: white; border: none; border-radius: var(--radius); font-size: 0.94rem; font-weight: 700; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background .15s, box-shadow .15s, transform .1s; box-shadow: 0 4px 14px rgba(79,70,229,0.3); }
    .modal-new-btn:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,0.4); }

    .confirm-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.6);
        backdrop-filter: blur(8px); z-index: 300;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; animation: fadeIn .2s ease;
    }
    .confirm-card { background: white; border-radius: var(--radius-xl); width: 100%; max-width: 520px; box-shadow: var(--shadow-xl); animation: scaleIn .22s ease; overflow: hidden; }
    .confirm-header { padding: 24px 28px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .confirm-header h2 { font-size: 1.1rem; font-weight: 800; color: var(--ink); margin: 0; display: flex; align-items: center; gap: 10px; }
    .confirm-header-icon { width: 36px; height: 36px; background: var(--warning-soft); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--warning); flex-shrink: 0; }
    .confirm-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: none; cursor: pointer; color: var(--ink-3); display: flex; align-items: center; justify-content: center; transition: background .12s, color .12s; font-size: 1.1rem; }
    .confirm-close:hover { background: var(--danger-soft); color: var(--danger); }
    .confirm-meta { padding: 16px 28px 0; display: flex; align-items: center; gap: 10px; }
    .confirm-meta-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: var(--accent); flex-shrink: 0; }
    .confirm-meta-info small  { font-size: 0.72rem; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; display: block; }
    .confirm-meta-info strong { font-size: 0.94rem; color: var(--ink); font-weight: 700; }
    .confirm-meta-date { margin-left: auto; font-family: 'DM Mono', monospace; font-size: 0.78rem; color: var(--ink-3); background: var(--surface); padding: 5px 10px; border-radius: 8px; }
    .confirm-items { padding: 14px 28px; max-height: 280px; overflow-y: auto; }
    .confirm-items-label { font-size: 0.72rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--ink-3); margin-bottom: 10px; display: block; }
    .confirm-table { width: 100%; border-collapse: collapse; }
    .confirm-table thead tr { border-bottom: 1.5px solid var(--border); }
    .confirm-table th { font-size: 0.72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--ink-3); padding: 6px 8px; text-align: left; }
    .confirm-table th:last-child { text-align: right; }
    .confirm-table tbody tr { border-bottom: 1px solid var(--surface); transition: background .1s; }
    .confirm-table tbody tr:hover { background: var(--surface); }
    .confirm-table tbody tr:last-child { border-bottom: none; }
    .confirm-table td { padding: 10px 8px; font-size: 0.84rem; color: var(--ink); vertical-align: middle; }
    .confirm-table td:last-child { text-align: right; font-family: 'DM Mono', monospace; font-weight: 700; color: var(--accent); }
    .confirm-item-name    { font-weight: 600; color: var(--ink); }
    .confirm-item-variant { font-size: 0.74rem; color: var(--ink-3); margin-top: 2px; }
    .confirm-item-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent-soft); border: 2px solid var(--accent); display: inline-block; margin-right: 6px; }
    .confirm-totals { padding: 12px 28px; background: var(--surface); border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .confirm-totals-stat { text-align: center; }
    .confirm-totals-stat .val { font-size: 1.2rem; font-weight: 800; color: var(--ink); font-family: 'DM Mono', monospace; display: block; }
    .confirm-totals-stat .lbl { font-size: 0.7rem; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: .05em; }
    .confirm-totals-divider { width: 1px; height: 32px; background: var(--border); }
    .confirm-actions { padding: 18px 28px; display: flex; gap: 12px; border-top: 1px solid var(--border); }
    .confirm-cancel-btn { flex: 1; padding: 12px; background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 0.88rem; font-weight: 600; font-family: 'DM Sans', sans-serif; color: var(--ink-2); cursor: pointer; transition: background .12s, border-color .12s; }
    .confirm-cancel-btn:hover { background: var(--danger-soft); border-color: var(--danger); color: var(--danger); }
    .confirm-proceed-btn { flex: 2; padding: 12px; background: linear-gradient(135deg, var(--accent), #7c3aed); color: white; border: none; border-radius: var(--radius); font-size: 0.92rem; font-weight: 700; font-family: 'DM Sans', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(79,70,229,0.3); transition: opacity .15s, transform .1s, box-shadow .15s; }
    .confirm-proceed-btn:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,0.4); }
    .confirm-warning-note { padding: 10px 28px 0; font-size: 0.78rem; color: var(--warning); display: flex; align-items: center; gap: 6px; font-weight: 500; }
</style>
@endpush

<div class="pos-root">

    <div class="pos-body">

        {{-- PRODUCTS --}}
        <div class="pos-products">

            <div class="filter-bar">
                <div class="search-wrap">
                    <span class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="itemSearch" class="search-input" placeholder="Search supplies — pen, paper, toner..." />
                </div>
                <div class="cat-pills">
                    @foreach($this->categories as $cat)
                        <button wire:click="$set('activeCategory', '{{ $cat }}')" class="cat-pill {{ $this->activeCategory === $cat ? 'active' : '' }}">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>

            @if(count($this->filteredItems) === 0 && $this->itemSearch)
                <div class="items-grid">
                    <div class="no-results">
                        <div class="emoji">🔍</div>
                        <h3>Nothing found for "{{ $this->itemSearch }}"</h3>
                        <p>Try different keywords or clear the search</p>
                    </div>
                </div>
            @else
                <div class="items-grid">
                    @foreach($this->filteredItems as $item)
                        @php
                            $hasVariants = count($item['variants']) > 1;
                            $selectedVariant = $this->getSelectedVariant($item['id']);
                            $selectedVariantId = $selectedVariant['id'] ?? null;
                            $effectiveStock = $selectedVariant ? (int) $selectedVariant['quantity'] : 0;
                            $stockStatus = $effectiveStock <= 0 ? 'out' : ($effectiveStock <= 3 ? 'low' : 'ok');
                            $inCart = false;
                            $cartQty = 0;

                            // Check if any variant of this item is in cart
                            foreach ($this->cart as $ck => $ci) {
                                if (($ci['item']['id'] ?? 0) === $item['id']) {
                                    $inCart = true;
                                    $cartQty += $ci['qty'];
                                    break;
                                }
                            }
                        @endphp

                        <div class="item-card {{ $inCart ? 'in-cart' : '' }} {{ $stockStatus === 'out' ? 'out-of-stock' : '' }}">
                            <div class="item-card-img">
                                @if($inCart && $cartQty > 0)
                                    <span class="cart-qty-badge">{{ $cartQty }}</span>
                                @endif
                                @if($stockStatus === 'out')
                                    <span class="out-badge">Out of Stock</span>
                                @elseif($stockStatus === 'low')
                                    <span class="low-badge">Low Stock</span>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <div class="item-icon-label">{{ $item['category'] }}</div>
                            </div>

                            <div class="item-card-body">
                                <div class="item-name">{{ $item['name'] }}</div>

                                <div class="item-stock">
                                    <span class="dot {{ $stockStatus }}"></span>
                                    {{ $effectiveStock }} {{ $item['unit'] }} available
                                </div>

                                @if($hasVariants)
                                    <select wire:change="updateVariant({{ $item['id'] }}, $event.target.value)" class="item-variant-select">
                                        @foreach($item['variants'] as $v)
                                            <option value="{{ $v['id'] }}" {{ $selectedVariantId == $v['id'] ? 'selected' : '' }}>
                                                {{ $v['size'] }} — {{ $v['quantity'] }} left
                                            </option>
                                        @endforeach
                                    </select>
                                @endif

                                <button
                                    wire:click="addToCart({{ $item['id'] }})"
                                    class="add-btn"
                                    @if($stockStatus === 'out') disabled @endif
                                >
                                    @if($stockStatus === 'out')
                                        Out of Stock
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- CART SIDEBAR --}}
        <div class="pos-cart-panel">
            <div class="cart-header">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Request Cart
                    @if(count($this->cart))
                        <span class="cart-count-badge">{{ count($this->cart) }}</span>
                    @endif
                </h2>
                @if(count($this->cart))
                    <button wire:click="clearCart" class="cart-clear-btn">Clear all</button>
                @endif
            </div>

            <div class="cart-items">
                @if(empty($this->cart))
                    <div class="cart-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>Your cart is empty</p>
                        <small>Add supplies from the left panel</small>
                    </div>
                @else
                    @foreach($this->cart as $cartKey => $cartItem)
                        @php
                            $itemId = $cartItem['item']['id'] ?? 0;
                            $variantId = $cartItem['selected_variant_id'] ?? null;
                            $liveStock = $variantId
                                ? $this->getVariantQuantity($itemId, $variantId)
                                : 999999;
                            $atMax = $cartItem['qty'] >= $liveStock;
                        @endphp
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name">{{ $cartItem['item']['name'] }}</div>
                                <div class="cart-item-meta">
                                    {{ $cartItem['item']['unit'] ?? 'pc' }}
                                    @if($cartItem['selected_size']) · {{ $cartItem['selected_size'] }} @endif
                                    · {{ $liveStock }} available
                                </div>
                            </div>
                            <div class="cart-item-controls">
                                <button wire:click="decrementQty('{{ $cartKey }}')" class="cart-mini-btn">−</button>
                                <span class="cart-item-qty">{{ $cartItem['qty'] }}</span>
                                <button wire:click="incrementQty('{{ $cartKey }}')" class="cart-mini-btn" @if($atMax) disabled @endif>+</button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="cart-footer">
                @if($this->currentUser)
                    <div class="cart-user-block">
                        <div class="cart-user-avatar">{{ strtoupper(substr($this->currentUser['name'], 0, 2)) }}</div>
                        <div class="cart-user-info">
                            <small>Requested by</small>
                            <strong>{{ $this->currentUser['name'] }}</strong>
                        </div>
                    </div>
                @endif

                <div class="cart-summary-row">
                    <span class="cart-summary-label">Total Items</span>
                    <span class="cart-summary-val">{{ count($this->cart) }}</span>
                </div>
                <div class="cart-summary-row">
                    <span class="cart-summary-label">Total Qty</span>
                    <span class="cart-summary-val">{{ array_sum(array_column($this->cart, 'qty')) }}</span>
                </div>

                <button wire:click="submit" class="submit-btn" @if(empty($this->cart)) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit
                </button>
            </div>
        </div>
    </div>

    {{-- ── CONFIRMATION MODAL ── --}}
    @if($this->showConfirm)
        <div class="confirm-overlay">
            <div class="confirm-card">
                <div class="confirm-header">
                    <h2>
                        <div class="confirm-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        Review & Confirm Request
                    </h2>
                    <button wire:click="cancelConfirm" class="confirm-close">✕</button>
                </div>

                <div class="confirm-meta">
                    <div class="confirm-meta-avatar">
                        {{ strtoupper(substr($this->currentUser['name'] ?? 'UN', 0, 2)) }}
                    </div>
                    <div class="confirm-meta-info">
                        <small>Requested by</small>
                        <strong>{{ $this->currentUser['name'] ?? '—' }}</strong>
                    </div>
                    <div class="confirm-meta-date">{{ now()->format('M d, Y') }}</div>
                </div>

                <div class="confirm-items">
                    <span class="confirm-items-label">Items to be requested</span>
                    <table class="confirm-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Variant</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->cart as $cartItem)
                                @if(isset($cartItem['qty']))
                                    <tr>
                                        <td>
                                            <span class="confirm-item-dot"></span>
                                            <span class="confirm-item-name">{{ $cartItem['item']['name'] }}</span>
                                        </td>
                                        <td>
                                            <span class="confirm-item-variant">{{ $cartItem['selected_size'] ?? '—' }}</span>
                                        </td>
                                        <td>× {{ $cartItem['qty'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="confirm-totals">
                    <div class="confirm-totals-stat">
                        <span class="val">{{ count(array_filter($this->cart, fn($c) => isset($c['qty']))) }}</span>
                        <span class="lbl">Items</span>
                    </div>
                    <div class="confirm-totals-divider"></div>
                    <div class="confirm-totals-stat">
                        <span class="val">{{ array_sum(array_column(array_filter($this->cart, fn($c) => isset($c['qty'])), 'qty')) }}</span>
                        <span class="lbl">Total Qty</span>
                    </div>
                    <div class="confirm-totals-divider"></div>
                    <div class="confirm-totals-stat">
                        <span class="val" style="color:var(--warning);font-size:0.9rem;">REQUESTED</span>
                        <span class="lbl">Status</span>
                    </div>
                </div>

                <div class="confirm-warning-note">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4H12M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    This will deduct quantities from the variant's inventory. This action cannot be undone.
                </div>

                <div class="confirm-actions">
                    <button wire:click="cancelConfirm" class="confirm-cancel-btn">Cancel</button>
                    <button wire:click="confirmSubmit" class="confirm-proceed-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm & Submit
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── SUCCESS MODAL ── --}}
    @if($this->submitted)
        <div class="modal-overlay">
            <div class="modal-card">
                <div class="modal-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2>Submitted!</h2>
                <p>
                    Supply request successfully created for<br>
                    <strong>{{ $this->submittedEmployeeName }}</strong>
                </p>
                <div class="modal-ref">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    REF #{{ str_pad($this->submittedRequestId, 6, '0', STR_PAD_LEFT) }}
                </div>
                <br>
                <button wire:click="resetPOS" class="modal-new-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Request
                </button>
            </div>
        </div>
    @endif

</div>

</x-filament-panels::page>
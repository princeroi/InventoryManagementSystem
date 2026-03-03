{{-- filament.pages.office-supply-pos.blade.php --}}

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    .pos-root * { font-family: 'DM Sans', sans-serif; }
    .pos-mono   { font-family: 'DM Mono', monospace; }

    /* ─── Light tokens ─────────────────────────────────────────────── */
    :root {
        --pos-ink:          #0f0f14;
        --pos-ink-2:        #3a3a4a;
        --pos-ink-3:        #7b7b92;
        --pos-surface:      #f3f4f6;
        --pos-surface-2:    #ffffff;
        --pos-border:       #e5e7eb;
        --pos-accent:       #4f46e5;
        --pos-accent-h:     #4338ca;
        --pos-accent-soft:  #ede9fe;
        --pos-success:      #059669;
        --pos-success-soft: #d1fae5;
        --pos-danger:       #dc2626;
        --pos-danger-soft:  #fee2e2;
        --pos-warning:      #d97706;
        --pos-warning-soft: #fef3c7;
        --pos-card:         #ffffff;
        --pos-topbar:       rgba(255,255,255,0.97);
        --pos-img-a:        #f0f0f8;
        --pos-img-b:        #e8e8f4;
        --pos-img-icon:     #a5b4fc;
        --pos-overlay:      rgba(0,0,0,0.55);
        --pos-avatar-bg:    linear-gradient(135deg,#e0e7ff,#c7d2fe);
        --pos-avatar-color: #4f46e5;
        --pos-ok-bg:        linear-gradient(135deg,#d1fae5,#a7f3d0);
        --pos-ub-border:    #c7d2fe;
        --pos-shadow-sm:    0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
        --pos-shadow-md:    0 4px 16px rgba(0,0,0,.08),0 2px 6px rgba(0,0,0,.04);
        --pos-shadow-lg:    0 12px 40px rgba(0,0,0,.12),0 4px 16px rgba(0,0,0,.06);
        --pos-shadow-xl:    0 24px 64px rgba(0,0,0,.14);
        --pos-r:  12px;
        --pos-rl: 16px;
        --pos-rx: 20px;
        --pos-toolbar-h: 57px;
    }

    /* ─── Dark tokens ───────────────────────────────────────────────── */
    .dark {
        --pos-ink:          #f9fafb;
        --pos-ink-2:        #9ca3af;
        --pos-ink-3:        #6b7280;
        --pos-surface:      #111827;
        --pos-surface-2:    #1f2937;
        --pos-border:       #374151;
        --pos-accent:       #818cf8;
        --pos-accent-h:     #a5b4fc;
        --pos-accent-soft:  #1e1b4b;
        --pos-success:      #34d399;
        --pos-success-soft: #022c22;
        --pos-danger:       #f87171;
        --pos-danger-soft:  #3b0000;
        --pos-warning:      #fbbf24;
        --pos-warning-soft: #2d1a00;
        --pos-card:         #1f2937;
        --pos-topbar:       rgba(17,24,39,0.97);
        --pos-img-a:        #1f2937;
        --pos-img-b:        #111827;
        --pos-img-icon:     #6366f1;
        --pos-overlay:      rgba(0,0,0,0.75);
        --pos-avatar-bg:    linear-gradient(135deg,#1e1b4b,#312e81);
        --pos-avatar-color: #a5b4fc;
        --pos-ok-bg:        linear-gradient(135deg,#022c22,#064e3b);
        --pos-ub-border:    #312e81;
        --pos-shadow-sm:    0 1px 3px rgba(0,0,0,.5),0 1px 2px rgba(0,0,0,.4);
        --pos-shadow-md:    0 4px 16px rgba(0,0,0,.6),0 2px 6px rgba(0,0,0,.4);
        --pos-shadow-lg:    0 12px 40px rgba(0,0,0,.7),0 4px 16px rgba(0,0,0,.5);
        --pos-shadow-xl:    0 24px 64px rgba(0,0,0,.8);
    }

    /* ─── Strip ALL Filament page chrome ───────────────────────────── */
    .fi-page-header,
    .fi-page-header + div,
    header.fi-page-header { 
        display: none !important; 
    }

    .fi-page,
    .fi-page-content,
    .fi-page-content > div,
    .fi-simple-page,
    .fi-main-ctn,
    .fi-main,
    .fi-body {
        padding: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        gap: 0 !important;
        height: 100% !important;
    }
    /* ─── Root ───────────────────────────────────────────────────────── */
    .pos-root {
        background: var(--pos-surface);
        color: var(--pos-ink);
        display: flex;
        flex-direction: column;
        height: calc(100vh);
        overflow: hidden;
        margin: 0 -1.5rem 0 0; /* bleed out of wrapper padding */
        padding: 0;
        width: calc(100% + 1rem);          /* compensate for negative margins */
    }

    /* ─── Toolbar ────────────────────────────────────────────────────── */
    .pos-toolbar {
        height: var(--pos-toolbar-h);
        flex-shrink: 0;
        background: var(--pos-topbar);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--pos-border);
        padding: 0 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        z-index: 40;
        overflow: hidden;
    }
    .pos-toolbar-title {
        display: flex; align-items: center; gap: 9px;
        flex-shrink: 0;
    }
    .pos-toolbar-title-icon {
        width: 30px; height: 30px;
        background: var(--pos-accent);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; flex-shrink: 0;
    }
    .pos-toolbar-title strong {
        font-size: .92rem; font-weight: 700;
        color: var(--pos-ink); display: block; line-height: 1.1;
    }
    .pos-toolbar-title span {
        font-size: .68rem; color: var(--pos-ink-3);
        font-weight: 400; display: block;
    }
    .pos-toolbar-sep {
        width: 1px; height: 22px;
        background: var(--pos-border); flex-shrink: 0;
    }
    .pos-toolbar .search-wrap {
        flex: 1; min-width: 150px; max-width: 320px;
        position: relative; display: flex; align-items: center;
    }
    .pos-toolbar .search-icon {
        position: absolute; left: 11px;
        color: var(--pos-ink-3); pointer-events: none;
    }
    .pos-toolbar .search-input {
        width: 100%; padding: 7px 11px 7px 34px;
        border: 1.5px solid var(--pos-border);
        border-radius: 9px;
        font-size: .8rem; font-family: 'DM Sans',sans-serif;
        background: var(--pos-surface-2); color: var(--pos-ink);
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .pos-toolbar .search-input:focus {
        border-color: var(--pos-accent);
        box-shadow: 0 0 0 3px rgba(129,140,248,.18);
    }
    .pos-toolbar .search-input::placeholder { color: var(--pos-ink-3); }

    .cat-pills { display: flex; gap: 6px; flex-wrap: nowrap; overflow-x: auto; }
    .cat-pills::-webkit-scrollbar { display: none; }
    .cat-pill {
        padding: 5px 13px; border-radius: 999px;
        font-size: .75rem; font-weight: 500; cursor: pointer;
        border: 1.5px solid var(--pos-border);
        background: var(--pos-surface-2); color: var(--pos-ink-2);
        transition: all .15s; white-space: nowrap;
        font-family: 'DM Sans',sans-serif; flex-shrink: 0;
    }
    .cat-pill:hover { border-color: var(--pos-accent); color: var(--pos-accent); }
    .cat-pill.active {
        background: var(--pos-accent); border-color: var(--pos-accent);
        color: #fff; box-shadow: 0 2px 8px rgba(129,140,248,.3);
    }

    /* ─── Main split ─────────────────────────────────────────────────── */
    .pos-main {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 320px;
        min-height: 0;
        overflow: hidden;
    }
    @media (max-width: 1100px) {
        .pos-main { grid-template-columns: 1fr; }
        .pos-cart-panel { display: none; }
        .pos-floating-bar { display: block !important; }
    }

    /* ─── Products pane ──────────────────────────────────────────────── */
    .pos-products {
        overflow-y: auto;
        padding: 18px 20px 40px;
        min-width: 0;
    }
    .pos-products::-webkit-scrollbar { width: 1px; }
    .pos-products::-webkit-scrollbar-track { background: transparent; }
    .pos-products::-webkit-scrollbar-thumb { background: var(--pos-border); border-radius: 99px; }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }

    /* ─── Item card ──────────────────────────────────────────────────── */
    .item-card {
        background: var(--pos-card);
        border: 1.5px solid var(--pos-border);
        border-radius: var(--pos-rl);
        overflow: hidden;
        transition: transform .18s, box-shadow .18s, border-color .18s;
        cursor: pointer;
        position: relative;
    }
    .item-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--pos-shadow-md);
        border-color: var(--pos-accent);
    }
    .item-card.in-cart {
        border-color: var(--pos-accent);
        box-shadow: 0 0 0 3px rgba(129,140,248,.2), var(--pos-shadow-sm);
    }
    .item-card.out-of-stock { opacity: .5; cursor: default; }
    .item-card.out-of-stock:hover {
        transform: none; box-shadow: none; border-color: var(--pos-border);
    }

    .item-card-img {
        height: 110px;
        background: linear-gradient(135deg, var(--pos-img-a) 0%, var(--pos-img-b) 100%);
        display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }
    .item-card-img::after {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 70% 30%, rgba(129,140,248,.08), transparent 60%);
    }
    .item-card-img svg { color: var(--pos-img-icon); opacity: .65; }
    .item-card-img .item-icon-label {
        position: absolute; bottom: 7px; left: 9px;
        font-size: .58rem; font-weight: 600;
        letter-spacing: .06em; text-transform: uppercase;
        color: var(--pos-ink-3);
    }
    .out-badge {
        position: absolute; top: 7px; right: 7px;
        background: var(--pos-danger); color: #fff;
        font-size: .58rem; font-weight: 700;
        padding: 2px 6px; border-radius: 999px;
        letter-spacing: .04em; text-transform: uppercase; z-index: 2;
    }
    .low-badge {
        position: absolute; top: 7px; right: 7px;
        background: var(--pos-warning-soft); color: var(--pos-warning);
        font-size: .58rem; font-weight: 700;
        padding: 2px 6px; border-radius: 999px;
        letter-spacing: .04em; text-transform: uppercase; z-index: 2;
    }
    .cart-qty-badge {
        position: absolute; top: 7px; left: 7px;
        background: var(--pos-accent); color: #fff;
        font-size: .62rem; font-weight: 700;
        width: 20px; height: 20px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 6px rgba(129,140,248,.4); z-index: 2;
    }

    .item-card-body { padding: 11px 11px 13px; }
    .item-name {
        font-size: .82rem; font-weight: 600; color: var(--pos-ink);
        line-height: 1.35; display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; min-height: 2.2em; margin-bottom: 5px;
    }
    .item-stock {
        font-size: .7rem; color: var(--pos-ink-3); font-weight: 500;
        display: flex; align-items: center; gap: 5px; margin-bottom: 9px;
    }
    .item-stock .dot {
        width: 5px; height: 5px; border-radius: 50%;
        background: var(--pos-success); flex-shrink: 0;
    }
    .item-stock .dot.low { background: var(--pos-warning); }
    .item-stock .dot.out { background: var(--pos-danger); }

    .item-variant-select {
        width: 100%; padding: 5px 8px; font-size: .74rem;
        border: 1.5px solid var(--pos-border); border-radius: 7px;
        font-family: 'DM Sans',sans-serif; color: var(--pos-ink);
        background: var(--pos-surface); outline: none;
        margin-bottom: 7px; cursor: pointer; transition: border-color .15s;
    }
    .item-variant-select:focus { border-color: var(--pos-accent); }

    .add-btn {
        width: 100%; padding: 7px;
        background: var(--pos-accent); color: #fff;
        border: none; border-radius: 8px;
        font-size: .78rem; font-weight: 600;
        font-family: 'DM Sans',sans-serif; cursor: pointer;
        transition: background .15s, transform .1s, box-shadow .15s;
        display: flex; align-items: center; justify-content: center; gap: 5px;
    }
    .add-btn:hover {
        background: var(--pos-accent-h);
        box-shadow: 0 4px 12px rgba(129,140,248,.4);
        transform: translateY(-1px);
    }
    .add-btn:disabled {
        background: var(--pos-border); color: var(--pos-ink-3);
        cursor: not-allowed; transform: none; box-shadow: none;
    }

    /* ─── Cart panel ─────────────────────────────────────────────────── */
    .pos-cart-panel {
        background: var(--pos-card);
        border-left: 1px solid var(--pos-border);
        display: flex; flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }

    .cart-header {
        padding: 14px 18px 12px;
        border-bottom: 1px solid var(--pos-border);
        display: flex; align-items: center; justify-content: space-between;
        flex-shrink: 0;
    }
    .cart-header h2 {
        font-size: .88rem; font-weight: 700; color: var(--pos-ink);
        margin: 0; display: flex; align-items: center; gap: 7px;
    }
    .cart-count-badge {
        background: var(--pos-accent); color: #fff;
        font-size: .65rem; font-weight: 700;
        padding: 2px 7px; border-radius: 999px;
    }
    .cart-clear-btn {
        font-size: .72rem; color: var(--pos-ink-3); cursor: pointer;
        background: none; border: none; font-family: 'DM Sans',sans-serif;
        font-weight: 500; padding: 4px 7px; border-radius: 6px;
        transition: background .12s, color .12s;
    }
    .cart-clear-btn:hover { background: var(--pos-danger-soft); color: var(--pos-danger); }

    .cart-items {
        overflow-y: auto; flex: 1; padding: 10px 18px;
    }
    .cart-items::-webkit-scrollbar { width: 1px; }
    .cart-items::-webkit-scrollbar-track { background: transparent; }
    .cart-items::-webkit-scrollbar-thumb { background: var(--pos-border); border-radius: 99px; }

    .cart-empty {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        height: 100%; color: var(--pos-ink-3); text-align: center; padding: 20px;
    }
    .cart-empty svg { opacity: .2; margin-bottom: 10px; }
    .cart-empty p { font-size: .82rem; font-weight: 500; margin: 0; }
    .cart-empty small { font-size: .72rem; opacity: .7; margin-top: 3px; display: block; }

    .cart-item {
        display: flex; align-items: center; gap: 9px;
        padding: 8px 0; border-bottom: 1px solid var(--pos-border);
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-info { flex: 1; min-width: 0; }
    .cart-item-name {
        font-size: .78rem; font-weight: 600; color: var(--pos-ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cart-item-meta { font-size: .68rem; color: var(--pos-ink-3); margin-top: 2px; }
    .cart-item-controls { display: flex; align-items: center; gap: 3px; flex-shrink: 0; }
    .cart-mini-btn {
        width: 22px; height: 22px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 5px; border: 1.5px solid var(--pos-border);
        background: none; cursor: pointer;
        font-size: .8rem; font-weight: 700; color: var(--pos-ink-2);
        transition: all .12s; font-family: 'DM Mono',monospace;
    }
    .cart-mini-btn:hover {
        background: var(--pos-accent-soft);
        border-color: var(--pos-accent); color: var(--pos-accent);
    }
    .cart-mini-btn:disabled {
        opacity: .35; cursor: not-allowed;
        background: none; border-color: var(--pos-border); color: var(--pos-ink-3);
    }
    .cart-item-qty {
        font-size: .78rem; font-weight: 700;
        width: 22px; text-align: center;
        color: var(--pos-ink); font-family: 'DM Mono',monospace;
    }

    /* ─── Cart footer ────────────────────────────────────────────────── */
    .cart-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--pos-border);
        flex-shrink: 0;
    }
    .cart-user-block {
        margin-bottom: 10px; padding: 9px 11px;
        background: var(--pos-accent-soft);
        border-radius: 9px; border: 1.5px solid var(--pos-ub-border);
        display: flex; align-items: center; gap: 8px;
    }
    .cart-user-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--pos-accent);
        display: flex; align-items: center; justify-content: center;
        font-size: .62rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .cart-user-info small {
        display: block; font-size: .6rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        color: var(--pos-accent); line-height: 1;
    }
    .cart-user-info strong {
        display: block; font-size: .8rem; font-weight: 700;
        color: var(--pos-ink); line-height: 1.3; margin-top: 2px;
    }

    .cart-summary-row {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 8px;
    }
    .cart-summary-label { font-size: .76rem; color: var(--pos-ink-3); font-weight: 500; }
    .cart-summary-val {
        font-size: .8rem; font-weight: 700;
        color: var(--pos-ink); font-family: 'DM Mono',monospace;
    }

    .submit-btn {
        width: 100%; padding: 11px;
        background: linear-gradient(135deg, var(--pos-accent) 0%, #7c3aed 100%);
        color: #fff; border: none; border-radius: var(--pos-r);
        font-size: .86rem; font-weight: 700;
        font-family: 'DM Sans',sans-serif; cursor: pointer;
        transition: opacity .15s, transform .1s, box-shadow .15s;
        display: flex; align-items: center; justify-content: center; gap: 7px;
        box-shadow: 0 4px 16px rgba(129,140,248,.35); letter-spacing: -.01em;
    }
    .submit-btn:hover {
        opacity: .92; transform: translateY(-1px);
        box-shadow: 0 6px 24px rgba(129,140,248,.45);
    }
    .submit-btn:disabled {
        background: var(--pos-border); color: var(--pos-ink-3);
        cursor: not-allowed; transform: none; box-shadow: none;
    }

    /* ─── Mobile floating bar ────────────────────────────────────────── */
    .pos-floating-bar {
        display: none;
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
        background: var(--pos-card);
        border-top: 1px solid var(--pos-border);
        box-shadow: 0 -8px 32px rgba(0,0,0,.15);
        padding: 11px 18px;
    }
    .floating-bar-inner {
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }
    .floating-bar-info h3 { font-size: .9rem; font-weight: 700; color: var(--pos-ink); margin: 0; }
    .floating-bar-info p  { font-size: .72rem; color: var(--pos-ink-3); margin: 0; }
    .floating-bar-actions { display: flex; gap: 8px; }
    .fbar-clear {
        padding: 8px 14px; background: var(--pos-surface); border: none;
        border-radius: 8px; font-size: .78rem; font-weight: 600;
        color: var(--pos-ink-2); cursor: pointer;
        font-family: 'DM Sans',sans-serif; transition: background .12s;
    }
    .fbar-clear:hover { background: var(--pos-danger-soft); color: var(--pos-danger); }
    .fbar-submit {
        padding: 8px 20px;
        background: linear-gradient(135deg, var(--pos-accent), #7c3aed);
        color: #fff; border: none; border-radius: 8px;
        font-size: .78rem; font-weight: 700;
        font-family: 'DM Sans',sans-serif; cursor: pointer;
    }

    /* ─── Empty / no-results ─────────────────────────────────────────── */
    .no-results {
        grid-column: 1/-1; text-align: center;
        padding: 60px 20px; color: var(--pos-ink-3);
    }
    .no-results .emoji { font-size: 2.4rem; margin-bottom: 10px; opacity: .5; }
    .no-results h3 { font-size: .96rem; font-weight: 600; color: var(--pos-ink-2); margin: 0 0 5px; }
    .no-results p  { font-size: .8rem; margin: 0; }

    /* ─── Animations ─────────────────────────────────────────────────── */
    @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
    @keyframes scaleIn { from { opacity: 0; transform: scale(.93) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    .skeleton {
        background: linear-gradient(90deg, var(--pos-surface) 25%, var(--pos-border) 50%, var(--pos-surface) 75%);
        background-size: 800px 100%;
        animation: shimmer 1.4s infinite linear; border-radius: 8px;
    }

    /* ─── Confirm modal ──────────────────────────────────────────────── */
    .confirm-overlay {
        position: fixed; inset: 0; background: var(--pos-overlay);
        backdrop-filter: blur(8px); z-index: 300;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; animation: fadeIn .2s ease;
    }
    .confirm-card {
        background: var(--pos-card); border: 1px solid var(--pos-border);
        border-radius: var(--pos-rx); width: 100%; max-width: 500px;
        box-shadow: var(--pos-shadow-xl); animation: scaleIn .22s ease; overflow: hidden;
    }
    .confirm-header {
        padding: 20px 24px 13px; border-bottom: 1px solid var(--pos-border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .confirm-header h2 {
        font-size: .96rem; font-weight: 800; color: var(--pos-ink);
        margin: 0; display: flex; align-items: center; gap: 9px;
    }
    .confirm-header-icon {
        width: 32px; height: 32px; background: var(--pos-warning-soft);
        border-radius: 8px; display: flex; align-items: center; justify-content: center;
        color: var(--pos-warning); flex-shrink: 0;
    }
    .confirm-close {
        width: 28px; height: 28px; border-radius: 7px; border: none;
        background: none; cursor: pointer; color: var(--pos-ink-3);
        display: flex; align-items: center; justify-content: center;
        transition: background .12s, color .12s; font-size: .96rem;
    }
    .confirm-close:hover { background: var(--pos-danger-soft); color: var(--pos-danger); }

    .confirm-meta {
        padding: 13px 24px 0; display: flex; align-items: center; gap: 9px;
    }
    .confirm-meta-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: var(--pos-avatar-bg);
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; font-weight: 800; color: var(--pos-avatar-color); flex-shrink: 0;
    }
    .confirm-meta-info small {
        font-size: .65rem; color: var(--pos-ink-3); font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; display: block;
    }
    .confirm-meta-info strong { font-size: .88rem; color: var(--pos-ink); font-weight: 700; }
    .confirm-meta-date {
        margin-left: auto; font-family: 'DM Mono',monospace;
        font-size: .72rem; color: var(--pos-ink-3);
        background: var(--pos-surface); border: 1px solid var(--pos-border);
        padding: 4px 8px; border-radius: 7px;
    }

    .confirm-items { padding: 11px 24px; max-height: 240px; overflow-y: auto; }
    .confirm-items::-webkit-scrollbar { width: 1px; }
    .confirm-items::-webkit-scrollbar-track { background: transparent; }
    .confirm-items::-webkit-scrollbar-thumb { background: var(--pos-border); border-radius: 99px; }
    .confirm-items-label {
        font-size: .65rem; font-weight: 700; letter-spacing: .07em;
        text-transform: uppercase; color: var(--pos-ink-3);
        margin-bottom: 8px; display: block;
    }

    .confirm-table { width: 100%; border-collapse: collapse; }
    .confirm-table thead tr { border-bottom: 1.5px solid var(--pos-border); }
    .confirm-table th {
        font-size: .65rem; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: var(--pos-ink-3);
        padding: 5px 6px; text-align: left;
    }
    .confirm-table th:last-child { text-align: right; }
    .confirm-table tbody tr { border-bottom: 1px solid var(--pos-border); transition: background .1s; }
    .confirm-table tbody tr:hover { background: var(--pos-surface); }
    .confirm-table tbody tr:last-child { border-bottom: none; }
    .confirm-table td {
        padding: 8px 6px; font-size: .8rem;
        color: var(--pos-ink); vertical-align: middle;
    }
    .confirm-table td:last-child {
        text-align: right; font-family: 'DM Mono',monospace;
        font-weight: 700; color: var(--pos-accent);
    }
    .confirm-item-name    { font-weight: 600; }
    .confirm-item-variant { font-size: .68rem; color: var(--pos-ink-3); margin-top: 2px; }
    .confirm-item-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--pos-accent-soft); border: 2px solid var(--pos-accent);
        display: inline-block; margin-right: 5px;
    }

    .confirm-totals {
        padding: 9px 24px; background: var(--pos-surface);
        border-top: 1px solid var(--pos-border);
        display: flex; justify-content: space-between; align-items: center;
    }
    .confirm-totals-stat { text-align: center; }
    .confirm-totals-stat .val {
        font-size: 1.05rem; font-weight: 800; color: var(--pos-ink);
        font-family: 'DM Mono',monospace; display: block;
    }
    .confirm-totals-stat .lbl {
        font-size: .62rem; font-weight: 600; color: var(--pos-ink-3);
        text-transform: uppercase; letter-spacing: .05em;
    }
    .confirm-totals-divider { width: 1px; height: 26px; background: var(--pos-border); }

    .confirm-actions {
        padding: 14px 24px; display: flex; gap: 10px;
        border-top: 1px solid var(--pos-border);
    }
    .confirm-cancel-btn {
        flex: 1; padding: 10px; background: var(--pos-surface);
        border: 1.5px solid var(--pos-border); border-radius: var(--pos-r);
        font-size: .82rem; font-weight: 600; font-family: 'DM Sans',sans-serif;
        color: var(--pos-ink-2); cursor: pointer;
        transition: background .12s, border-color .12s;
    }
    .confirm-cancel-btn:hover {
        background: var(--pos-danger-soft);
        border-color: var(--pos-danger); color: var(--pos-danger);
    }
    .confirm-proceed-btn {
        flex: 2; padding: 10px;
        background: linear-gradient(135deg, var(--pos-accent), #7c3aed);
        color: #fff; border: none; border-radius: var(--pos-r);
        font-size: .86rem; font-weight: 700; font-family: 'DM Sans',sans-serif;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
        box-shadow: 0 4px 14px rgba(129,140,248,.3);
        transition: opacity .15s, transform .1s, box-shadow .15s;
    }
    .confirm-proceed-btn:hover {
        opacity: .92; transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(129,140,248,.4);
    }
    .confirm-warning-note {
        padding: 7px 24px 0; font-size: .72rem;
        color: var(--pos-warning); display: flex; align-items: center;
        gap: 5px; font-weight: 500;
    }

    /* ─── Success modal ──────────────────────────────────────────────── */
    .modal-overlay {
        position: fixed; inset: 0; background: var(--pos-overlay);
        backdrop-filter: blur(6px); z-index: 200;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; animation: fadeIn .2s ease;
    }
    .modal-card {
        background: var(--pos-card); border: 1px solid var(--pos-border);
        border-radius: var(--pos-rx); padding: 40px 36px; max-width: 420px; width: 100%;
        text-align: center; box-shadow: var(--pos-shadow-xl); animation: scaleIn .25s ease;
    }
    .modal-icon-wrap {
        width: 68px; height: 68px; margin: 0 auto 18px;
        background: var(--pos-ok-bg); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-icon-wrap svg { color: var(--pos-success); }
    .modal-card h2 {
        font-size: 1.45rem; font-weight: 800; color: var(--pos-ink);
        margin: 0 0 7px; letter-spacing: -.02em;
    }
    .modal-card p {
        font-size: .88rem; color: var(--pos-ink-2);
        line-height: 1.6; margin: 0 0 22px;
    }
    .modal-ref {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--pos-accent-soft); border-radius: 9px;
        padding: 8px 16px; font-family: 'DM Mono',monospace;
        font-size: .82rem; font-weight: 600; color: var(--pos-accent); margin-bottom: 22px;
    }
    .modal-new-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 11px 30px; background: var(--pos-accent); color: #fff;
        border: none; border-radius: var(--pos-r);
        font-size: .88rem; font-weight: 700; font-family: 'DM Sans',sans-serif;
        cursor: pointer; transition: background .15s, box-shadow .15s, transform .1s;
        box-shadow: 0 4px 14px rgba(129,140,248,.3);
    }
    .modal-new-btn:hover {
        background: var(--pos-accent-h); transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(129,140,248,.4);
    }
</style>
@endpush

<div class="pos-root">

    {{-- ── STICKY TOOLBAR ── --}}
    <div class="pos-toolbar">
        <div class="pos-toolbar-title">
            <div class="pos-toolbar-title-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                Supply POS
                <span>{{ now()->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="pos-toolbar-sep"></div>

        <div class="search-wrap">
            <span class="search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                </svg>
            </span>
            <input wire:model.live.debounce.300ms="itemSearch" class="search-input" placeholder="Search supplies…" />
        </div>

        <div class="cat-pills">
            @foreach($this->categories as $cat)
                <button wire:click="$set('activeCategory', '{{ $cat }}')" class="cat-pill {{ $this->activeCategory === $cat ? 'active' : '' }}">{{ $cat }}</button>
            @endforeach
        </div>
    </div>

    {{-- ── MAIN ── --}}
    <div class="pos-main">

        {{-- PRODUCTS --}}
        <div class="pos-products">
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
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
                                <button wire:click="addToCart({{ $item['id'] }})" class="add-btn" @if($stockStatus === 'out') disabled @endif>
                                    @if($stockStatus === 'out')
                                        Out of Stock
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
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
                            $liveStock = $variantId ? $this->getVariantQuantity($itemId, $variantId) : 999999;
                            $atMax = $cartItem['qty'] >= $liveStock;
                        @endphp
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name">{{ $cartItem['item']['name'] }}</div>
                                <div class="cart-item-meta">
                                    {{ $cartItem['item']['unit'] ?? 'pc' }}
                                    @if($cartItem['selected_size']) · {{ $cartItem['selected_size'] }} @endif
                                    · {{ $liveStock }} avail.
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Request
                </button>
            </div>
        </div>
    </div>

    {{-- ── MOBILE FLOATING BAR ── --}}
    @if(count($this->cart))
    <div class="pos-floating-bar">
        <div class="floating-bar-inner">
            <div class="floating-bar-info">
                <h3>{{ count($this->cart) }} item{{ count($this->cart) > 1 ? 's' : '' }} · {{ array_sum(array_column($this->cart, 'qty')) }} qty</h3>
                <p>Ready to submit</p>
            </div>
            <div class="floating-bar-actions">
                <button wire:click="clearCart" class="fbar-clear">Clear</button>
                <button wire:click="submit" class="fbar-submit">Submit →</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── CONFIRM MODAL ── --}}
    @if($this->showConfirm)
        <div class="confirm-overlay">
            <div class="confirm-card">
                <div class="confirm-header">
                    <h2>
                        <div class="confirm-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        Review & Confirm
                    </h2>
                    <button wire:click="cancelConfirm" class="confirm-close">✕</button>
                </div>

                <div class="confirm-meta">
                    <div class="confirm-meta-avatar">{{ strtoupper(substr($this->currentUser['name'] ?? 'UN', 0, 2)) }}</div>
                    <div class="confirm-meta-info">
                        <small>Requested by</small>
                        <strong>{{ $this->currentUser['name'] ?? '—' }}</strong>
                    </div>
                    <div class="confirm-meta-date">{{ now()->format('M d, Y') }}</div>
                </div>

                <div class="confirm-items">
                    <span class="confirm-items-label">Items to be requested</span>
                    <table class="confirm-table">
                        <thead><tr><th>Item</th><th>Variant</th><th>Qty</th></tr></thead>
                        <tbody>
                            @foreach($this->cart as $cartItem)
                                @if(isset($cartItem['qty']))
                                    <tr>
                                        <td><span class="confirm-item-dot"></span><span class="confirm-item-name">{{ $cartItem['item']['name'] }}</span></td>
                                        <td><span class="confirm-item-variant">{{ $cartItem['selected_size'] ?? '—' }}</span></td>
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
                        <span class="val" style="color:var(--pos-warning);font-size:.82rem;font-family:'DM Sans',sans-serif;">PENDING</span>
                        <span class="lbl">Status</span>
                    </div>
                </div>

                <div class="confirm-warning-note">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4H12M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    This will deduct quantities from inventory. This action cannot be undone.
                </div>

                <div class="confirm-actions">
                    <button wire:click="cancelConfirm" class="confirm-cancel-btn">Cancel</button>
                    <button wire:click="confirmSubmit" class="confirm-proceed-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2>Submitted!</h2>
                <p>Supply request successfully created for<br><strong>{{ $this->submittedEmployeeName }}</strong></p>
                <div class="modal-ref">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    REF #{{ str_pad($this->submittedRequestId, 6, '0', STR_PAD_LEFT) }}
                </div>
                <br>
                <button wire:click="resetPOS" class="modal-new-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Request
                </button>
            </div>
        </div>
    @endif

</div>

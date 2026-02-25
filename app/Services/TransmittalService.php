<?php

namespace App\Services;

use App\Models\Transmittal;
use App\Models\UniformIssuance;

class TransmittalService
{
    private const COMPANY_NAME    = 'STRONGLINK SERVICES';
    private const COMPANY_TAGLINE = 'Manpower and Housekeeping Services Provider';
    private const COMPANY_DEPT    = 'HR DEPARTMENT';
    private const COMPANY_ADDRESS = 'RL Bldg., Francisco Village, Brgy. Pulong Sta. Cruz, Santa Rosa, Laguna 4026';
    private const COMPANY_PHONE   = 'Tel no.: (049) 539-3215';

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC ENTRY POINTS
    // ─────────────────────────────────────────────────────────────────────────

    public static function generateFromTransmittal(Transmittal $transmittal): string
    {
        $transmittal->loadMissing(
            'issuances.site',
            'issuances.issuanceType',
            'issuances.recipients.position',
            'issuances.recipients.items.item'
        );
        $data = self::buildDataFromTransmittal($transmittal);
        return self::wrapDocument($data, "Transmittal #{$transmittal->transmittal_number}");
    }

    public static function generateFromIssuance(UniformIssuance $issuance): string
    {
        $issuance->loadMissing(
            'site', 'issuanceType',
            'recipients.position',
            'recipients.items.item',
            'transmittal'
        );
        $data  = self::buildDataFromIssuance($issuance);
        $txnNo = $issuance->transmittal?->transmittal_number ?? 'PREVIEW';
        return self::wrapDocument($data, "Transmittal #{$txnNo}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DATA BUILDERS
    // ─────────────────────────────────────────────────────────────────────────

    private static function buildDataFromTransmittal(Transmittal $transmittal): array
    {
        $rows = [];
        $issuanceType = null;
        foreach ($transmittal->issuances as $issuance) {
            if (!$issuanceType) $issuanceType = $issuance->issuanceType?->name;
            foreach ($issuance->recipients as $recipient) {
                foreach ($recipient->items as $item) {
                    $qty = (int)($item->released_quantity ?: $item->quantity);
                    if ($qty <= 0) continue;
                    $rows[] = [
                        'employee'  => $recipient->employee_name ?? '—',
                        'item_name' => $item->item?->name ?? "Item #{$item->item_id}",
                        'size'      => $item->size ?? '',
                        'qty'       => $qty,
                    ];
                }
            }
        }
        return [
            'transmittal_number' => $transmittal->transmittal_number,
            'transmitted_by'     => $transmittal->transmitted_by ?? '—',
            'transmitted_to'     => $transmittal->transmitted_to ?? '—',
            'issuance_type'      => $issuanceType ?? '—',
            'purpose'            => $transmittal->purpose ?? '',
            'instructions'       => $transmittal->instructions ?? '',
            'date'               => $transmittal->created_at
                ? \Carbon\Carbon::parse($transmittal->created_at)->timezone('Asia/Manila')->format('F d, Y')
                : now()->format('F d, Y'),
            'rows' => $rows,
        ];
    }

    private static function buildDataFromIssuance(UniformIssuance $issuance): array
    {
        $rows = [];
        foreach ($issuance->recipients as $recipient) {
            foreach ($recipient->items as $item) {
                $qty = (int)($item->released_quantity ?: $item->quantity);
                if ($qty <= 0) continue;
                $rows[] = [
                    'employee'  => $recipient->employee_name ?? '—',
                    'item_name' => $item->item?->name ?? "Item #{$item->item_id}",
                    'size'      => $item->size ?? '',
                    'qty'       => $qty,
                ];
            }
        }
        $txn = $issuance->transmittal;
        return [
            'transmittal_number' => $txn?->transmittal_number ?? 'PREVIEW',
            'transmitted_by'     => $txn?->transmitted_by ?? (auth()->user()?->name ?? '—'),
            'transmitted_to'     => $issuance->transmitted_to ?? $txn?->transmitted_to ?? '—',
            'issuance_type'      => $issuance->issuanceType?->name ?? '—',
            'purpose'            => $issuance->transmittal_purpose ?? $txn?->purpose ?? '',
            'instructions'       => $issuance->transmittal_instructions ?? $txn?->instructions ?? '',
            'date'               => $txn?->created_at
                ? \Carbon\Carbon::parse($txn->created_at)->timezone('Asia/Manila')->format('F d, Y')
                : now()->format('F d, Y'),
            'rows' => $rows,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDERER
    // ─────────────────────────────────────────────────────────────────────────

    private static function renderPage(array $d): string
    {
        $txnNo        = e($d['transmittal_number']);
        $txnBy        = e($d['transmitted_by']);
        $txnTo        = e($d['transmitted_to']);
        $date         = e($d['date']);
        $issuanceType = e($d['issuance_type'] ?? '—');
        $purpose      = e($d['purpose'] ?? '');
        $instructions = e($d['instructions'] ?? '');
        $rows         = $d['rows'] ?? [];

        $cn      = e(self::COMPANY_NAME);
        $tagline = e(self::COMPANY_TAGLINE);
        $dept    = e(self::COMPANY_DEPT);
        $addr    = e(self::COMPANY_ADDRESS);
        $phone   = e(self::COMPANY_PHONE);

        // ── Item rows ─────────────────────────────────────────────────────────
        $itemRows   = '';
        $grandTotal = 0;
        $MIN_ROWS   = 12;

        foreach ($rows as $i => $row) {
            $no       = $i + 1;
            $employee = e($row['employee']);
            $itemName = e($row['item_name']);
            $size     = $row['size'] ? ' (' . e($row['size']) . ')' : '';
            $qty      = (int)$row['qty'];
            $grandTotal += $qty;
            $desc = "{$employee} - {$itemName}{$size}";
            $itemRows .= "
                <tr class='data-row'>
                    <td class='c-no'>{$no}</td>
                    <td class='c-qty'>{$qty} PCS</td>
                    <td class='c-desc'>{$desc}</td>
                </tr>";
        }

        // Filler rows to fill the table body
        for ($f = count($rows); $f < $MIN_ROWS; $f++) {
            $itemRows .= "
                <tr class='data-row filler'>
                    <td class='c-no'>&nbsp;</td>
                    <td class='c-qty'>&nbsp;</td>
                    <td class='c-desc'>&nbsp;</td>
                </tr>";
        }

        return <<<HTML
<div class="page">

    <!-- ════ COMPANY HEADER ════ -->
    <div class="co-header">
        <!-- Logo -->
        <div class="co-logo">
            <div class="logo-top">STRONG<span>LINK</span></div>
            <div class="logo-bottom">— SERVICES —</div>
        </div>
        <div class="co-tagline">{$tagline}</div>
    </div>

    <!-- ════ DEPT TITLE + No. ════ -->
    <table class="dept-row">
        <tr>
            <td class="dept-name">{$dept}</td>
            <td class="no-label">No.</td>
            <td class="no-value">{$txnNo}</td>
        </tr>
    </table>

    <!-- ════ TO / FROM / DATE ════ -->
    <table class="meta-table">
        <tr>
            <td class="meta-key">TO:</td>
            <td class="meta-val meta-val-to">{$txnTo}</td>
        </tr>
        <tr>
            <td class="meta-key">FROM:</td>
            <td class="meta-val">HR {$txnBy}</td>
        </tr>
        <tr>
            <td class="meta-key">DATE:</td>
            <td class="meta-val">{$date}</td>
        </tr>
        <tr class="meta-spacer">
            <td colspan="2" style="border:none !important;background:#fff;padding:2mm 0;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" class="meta-issuance-type">{$issuanceType}</td>
        </tr>
    </table>

    <!-- ════ ITEMS TABLE ════ -->
    <table class="items-table">
        <thead>
            <tr class="col-header">
                <th class="c-no">ITEM NO.</th>
                <th class="c-qty">QTY</th>
                <th class="c-desc">ITEM DESCRIPTION</th>
            </tr>

        </thead>
        <tbody>
            {$itemRows}
        </tbody>
    </table>

    <!-- ════ BOTTOM SECTION ════ -->
    <table class="bottom-table">
        <tr>
            <td class="b-label">Purpose :</td>
            <td class="b-value b-bold">{$purpose}</td>
        </tr>
        <tr>
            <td class="b-label">Instructions : <span class="b-hint">(please specify)</span></td>
            <td class="b-value b-bold">{$instructions}</td>
        </tr>
        <tr>
            <td class="b-label">Received By :</td>
            <td class="b-value b-sig">
                <div class="sig-space"></div>
                <div class="sig-line-wrap">
                    <div class="sig-line"></div>
                    <div class="sig-line-label">Signature over printed name</div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="b-label">Date :</td>
            <td class="b-value" style="text-align:center;vertical-align:middle;padding:3mm 3mm;">
                <div class="date-line"></div>
            </td>
        </tr>
    </table>

    <!-- ════ ADDRESS FOOTER ════ -->
    <div class="addr-footer">
        {$addr}<br>{$phone}
    </div>

</div>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML DOCUMENT WRAPPER
    // ─────────────────────────────────────────────────────────────────────────

    private static function wrapDocument(array $data, string $title): string
    {
        $page       = self::renderPage($data);
        $safeTitle  = e($title);
        $genTime    = now()->timezone('Asia/Manila')->format('M d, Y h:i A');
        $totalItems = count($data['rows']);
        $grandTotal = array_sum(array_column($data['rows'], 'qty'));

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$safeTitle}</title>
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Arial,sans-serif;background:#d1d9e6;color:#000;}

/* ── Screen toolbar ── */
.toolbar{
    position:fixed;top:0;left:0;right:0;z-index:9999;
    font-family:'Segoe UI',Arial,sans-serif;
    background:#1e3a5f;
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 28px;box-shadow:0 2px 16px rgba(0,0,0,.4);
}
.tbar-l{display:flex;align-items:center;gap:16px;}
.tbar-title{font-size:14px;font-weight:800;color:#fff;letter-spacing:.02em;}
.tbar-sub{font-size:10px;color:#94a3b8;margin-top:1px;}
.tbar-badge{background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:999px;}
.tbar-r{display:flex;gap:8px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s;}
.btn:hover{opacity:.85;}
.btn-blue{background:#2563eb;color:#fff;}
.btn-ghost{background:rgba(255,255,255,.1);color:#e2e8f0;border:1px solid rgba(255,255,255,.2);}

/* ── A4 wrapper ── */
.pages{padding:72px 32px 48px;display:flex;flex-direction:column;align-items:center;}
.a4{
    width:210mm;
    height:297mm;
    background:#fff;
    box-shadow:0 8px 32px rgba(0,0,0,.22);
    border-radius:2px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
}

/* ── PAGE — fills the A4 ── */
.page{
    width:100%;
    height:100%;
    display:flex;
    flex-direction:column;
    padding:6mm 8mm 5mm;
    border:1.5px dashed #b0bec5; /* dashed border like the sample */
    box-sizing:border-box;
}

/* ════ COMPANY HEADER ════ */
.co-header{
    text-align:center;
    flex-shrink:0;
    padding-bottom:2mm;
    border-bottom:1px solid #ccc;
}
.co-logo{
    display:inline-flex;
    flex-direction:column;
    align-items:center;
    margin-bottom:1mm;
}
.logo-top{
    font-size:20pt;
    font-weight:900;
    letter-spacing:.08em;
    color:#1a237e;
    line-height:1;
    font-family:Arial,sans-serif;
}
.logo-top span{color:#1565c0;}
.logo-bottom{
    font-size:8pt;
    color:#555;
    letter-spacing:.2em;
    margin-top:0;
}
.co-tagline{
    font-size:9.5pt;
    color:#333;
    margin-top:1mm;
}

/* ════ DEPT TITLE + No. ════ */
.dept-row{
    width:100%;
    border-collapse:collapse;
    border:1.5px solid #000;
    border-top:none;
    flex-shrink:0;
}
.dept-row td{
    padding:2.5mm 3mm;
    border:1px solid #000;
    vertical-align:middle;
    background:#1a237e;
    color:#fff;
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
}
.dept-name{
    font-size:14pt;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.06em;
    text-align:center;
    border-right:2px solid #fff;
    color:#fff;
}
.no-label{
    font-size:9.5pt;
    font-weight:700;
    width:18mm;
    text-align:center;
    border-right:1px solid #fff;
    white-space:nowrap;
    color:#fff;
}
.no-value{
    font-size:12pt;
    font-weight:900;
    color:#93c5fd;
    width:30mm;
    text-align:center;
    white-space:nowrap;
}

/* ════ META TABLE ════ */
.meta-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    border:1px solid #ccc;
    border-top:none;
    flex-shrink:0;
}
.meta-table td{
    padding:1.8mm 3mm;
    border-bottom:1px solid #ccc;
    border-right:1px solid #ccc;
    font-size:10pt;
    vertical-align:middle;
}
.meta-table td:first-child{
    border-left:none;
}
.meta-table tr:last-child td{
    border-bottom:none;
}
.meta-key{
    font-weight:700;
    width:18mm;
    text-align:right;
    white-space:nowrap;
    border-right:1px solid #ccc;
    color:#333;
}
.meta-val{
    text-align:center;
}
.meta-val-to{
    font-weight:700;
    font-size:11pt;
}
.meta-spacer td{
    padding:2mm 0;
    border:none !important;
    background:#fff;
}
.meta-table tr.meta-spacer td{
    border:none !important;
    outline:none !important;
    box-shadow:none !important;
}
.meta-issuance-type{
    text-align:center;
    font-size:11pt;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    padding:2.5mm 3mm;
    border-top:1px solid #ccc;
    border-right:none;
    color:#1a237e;
}

/* ════ ITEMS TABLE ════ */
.items-table{
    width:100%;
    border-collapse:collapse;
    border:1.5px solid #000;
    border-top:none;
    flex:1;                   /* fills all remaining vertical space */
    table-layout:fixed;
}
/* Column widths */
.c-no  { width:16mm; }
.c-qty { width:22mm; }
.c-desc{ }                    /* takes remaining width */

/* Column headers */
.col-header th{
    padding:2mm 2mm;
    font-size:8pt;
    font-weight:700;
    text-transform:uppercase;
    text-align:center;
    background:#1a237e;
    color:#fff;
    border:1px solid #000;
    letter-spacing:.04em;
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
}

/* Subject row (issuance type) */
.subject-row td{
    padding:1.5mm 2mm;
}
.subject-row td.c-no,
.subject-row td.c-qty{
    border:none !important;
    background:#fff;
}
.c-subject{
    font-size:9.5pt;
    font-weight:700;
    text-align:center;
    text-transform:uppercase;
    letter-spacing:.03em;
    border:1px solid #ccc !important;
}

/* Data + filler rows */
.data-row td{
    border-bottom:1px solid #e0e0e0;
    border-right:1px solid #ccc;
    vertical-align:middle;
    height:9mm;                /* even row height like the sample */
}
.data-row td:last-child{border-right:none;}
.c-no {text-align:center; font-size:9.5pt; border-right:1px solid #000 !important;}
.c-qty{text-align:center; font-size:9.5pt; font-weight:600; border-right:1px solid #000 !important; white-space:nowrap;}
.c-desc{text-align:center; font-size:9.5pt; padding:1.5mm 3mm;}

/* ════ BOTTOM SECTION ════ */
.bottom-table{
    width:100%;
    border-collapse:collapse;
    border:1.5px solid #000;
    border-top:1.5px solid #000;
    flex-shrink:0;
}
.bottom-table td{
    border:1px solid #ccc;
    vertical-align:middle;
    padding:1.8mm 3mm;
    font-size:9.5pt;
}
.b-label{
    width:42mm;
    font-weight:700;
    font-size:9pt;
    border-right:1.5px solid #000;
    white-space:nowrap;
}
.b-hint{font-weight:400;font-style:italic;font-size:8pt;}
.b-value{text-align:center;}
.b-bold{font-weight:700;text-transform:uppercase;letter-spacing:.04em;font-size:9.5pt;}
.b-sig{padding:0 3mm 2mm;}
.sig-space{height:10mm;}
.sig-line-wrap{display:flex;flex-direction:column;align-items:center;width:65%;margin:0 auto;}
.sig-line{width:100%;border-bottom:1.5px solid #000;}
.sig-line-label{font-size:7.5pt;color:#555;margin-top:1mm;text-align:center;}
.date-line{width:55%;margin:2mm auto;border-bottom:1.5px solid #000;height:8mm;}

/* ════ ADDRESS FOOTER ════ */
.addr-footer{
    flex-shrink:0;
    text-align:center;
    font-size:7.5pt;
    color:#555;
    padding-top:2mm;
    border-top:1px solid #ccc;
    margin-top:auto;
    line-height:1.6;
}

/* ════ PRINT ════ */
@media print{
    @page{size:A4 portrait;margin:0;}
    html,body{width:210mm;height:297mm;background:#fff !important;overflow:hidden;}
    .toolbar{display:none !important;}
    .pages{padding:0;background:#fff;}
    .a4{width:210mm;height:297mm;box-shadow:none;border-radius:0;page-break-after:avoid;page-break-inside:avoid;}
    .page{border-color:#b0bec5;}
    .col-header th,.dept-row,.meta-table,.items-table,.bottom-table{
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }
}
</style>
</head>
<body>

<!-- Screen toolbar -->
<div class="toolbar">
    <div class="tbar-l">
        <div>
            <div class="tbar-title">📮 {$safeTitle}</div>
            <div class="tbar-sub">Generated {$genTime}</div>
        </div>
        <div class="tbar-badge">{$totalItems} line(s) &nbsp;·&nbsp; {$grandTotal} total pcs</div>
    </div>
    <div class="tbar-r">
        <button class="btn btn-blue" onclick="window.print()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print
        </button>
        <button class="btn btn-ghost" onclick="window.close()">✕ Close</button>
    </div>
</div>

<!-- A4 page -->
<div class="pages">
    <div class="a4">
        {$page}
    </div>
</div>

</body>
</html>
HTML;
    }
}
<?php

namespace App\Services;

use App\Models\UniformIssuance;
use App\Models\UniformIssuanceLog;
use App\Models\UniformIssuanceRecipient;

class ReceivingCopyService
{
    // ── Company constants — edit these ────────────────────────────────────────
    private const COMPANY_NAME    = 'STRONGLINK SERVICES';
    private const COMPANY_NAME_FULL = 'Stronglink Services Inc.';
    private const COMPANY_ADDRESS = 'RL Bldg. Francisco Village, Brgy. Pulong Sta. Cruz, Sta. Rosa, Laguna';
    private const COMPANY_PHONE   = '(049) 543-9544';

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC ENTRY POINTS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Full issuance (issued/returned) — all recipients on one print document.
     */
    public static function generate(
        UniformIssuance $issuance,
        ?UniformIssuanceRecipient $recipient = null
    ): string {
        $issuance->loadMissing('site', 'issuanceType', 'recipients.position', 'recipients.items.item');

        $recipients = $recipient
            ? collect([$recipient])
            : $issuance->recipients;

        $slips = $recipients->map(fn ($rec) => self::buildSlipFromRecipient($issuance, $rec))->all();
        $title = 'Receiving Copy' . ($issuance->site ? ' — ' . $issuance->site->name : '');

        $isSalaryDeduct = (bool) $issuance->issuanceType?->is_salary_deduct;

        return self::wrapDocument($slips, $title, $isSalaryDeduct);
    }

    /**
     * Single log entry — one receiving copy for that specific partial/issued release.
     */
    public static function generateFromLog(UniformIssuanceLog $log): string
    {
        $issuance = $log->uniformIssuance;
        $issuance->loadMissing('site', 'issuanceType');

        $slips = self::buildSlipsFromLogSnapshot($issuance, $log);

        $title = 'Receiving Copy'
               . ($issuance->site ? ' — ' . $issuance->site->name : '');

        $isSalaryDeduct = (bool) $issuance->issuanceType?->is_salary_deduct;

        return self::wrapDocument($slips, $title, $isSalaryDeduct);
    }

    /**
     * All release logs for one issuance — one document with all batches.
     */
    public static function generateAllLogs(UniformIssuance $issuance): string
    {
        $issuance->loadMissing('site', 'issuanceType', 'logs');

        $logs = $issuance->logs
            ->whereIn('action', ['partial', 'issued'])
            ->filter(fn ($log) => ! empty($log->note) && self::isJsonSnapshot($log->note))
            ->values();

        $slips = [];
        foreach ($logs as $log) {
            foreach (self::buildSlipsFromLogSnapshot($issuance, $log) as $slip) {
                $slips[] = $slip;
            }
        }

        // Fallback: if no log snapshots exist, use current item data
        if (empty($slips)) {
            $issuance->loadMissing('recipients.position', 'recipients.items.item');
            foreach ($issuance->recipients as $rec) {
                $slips[] = self::buildSlipFromRecipient($issuance, $rec);
            }
        }

        $title          = 'Receiving Copy — ' . ($issuance->site?->name ?? 'All Releases');
        $isSalaryDeduct = (bool) $issuance->issuanceType?->is_salary_deduct;

        return self::wrapDocument($slips, $title, $isSalaryDeduct);
    }

    /**
     * Bulk across multiple issuances — all recipients, 2 slips per A4 page.
     */
    public static function generateBulk(iterable $issuances): string
    {
        $slips = [];

        foreach ($issuances as $issuance) {
            $issuance->loadMissing('site', 'issuanceType', 'logs');

            $isSalaryDeduct = (bool) $issuance->issuanceType?->is_salary_deduct;
            $isPartial      = $issuance->status === 'partial';

            if ($isPartial) {
                $logs = $issuance->logs
                    ->whereIn('action', ['partial', 'issued'])
                    ->filter(fn ($log) => ! empty($log->note) && self::isJsonSnapshot($log->note))
                    ->values();

                if ($logs->isNotEmpty()) {
                    foreach ($logs as $log) {
                        foreach (self::buildSlipsFromLogSnapshot($issuance, $log) as $slip) {
                            $slip['is_salary_deduct'] = $isSalaryDeduct;
                            $slips[] = $slip;
                        }
                    }
                    continue;
                }
            }

            $issuance->loadMissing('recipients.position', 'recipients.items.item');
            foreach ($issuance->recipients as $rec) {
                $slip                   = self::buildSlipFromRecipient($issuance, $rec);
                $slip['is_salary_deduct'] = $isSalaryDeduct;
                $slips[] = $slip;
            }
        }

        return self::wrapDocument($slips, 'Bulk Receiving Copy', false);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SLIP DATA BUILDERS
    // ─────────────────────────────────────────────────────────────────────────

    private static function buildSlipFromRecipient(UniformIssuance $issuance, UniformIssuanceRecipient $rec): array
    {
        $items = [];
        foreach ($rec->items as $item) {
            $qty = (int) ($item->released_quantity ?: $item->quantity);
            if ($qty <= 0) continue;
            $items[] = [
                'name' => $item->item?->name ?? "Item #{$item->item_id}",
                'size' => $item->size ?? '—',
                'qty'  => $qty,
            ];
        }

        return [
            'txn_id'          => $rec->transaction_id ?? '—',
            'date'            => $issuance->issued_at
                                    ? \Carbon\Carbon::parse($issuance->issued_at)->format('M d, Y')
                                    : now()->format('M d, Y'),
            'employee'        => $rec->employee_name ?? 'Unknown Employee',
            'position'        => $rec->position?->name ?? '—',
            'site'            => $issuance->site?->name ?? '—',
            'issuance_type'   => $issuance->issuanceType?->name ?? '—',
            'is_salary_deduct'=> (bool) $issuance->issuanceType?->is_salary_deduct,
            'items'           => $items,
        ];
    }

    private static function buildSlipsFromLogSnapshot(UniformIssuance $issuance, UniformIssuanceLog $log): array
    {
        $snapshot = json_decode($log->note, true);
        if (! is_array($snapshot)) return [];

        $logDate = \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y');

        $byEmployee = [];
        foreach ($snapshot as $row) {
            $label    = $row['label'] ?? '';
            $released = (int) ($row['released'] ?? 0);
            if ($released <= 0) continue;

            $parts        = explode(' — ', $label, 2);
            $itemPart     = trim($parts[0] ?? $label);
            $employeeName = trim($parts[1] ?? 'Unknown Employee');

            $itemName = $itemPart;
            $size     = '—';
            if (preg_match('/^(.+?)\s*\(([^)]+)\)$/', $itemPart, $m)) {
                $itemName = trim($m[1]);
                $size     = trim($m[2]);
            }

            if (! isset($byEmployee[$employeeName])) {
                $byEmployee[$employeeName] = [];
            }

            $byEmployee[$employeeName][] = [
                'name' => $itemName,
                'size' => $size,
                'qty'  => $released,
            ];
        }

        $issuance->loadMissing('recipients.position');
        $recipientsByName = $issuance->recipients->keyBy(fn ($r) => $r->employee_name);

        $isSalaryDeduct = (bool) $issuance->issuanceType?->is_salary_deduct;

        $slips = [];
        foreach ($byEmployee as $employeeName => $items) {
            $recipient = $recipientsByName->first(
                fn ($r) => strtolower(trim($r->employee_name)) === strtolower(trim($employeeName))
            );

            $slips[] = [
                'txn_id'          => $recipient?->transaction_id ?? '—',
                'date'            => $logDate,
                'employee'        => $employeeName,
                'position'        => $recipient?->position?->name ?? '—',
                'site'            => $issuance->site?->name ?? '—',
                'issuance_type'   => $issuance->issuanceType?->name ?? '—',
                'is_salary_deduct'=> $isSalaryDeduct,
                'items'           => $items,
            ];
        }

        return $slips;
    }

    private static function isJsonSnapshot(string $note): bool
    {
        if (! str_starts_with(trim($note), '[')) return false;
        $decoded = json_decode($note, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECEIVING COPY SLIP RENDERER
    // ─────────────────────────────────────────────────────────────────────────

    private static function renderSlip(array $d): string
    {
        $employee     = e($d['employee']);
        $position     = e($d['position']);
        $site         = e($d['site']);
        $issuanceType = e($d['issuance_type']);
        $txnId        = e($d['txn_id']);
        $date         = e($d['date']);
        $cn           = e(self::COMPANY_NAME);
        $addr         = e(self::COMPANY_ADDRESS);
        $phone        = e(self::COMPANY_PHONE);

        // Item rows + filler
        $itemRows   = '';
        $grandTotal = 0;
        foreach ($d['items'] as $i => $item) {
            $no   = $i + 1;
            $name = e($item['name']);
            $size = e($item['size']);
            $qty  = (int) $item['qty'];
            $grandTotal += $qty;
            $bg   = $i % 2 === 0 ? '#ffffff' : '#f8fafc';
            $itemRows .= "
                <tr style='background:{$bg};'>
                    <td class='tc nb' style='width:24px;font-size:10px;color:#9ca3af;'>{$no}</td>
                    <td class='tl nb' style='font-size:11px;color:#111827;font-weight:500;'>{$name}</td>
                    <td class='tc nb' style='width:44px;font-size:11px;color:#374151;'>{$size}</td>
                    <td class='tc nb' style='width:32px;font-size:12px;font-weight:800;color:#1d4ed8;'>{$qty}</td>
                    <td class='tl nb' style='width:62px;'>&nbsp;</td>
                </tr>";
        }
        for ($f = count($d['items']); $f < 5; $f++) {
            $bg = $f % 2 === 0 ? '#ffffff' : '#f8fafc';
            $itemRows .= "
                <tr style='background:{$bg};'>
                    <td class='tc nb' style='color:transparent;font-size:10px;'>0</td>
                    <td class='tl nb'>&nbsp;</td>
                    <td class='tc nb'>&nbsp;</td>
                    <td class='tc nb'>&nbsp;</td>
                    <td class='tl nb'>&nbsp;</td>
                </tr>";
        }

        return "
        <div class='slip'>
            <!-- HEADER -->
            <div class='slip-head'>
                <div class='slip-brand'>
                    <div class='slip-logo'>
                        <svg width='26' height='26' viewBox='0 0 24 24' fill='none' stroke='#fff' stroke-width='1.6'>
                            <path d='M12 2L2 7l10 5 10-5-10-5z'/><path d='M2 17l10 5 10-5'/><path d='M2 12l10 5 10-5'/>
                        </svg>
                    </div>
                    <div>
                        <div class='brand-name'>{$cn}</div>
                        <div class='brand-addr'>{$addr}</div>
                        <div class='brand-phone'>{$phone}</div>
                    </div>
                </div>
                <div class='slip-head-right'>
                    <div class='slip-doc-type'>UNIFORM ISSUANCE RECEIPT</div>
                    <div class='slip-txn'>TXN# <span>{$txnId}</span></div>
                </div>
            </div>

            <!-- META -->
            <div class='slip-meta'>
                <table style='width:100%;border-collapse:collapse;'>
                    <tr>
                        <td style='width:60%;padding:2.5px 0;'>
                            <span class='ml'>Name:</span>
                            <span class='mv'>{$employee}</span>
                        </td>
                        <td style='text-align:right;padding:2.5px 0;'>
                            <span class='ml'>Date:</span>
                            <span class='mv'>{$date}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:2.5px 0;'>
                            <span class='ml'>Assigned at:</span>
                            <span class='mv'>{$site}</span>
                        </td>
                        <td style='text-align:right;padding:2.5px 0;'>
                            <span class='ml'>Type:</span>
                            <span class='mv'>{$issuanceType}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2' style='padding:2.5px 0;'>
                            <span class='ml'>Position:</span>
                            <span class='mv'>{$position}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ITEMS TABLE -->
            <div class='slip-items'>
                <table style='width:100%;border-collapse:collapse;border:1px solid #cbd5e1;'>
                    <thead>
                        <tr>
                            <th class='th-dark tc' style='width:24px;'>#</th>
                            <th class='th-dark tl'>Description of Item</th>
                            <th class='th-dark tc' style='width:44px;'>Size</th>
                            <th class='th-dark tc' style='width:32px;color:#93c5fd;'>Qty</th>
                            <th class='th-dark tl' style='width:62px;color:#94a3b8;'>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>{$itemRows}</tbody>
                    <tfoot>
                        <tr style='background:#eff6ff;border-top:2px solid #93c5fd;'>
                            <td colspan='3' class='tr' style='padding:4px 8px;font-size:10px;font-weight:700;color:#374151;border-right:1px solid #cbd5e1;'>TOTAL ITEMS RECEIVED:</td>
                            <td class='tc' style='padding:4px 8px;font-size:14px;font-weight:900;color:#1d4ed8;border-right:1px solid #cbd5e1;'>{$grandTotal}</td>
                            <td style='padding:4px 8px;'>&nbsp;</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SIGNATURES -->
            <div class='slip-sigs'>
                <div class='sig'>
                    <div class='sig-space'></div>
                    <div class='sig-pre'>{$employee}</div>
                    <div class='sig-line'></div>
                    <div class='sig-lbl'>Signature over Printed Name</div>
                </div>
                <div class='sig-sep'></div>
                <div class='sig'>
                    <div class='sig-space'></div>
                    <div class='sig-pre'>&nbsp;</div>
                    <div class='sig-line'></div>
                    <div class='sig-lbl'>Date Received</div>
                </div>
            </div>

            <!-- SLIP FOOTER -->
            <div class='slip-foot'>
                This document serves as the official Receiving Copy and shall be retained by the company for documentation purposes.
            </div>
        </div>";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTHORITY TO DEDUCT SLIP RENDERER
    // ─────────────────────────────────────────────────────────────────────────

    private static function renderAtd(array $d): string
    {
        $employee     = e($d['employee']);
        $site         = e($d['site']);
        $issuanceType = e($d['issuance_type']);
        $cn           = e(self::COMPANY_NAME);
        $cnFull       = e(self::COMPANY_NAME_FULL);
        $addr         = e(self::COMPANY_ADDRESS);
        $phone        = e(self::COMPANY_PHONE);

        // Build item description list for the blank: "Uniform items issued to me"
        $itemDescriptions = collect($d['items'])->map(fn ($i) =>
            e($i['name']) . ' (' . e($i['size']) . ')'
        )->implode(', ');

        return "
        <div class='slip atd-slip'>
            <!-- ATD HEADER -->
            <div class='atd-head'>
                <div class='slip-logo' style='width:38px;height:38px;'>
                    <svg width='22' height='22' viewBox='0 0 24 24' fill='none' stroke='#fff' stroke-width='1.6'>
                        <path d='M12 2L2 7l10 5 10-5-10-5z'/><path d='M2 17l10 5 10-5'/><path d='M2 12l10 5 10-5'/>
                    </svg>
                </div>
                <div style='text-align:center;flex:1;'>
                    <div style='font-size:13px;font-weight:900;color:#1e3a5f;letter-spacing:.05em;text-transform:uppercase;'>{$cn}</div>
                    <div style='font-size:8px;color:#64748b;margin-top:1px;'>{$addr}</div>
                    <div style='font-size:8px;color:#64748b;'>Tel: {$phone}</div>
                </div>
                <div style='width:38px;'></div>
            </div>

            <!-- ATD TITLE -->
            <div class='atd-title-wrap'>
                <div class='atd-title'>AUTHORITY TO DEDUCT</div>
                <div class='atd-underline'></div>
            </div>

            <!-- ATD BODY -->
            <div class='atd-body'>
                <p class='atd-para'>
                    I,&nbsp;
                    <span class='atd-blank atd-name'>{$employee}</span>,
                    &nbsp;an employee of <strong>{$cnFull}</strong>, assigned at&nbsp;
                    <span class='atd-blank atd-site'>{$site}</span>,
                    &nbsp;DO HEREBY AUTHORIZE SSI to deduct the amount of&nbsp;
                    <strong>PHP</strong>&nbsp;<span class='atd-blank atd-amount'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;peso,
                    for the cost of&nbsp;
                    <span class='atd-blank atd-items'>{$itemDescriptions}</span>
                    &nbsp;issued to me.
                </p>
            </div>

            <!-- ATD SIGNATURES -->
            <div class='atd-sigs'>
                <div class='atd-sig'>
                    <div class='atd-sig-space'></div>
                    <div class='atd-sig-name'>{$employee}</div>
                    <div class='atd-sig-line'></div>
                    <div class='atd-sig-lbl'>Signature Over Printed Name</div>
                </div>
                <div style='width:48px;flex-shrink:0;'></div>
                <div class='atd-sig' style='max-width:160px;'>
                    <div class='atd-sig-space'></div>
                    <div class='atd-sig-name'>&nbsp;</div>
                    <div class='atd-sig-line'></div>
                    <div class='atd-sig-lbl'>Date</div>
                </div>
            </div>
        </div>";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAIR SLIPS INTO A4 PAGES
    // Each "unit" is:
    //   - receiving copy slip
    //   - [cut line]
    //   - ATD slip (only if is_salary_deduct)
    //   - [cut line between units if needed]
    // Two units per A4 page.
    // ─────────────────────────────────────────────────────────────────────────

    private static function buildPages(array $slips, bool $globalSalaryDeduct = false): string
    {
        $html       = '';
        $nonSdQueue = [];
        $pages      = [];

        foreach ($slips as $slip) {
            $isSd = $globalSalaryDeduct || (bool) ($slip['is_salary_deduct'] ?? false);

            if ($isSd) {
                // Flush any pending non-SD slips first
                foreach (array_chunk($nonSdQueue, 2) as $pair) {
                    $pages[] = ['type' => 'non-sd', 'pair' => $pair];
                }
                $nonSdQueue = [];
                $pages[] = ['type' => 'sd', 'slip' => $slip];
            } else {
                $nonSdQueue[] = $slip;
            }
        }

        // Flush remaining non-SD slips
        foreach (array_chunk($nonSdQueue, 2) as $pair) {
            $pages[] = ['type' => 'non-sd', 'pair' => $pair];
        }

        $total = count($pages);

        foreach ($pages as $idx => $page) {
            $pbStyle = ($idx < $total - 1) ? "page-break-after:always;" : "";

            if ($page['type'] === 'sd') {
                // Full page: RC on top half, blue cut line, ATD on bottom half
                $slip    = $page['slip'];
                $rcHalf  = "<div class='page-half'>" . self::renderSlip($slip) . "</div>";
                $cut     = self::renderCutLine('✂ &nbsp; CUT HERE &nbsp; ✂', '#bfdbfe', 'ATD');
                $atdHalf = "<div class='page-half'>" . self::renderAtd($slip) . "</div>";
                $html   .= "<div class='a4' style='{$pbStyle}'>{$rcHalf}{$cut}{$atdHalf}</div>";
            } else {
                // Two non-SD slips per page, each in their own half
                $pair    = $page['pair'];
                $topHalf = "<div class='page-half'>" . self::renderSlip($pair[0]) . "</div>";
                $botHalf = '';
                $cut     = '';
                if (isset($pair[1])) {
                    $cut     = self::renderCutLine();
                    $botHalf = "<div class='page-half'>" . self::renderSlip($pair[1]) . "</div>";
                }
                $html .= "<div class='a4' style='{$pbStyle}'>{$topHalf}{$cut}{$botHalf}</div>";
            }
        }

        return $html;
    }

    private static function renderCutLine(
        string $label = '✂&nbsp;&nbsp;CUT HERE&nbsp;&nbsp;✂',
        string $color = '#94a3b8',
        string $type  = ''
    ): string {
        $style = $type === 'ATD'
            ? "border-top:1.5px dashed {$color};"
            : "border-top:1px dashed {$color};";

        return "
            <div class='cut-line'>
                <div class='cut-dash' style='{$style}'></div>
                <div class='cut-txt' style='color:{$color};'>{$label}</div>
                <div class='cut-dash' style='{$style}'></div>
            </div>";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FULL HTML DOCUMENT
    // ─────────────────────────────────────────────────────────────────────────

    private static function wrapDocument(array $slips, string $title, bool $isSalaryDeduct = false): string
    {
        $count     = count($slips);
        $pages     = self::buildPages($slips, $isSalaryDeduct);
        $genTime   = now()->timezone('Asia/Manila')->format('M d, Y h:i A');
        $safeTitle = e($title);
        $atdNote   = $isSalaryDeduct ? " &nbsp;+&nbsp; {$count} ATD slip(s)" : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$safeTitle}</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#dde3ea;color:#111827;}

/* ── TOOLBAR ── */
.toolbar{
    position:fixed;top:0;left:0;right:0;z-index:9999;
    background:#1e3a5f;
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 24px;box-shadow:0 2px 12px rgba(0,0,0,.35);
}
.tbar-l{display:flex;align-items:center;gap:14px;}
.tbar-title{font-size:14px;font-weight:800;color:#fff;letter-spacing:.02em;}
.tbar-sub{font-size:10px;color:#94a3b8;margin-top:1px;}
.tbar-badge{background:#3b82f6;color:#fff;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;}
.tbar-r{display:flex;gap:8px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s;}
.btn:hover{opacity:.85;}
.btn-blue{background:#2563eb;color:#fff;}
.btn-ghost{background:rgba(255,255,255,.1);color:#e2e8f0;border:1px solid rgba(255,255,255,.2);}

/* ── PAGE LAYOUT ── */
.pages{padding:72px 24px 40px;display:flex;flex-direction:column;align-items:center;gap:28px;}
.a4{
    width:210mm;height:297mm;
    background:#fff;box-shadow:0 6px 24px rgba(0,0,0,.18);
    border-radius:3px;
    display:flex;flex-direction:column;
    overflow:hidden;position:relative;
}

/* Each unit occupies exactly half the A4 page */
.page-half{
    width:100%;
    height:148.5mm;
    flex-shrink:0;
    overflow:hidden;
    display:flex;
    flex-direction:column;
}

/* ── RECEIVING COPY SLIP ── */
.slip{width:100%;padding:6mm 8mm 4mm;display:flex;flex-direction:column;gap:0;overflow:hidden;flex-shrink:0;}
.slip-head{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:5px;border-bottom:2.5px solid #1e3a5f;margin-bottom:4px;flex-shrink:0;}
.slip-brand{display:flex;align-items:center;gap:9px;}
.slip-logo{width:42px;height:42px;background:#1e3a5f;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.brand-name{font-size:13px;font-weight:900;color:#1e3a5f;letter-spacing:.04em;text-transform:uppercase;}
.brand-addr{font-size:8.5px;color:#64748b;margin-top:1px;font-weight:500;}
.brand-phone{font-size:8.5px;color:#64748b;}
.slip-head-right{text-align:right;flex-shrink:0;}
.slip-doc-type{font-size:8.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.1em;}
.slip-txn{margin-top:4px;background:#1e3a5f;color:#fff;font-size:10px;font-weight:800;padding:3px 11px;border-radius:999px;display:inline-block;letter-spacing:.05em;}
.slip-txn span{color:#93c5fd;}
.slip-meta{flex-shrink:0;padding:4px 0;border-bottom:1px dashed #cbd5e1;margin-bottom:4px;}
.ml{font-size:10px;font-weight:700;color:#475569;margin-right:3px;}
.mv{font-size:10px;color:#111827;}
.slip-items{flex:1;overflow:hidden;margin-bottom:4px;}
.th-dark{padding:5px 8px;background:#1e3a5f;font-size:9px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.05em;border-right:1px solid rgba(255,255,255,.15);}
.th-dark:last-child{border-right:none;}
.nb{padding:3.5px 8px;border-bottom:1px solid #e5e7eb;border-right:1px solid #e5e7eb;}
.nb:last-child{border-right:none;}
.tc{text-align:center;}
.tl{text-align:left;}
.tr{text-align:right;}
.slip-sigs{flex-shrink:0;display:flex;align-items:flex-end;padding-top:6px;border-top:1px solid #e2e8f0;margin-top:auto;}
.sig{flex:1;text-align:center;padding:0 5px;}
.sig-sep{width:1px;background:#e2e8f0;align-self:stretch;}
.sig-space{height:20px;}
.sig-pre{font-size:9px;font-weight:600;color:#374151;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sig-line{border-top:1px solid #374151;margin:0 10px;}
.sig-lbl{font-size:8px;color:#9ca3af;margin-top:2px;}
.slip-foot{flex-shrink:0;font-size:8px;color:#9ca3af;text-align:center;padding-top:3px;border-top:1px dashed #e2e8f0;margin-top:4px;}

/* ── ATD SLIP ── */
.atd-slip{
    background:#fafbff;
    border-top:none;
    padding:5mm 8mm 5mm;
}
.atd-head{
    display:flex;align-items:center;gap:10px;
    padding-bottom:5px;
    border-bottom:2px solid #1e3a5f;
    margin-bottom:6px;
}
.atd-title-wrap{
    text-align:center;
    margin-bottom:8px;
}
.atd-title{
    font-size:14px;
    font-weight:900;
    color:#1e3a5f;
    letter-spacing:.12em;
    text-transform:uppercase;
}
.atd-underline{
    width:60%;
    margin:3px auto 0;
    border-bottom:2px solid #1e3a5f;
}
.atd-body{
    padding:6px 0 10px;
}
.atd-para{
    font-size:11px;
    color:#111827;
    line-height:1.9;
    text-align:justify;
}
.atd-blank{
    display:inline-block;
    border-bottom:1px solid #374151;
    min-width:60px;
    vertical-align:bottom;
    text-align:center;
    font-weight:600;
    color:#1e3a5f;
}
.atd-name { min-width:180px; }
.atd-site { min-width:140px; }
.atd-amount{ min-width:100px; color:#1d4ed8; }
.atd-items { min-width:220px; font-style:italic; }
.atd-sigs{
    display:flex;
    align-items:flex-end;
    padding-top:8px;
    border-top:1px solid #e2e8f0;
    margin-top:4px;
}
.atd-sig{ flex:1; text-align:center; }
.atd-sig-space{ height:24px; }
.atd-sig-name{
    font-size:9px;font-weight:600;color:#374151;
    margin-bottom:2px;white-space:nowrap;
    overflow:hidden;text-overflow:ellipsis;
}
.atd-sig-line{ border-top:1px solid #374151; margin:0 8px; }
.atd-sig-lbl{ font-size:8px; color:#9ca3af; margin-top:2px; }

/* ── CUT LINES ── */
.cut-line{
    display:flex;align-items:center;gap:8px;
    padding:0 8mm;
    height:0;flex-shrink:0;
    position:relative;overflow:visible;
}
.cut-dash{ flex:1; }
.cut-txt{
    font-size:8px;font-weight:700;
    letter-spacing:.1em;white-space:nowrap;
    background:#fff;padding:0 5px;
}

@media print{
    @page{size:A4 portrait;margin:0;}
    body{background:#fff !important;}
    .toolbar{display:none !important;}
    .pages{padding:0;gap:0;background:#fff;}
    .a4{width:210mm;height:297mm;box-shadow:none;border-radius:0;}
    .page-half{height:148.5mm;}
    .slip-logo,.slip-head,.th-dark,tfoot tr,.atd-head{
        -webkit-print-color-adjust:exact;print-color-adjust:exact;
    }
}
</style>
</head>
<body>
<div class="toolbar">
    <div class="tbar-l">
        <div>
            <div class="tbar-title">📄 {$safeTitle}</div>
            <div class="tbar-sub">Generated {$genTime}</div>
        </div>
        <div class="tbar-badge">{$count} receiving slip(s){$atdNote}</div>
    </div>
    <div class="tbar-r">
        <button class="btn btn-blue" onclick="window.print()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print All ({$count} slips)
        </button>
        <button class="btn btn-ghost" onclick="window.close()">✕ Close</button>
    </div>
</div>
<div class="pages">{$pages}</div>
</body>
</html>
HTML;
    }
}
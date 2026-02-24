<?php

namespace App\Filament\Resources\Items\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Category;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ItemsTable
{
    // -------------------------------------------------------------------------
    // Permission Helper
    // -------------------------------------------------------------------------

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // -------------------------------------------------------------------------
    // Table Configuration
    // -------------------------------------------------------------------------

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->searchable(),

                TextColumn::make('description')
                    ->limit(40)
                    ->placeholder('No Description'),

                TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->getStateUsing(
                        fn ($record) => $record->itemVariants->sum('quantity')
                    ),
            ])
            ->filters([])
            ->recordActions([
                Action::make('viewVariants')
                    ->label('Variants')
                    ->icon('heroicon-o-eye')
                    ->visible(fn () => self::userCan('view-any item'))
                    ->modalHeading(fn ($record) => "Variants — {$record->name}")
                    ->modalContent(fn ($record) => new HtmlString(
                        $record->itemVariants->isEmpty()
                            ? '<p style="text-align:center;padding:24px;color:#9ca3af;">No variants found.</p>'
                            : '<table style="width:100%;border-collapse:collapse;font-size:14px;">
                                <thead>
                                    <tr style="border-bottom:2px solid #374151;">
                                        <th style="padding:10px 12px;text-align:left;color:#9ca3af;font-weight:600;">Size</th>
                                        <th style="padding:10px 12px;text-align:right;color:#9ca3af;font-weight:600;">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>' .
                                    $record->itemVariants->map(fn ($variant) =>
                                        '<tr style="border-bottom:1px solid #1f2937;">
                                            <td style="padding:10px 12px;color:#f9fafb;">' . e($variant->size_label) . '</td>
                                            <td style="padding:10px 12px;text-align:right;color:#f9fafb;font-weight:600;">' . e($variant->quantity) . '</td>
                                        </tr>'
                                    )->join('') .
                                '</tbody>
                                <tfoot>
                                    <tr style="border-top:2px solid #374151;">
                                        <td style="padding:10px 12px;color:#9ca3af;font-weight:600;">Total</td>
                                        <td style="padding:10px 12px;text-align:right;color:#f9fafb;font-weight:700;">' . $record->itemVariants->sum('quantity') . '</td>
                                    </tr>
                                </tfoot>
                            </table>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                EditAction::make()
                    ->visible(fn () => self::userCan('update item')),

                DeleteAction::make()
                    ->visible(fn () => self::userCan('delete item')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => self::userCan('delete item')),
                ]),

            Action::make('export_items')
                ->label('Export Items')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => self::userCan('view-any item'))
                ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                    $tenant = Filament::getTenant();

                    $items = Item::whereHas('department', fn ($q) =>
                        $q->where('departments.id', $tenant->id)
                    )
                    ->with(['category', 'itemVariants'])
                    ->get();

                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet       = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Items Export');

                    // ── Styles ─────────────────────────────────────────────────────
                    $styleHeader = [
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11, 'name' => 'Arial'],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
                    ];

                    $styleSubHeader = [
                        'font'      => ['bold' => false, 'color' => ['rgb' => '93C5FD'], 'size' => 8, 'name' => 'Arial', 'italic' => true],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
                    ];

                    $styleEven = [
                        'font'      => ['color' => ['rgb' => '374151'], 'size' => 10, 'name' => 'Arial'],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EBF4FF']],
                        'alignment' => ['vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
                    ];

                    $styleOdd = [
                        'font'      => ['color' => ['rgb' => '374151'], 'size' => 10, 'name' => 'Arial'],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFFFF']],
                        'alignment' => ['vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
                    ];

                    // ── Title row ──────────────────────────────────────────────────
                    $sheet->mergeCells('A1:G1');
                    $sheet->setCellValue('A1', 'ITEMS EXPORT — ' . strtoupper($tenant->name ?? 'ALL') . ' — ' . now()->format('M d, Y'));
                    $sheet->getStyle('A1')->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13, 'name' => 'Arial'],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(30);

                    // ── Column headers row 2 ───────────────────────────────────────
                    $headers = ['item_name', 'category', 'description', 'size_label', 'quantity', 'stock_status', 'total_qty'];
                    foreach ($headers as $col => $header) {
                        $sheet->getCell([$col + 1, 2])->setValue($header);
                    }
                    $sheet->getStyle('A2:G2')->applyFromArray($styleHeader);
                    $sheet->getRowDimension(2)->setRowHeight(22);

                    // ── Sub-header hints row 3 ─────────────────────────────────────
                    $hints = ['Item name', 'Category', 'Description', 'Size / Variant', 'Stock qty', 'Stock status', 'Total across sizes'];
                    foreach ($hints as $col => $hint) {
                        $sheet->getCell([$col + 1, 3])->setValue($hint);
                    }
                    $sheet->getStyle('A3:G3')->applyFromArray($styleSubHeader);
                    $sheet->getRowDimension(3)->setRowHeight(15);

                    // ── Data rows ──────────────────────────────────────────────────
                    $currentRow = 4;
                    $rowParity  = 0;

                    foreach ($items as $item) {
                        $totalQty     = $item->itemVariants->sum('quantity');
                        $variantCount = $item->itemVariants->count();
                        $style        = $rowParity % 2 === 0 ? $styleEven : $styleOdd;

                        if ($item->itemVariants->isEmpty()) {
                            $sheet->getCell([1, $currentRow])->setValue($item->name);
                            $sheet->getCell([2, $currentRow])->setValue($item->category?->name ?? '—');
                            $sheet->getCell([3, $currentRow])->setValue($item->description ?? '');
                            $sheet->getCell([4, $currentRow])->setValue('—');
                            $sheet->getCell([5, $currentRow])->setValue(0);
                            $sheet->getCell([6, $currentRow])->setValue('No Variants');
                            $sheet->getCell([7, $currentRow])->setValue(0);
                            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($style);
                            $sheet->getRowDimension($currentRow)->setRowHeight(18);
                            $currentRow++;
                            $rowParity++;
                            continue;
                        }

                        $firstVariant = true;
                        foreach ($item->itemVariants as $variant) {
                            $sheet->getCell([1, $currentRow])->setValue($firstVariant ? $item->name : '');
                            $sheet->getCell([2, $currentRow])->setValue($firstVariant ? ($item->category?->name ?? '—') : '');
                            $sheet->getCell([3, $currentRow])->setValue($firstVariant ? ($item->description ?? '') : '');
                            $sheet->getCell([4, $currentRow])->setValue($variant->size_label);
                            $sheet->getCell([5, $currentRow])->setValue($variant->quantity);
                            $sheet->getCell([6, $currentRow])->setValue($variant->stock_status);
                            $sheet->getCell([7, $currentRow])->setValue($firstVariant ? $totalQty : '');

                            $statusColor = match ($variant->stock_status) {
                                'Out of Stock' => ['font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true]],
                                'Low Stock'    => ['font' => ['color' => ['rgb' => 'D97706'], 'bold' => true]],
                                default        => ['font' => ['color' => ['rgb' => '059669'], 'bold' => true]],
                            };

                            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($style);
                            $sheet->getStyle("F{$currentRow}")->applyFromArray($statusColor);
                            $sheet->getRowDimension($currentRow)->setRowHeight(18);

                            $currentRow++;
                            $firstVariant = false;
                        }

                        // Merge item-level cells across variant rows
                        if ($variantCount > 1) {
                            $startRow = $currentRow - $variantCount;
                            $endRow   = $currentRow - 1;

                            foreach ([1, 2, 3, 7] as $col) {
                                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                                $sheet->mergeCells("{$colLetter}{$startRow}:{$colLetter}{$endRow}");
                                $sheet->getStyle("{$colLetter}{$startRow}")->getAlignment()
                                    ->setVertical('center')
                                    ->setWrapText(true);
                            }
                        }

                        $rowParity++;
                    }

                    // ── Summary row ────────────────────────────────────────────────
                    $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                    $sheet->setCellValue(
                        "A{$currentRow}",
                        'Total Items: ' . $items->count() .
                        '   |   Total Variants: ' . $items->sum(fn ($i) => $i->itemVariants->count()) .
                        '   |   Total Stock: '    . $items->sum(fn ($i) => $i->itemVariants->sum('quantity'))
                    );
                    $sheet->getCell([7, $currentRow])->setValue(
                        $items->sum(fn ($i) => $i->itemVariants->sum('quantity'))
                    );
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                        'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '93C5FD']]],
                    ]);
                    $sheet->getRowDimension($currentRow)->setRowHeight(22);

                    // ── Column widths ──────────────────────────────────────────────
                    $widths = [28, 18, 36, 14, 12, 16, 14];
                    foreach ($widths as $col => $width) {
                        $sheet->getColumnDimensionByColumn($col + 1)->setWidth($width);
                    }

                    $sheet->freezePane('A4');

                    // ── Stream download ────────────────────────────────────────────
                    $filename = 'items_export_' . now()->format('Ymd_His') . '.xlsx';
                    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                    return response()->streamDownload(function () use ($writer) {
                        $writer->save('php://output');
                    }, $filename, [
                        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }),
                // ── DOWNLOAD TEMPLATE ─────────────────────────────────────────
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $templatePath = public_path('templates/items_import_template.xlsx');

                        if (! file_exists($templatePath)) {
                            Notification::make()
                                ->title('Template not found.')
                                ->body('Please ask your administrator to upload the template file.')
                                ->danger()
                                ->send();
                            return;
                        }

                        return response()->download(
                            $templatePath,
                            'items_import_template.xlsx',
                            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                        );
                    }),

                // ── IMPORT EXCEL ──────────────────────────────────────────────
                Action::make('import_items')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn () => self::userCan('create item'))
                    ->modalHeading('Import Items from Excel')
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Import')
                    ->form([
                        Placeholder::make('instructions')
                            ->label('')
                            ->content(new HtmlString("
                                <div style='
                                    background:#eff6ff;
                                    border:1px solid #bfdbfe;
                                    border-radius:8px;
                                    padding:14px 16px;
                                    font-size:13px;
                                    color:#1e40af;
                                    line-height:1.7;
                                '>
                                    <div style='font-weight:700;margin-bottom:6px;font-size:14px;'>📋 Before you import:</div>
                                    <ul style='margin:0;padding-left:18px;'>
                                        <li>Download the template using the <strong>Download Template</strong> button.</li>
                                        <li>Each <strong>row</strong> = one size variant of an item.</li>
                                        <li>Repeat the item name across rows to add multiple sizes.</li>
                                        <li><strong>category</strong> must match an existing category in the system.</li>
                                        <li>Unknown categories will be <strong>skipped</strong>.</li>
                                        <li>If an item already exists, new variants will be <strong>added</strong> to it.</li>
                                        <li>Duplicate size variants for the same item will be <strong>merged</strong> (quantities added).</li>
                                    </ul>
                                </div>
                            ")),

                        FileUpload::make('excel_file')
                            ->label('Excel File (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->storeFiles(true)
                            ->disk('local')
                            ->directory('imports/items'),
                    ])
                    ->action(function (array $data): void {
                        $tenant = Filament::getTenant();
                        if (! $tenant) {
                            Notification::make()->title('No tenant found.')->danger()->send();
                            return;
                        }

                        $filePath = $data['excel_file'];
                        $fullPath = Storage::disk('local')->path($filePath);

                        if (! file_exists($fullPath)) {
                            Notification::make()->title('File not found.')->danger()->send();
                            return;
                        }

                        try {
                            $spreadsheet = IOFactory::load($fullPath);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to read Excel file.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        $sheet = $spreadsheet->getActiveSheet();
                        $rows  = $sheet->toArray(null, true, true, true);

                        // Find the header row (row 3 in our template)
                        // We detect it by looking for 'item_name' in column A
                        $headerRow  = null;
                        $dataRows   = [];

                        foreach ($rows as $rowIndex => $row) {
                            $cellA = strtolower(trim((string) ($row['A'] ?? '')));
                            if (str_contains($cellA, 'item_name')) {
                                $headerRow = $rowIndex;
                                continue;
                            }
                            if ($headerRow !== null) {
                                $dataRows[] = $row;
                            }
                        }

                        if ($headerRow === null) {
                            Notification::make()
                                ->title('Invalid template format.')
                                ->body('Could not find the header row. Please use the official template.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Load all categories for this tenant
                        $categories = Category::where('department_id', $tenant->id)
                            ->pluck('id', 'name')
                            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
                            ->toArray();

                        $created    = 0;
                        $skipped    = 0;
                        $variantsAdded = 0;
                        $skippedRows   = [];

                        // Group rows by item_name to process together
                        $grouped = [];
                        foreach ($dataRows as $row) {
                            $itemName  = trim((string) ($row['A'] ?? ''));
                            $catName   = trim((string) ($row['B'] ?? ''));
                            $desc      = trim((string) ($row['C'] ?? ''));
                            $sizeLabel = trim((string) ($row['D'] ?? ''));
                            $qty       = $row['E'] ?? 0;

                            // Skip completely empty rows
                            if ($itemName === '' && $catName === '' && $sizeLabel === '') {
                                continue;
                            }

                            // Skip rows missing required fields
                            if ($itemName === '' || $catName === '' || $sizeLabel === '') {
                                $skippedRows[] = "Row skipped — missing required fields (item_name, category, or size_label). Values: [{$itemName}] [{$catName}] [{$sizeLabel}]";
                                $skipped++;
                                continue;
                            }

                            // Validate category
                            $catKey = strtolower($catName);
                            if (! isset($categories[$catKey])) {
                                $skippedRows[] = "Row skipped — unknown category: \"{$catName}\" for item \"{$itemName}\".";
                                $skipped++;
                                continue;
                            }

                            $qty = max(0, (int) $qty);

                            if (! isset($grouped[$itemName])) {
                                $grouped[$itemName] = [
                                    'category_id' => $categories[$catKey],
                                    'description' => $desc,
                                    'variants'    => [],
                                ];
                            }

                            // Merge duplicate size labels for same item
                            $sizeKey = strtolower($sizeLabel);
                            if (isset($grouped[$itemName]['variants'][$sizeKey])) {
                                $grouped[$itemName]['variants'][$sizeKey]['quantity'] += $qty;
                            } else {
                                $grouped[$itemName]['variants'][$sizeKey] = [
                                    'size_label' => $sizeLabel,
                                    'quantity'   => $qty,
                                ];
                            }
                        }

                        // Persist items and variants
                        foreach ($grouped as $itemName => $itemData) {
                            // Find or create item
                            $item = Item::whereHas('department', fn ($q) =>
                                $q->where('departments.id', $tenant->id)
                            )->where('name', $itemName)->first();

                            if (! $item) {
                                $item = Item::create([
                                    'name'        => $itemName,
                                    'description' => $itemData['description'] ?: null,
                                    'category_id' => $itemData['category_id'],
                                ]);

                                $tenant->items()->syncWithoutDetaching([$item->id]);
                                $created++;
                            }

                            // Add or merge variants
                            foreach ($itemData['variants'] as $variantData) {
                                $existing = ItemVariant::where('item_id', $item->id)
                                    ->whereRaw('LOWER(size_label) = ?', [strtolower($variantData['size_label'])])
                                    ->first();

                                if ($existing) {
                                    $existing->increment('quantity', $variantData['quantity']);
                                } else {
                                    ItemVariant::create([
                                        'item_id'    => $item->id,
                                        'size_label' => $variantData['size_label'],
                                        'quantity'   => $variantData['quantity'],
                                    ]);
                                }

                                $variantsAdded++;
                            }
                        }

                        // Cleanup uploaded file
                        Storage::disk('local')->delete($filePath);

                        // Build result notification
                        $body = "{$created} new item(s) created. {$variantsAdded} variant(s) processed.";
                        if ($skipped > 0) {
                            $body .= " {$skipped} row(s) skipped.";
                        }

                        Notification::make()
                            ->title('Import complete.')
                            ->body($body)
                            ->success()
                            ->send();

                        // Send skipped details if any
                        if (! empty($skippedRows)) {
                            Notification::make()
                                ->title('Skipped rows details')
                                ->body(implode("\n", array_slice($skippedRows, 0, 5)) . (count($skippedRows) > 5 ? "\n...and more." : ''))
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),
            ]);
    }
}
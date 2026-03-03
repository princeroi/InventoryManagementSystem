<?php

namespace App\Filament\Resources\OfficeSupplyRequests\Tables;

use App\Models\OfficeSupplyRequest;
use App\Notifications\OfficeSupplyRequestedNotification;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Text;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class OfficeSupplyRequestsTable
{
    // ── Permission helper ─────────────────────────────────────────────────

    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    // ── Schema (View Modal) ───────────────────────────────────────────────

    public static function viewSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Request Info ──────────────────────────────────────────
                Section::make('Request Information')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Reference #')
                            ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                            ->fontFamily('mono')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'requested' => 'warning',
                                'completed' => 'success',
                                'rejected'  => 'danger',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => ucfirst($state)),

                        TextEntry::make('request_date')
                            ->label('Request Date')
                            ->date('F d, Y'),

                        TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime('F d, Y h:i A'),

                        TextEntry::make('note')
                            ->label('Note')
                            ->placeholder('No note provided.')
                            ->columnSpanFull(),
                    ]),

                // ── Requester Info ────────────────────────────────────────
                Section::make('Requester Details')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employee.name')
                            ->label('Requested For')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('employee.email')
                            ->label('Employee Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('—'),

                        TextEntry::make('requestedBy.name')
                            ->label('Submitted By')
                            ->icon('heroicon-o-pencil-square'),

                        TextEntry::make('requestedBy.email')
                            ->label('Submitter Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('—'),
                    ]),

                // ── Items ─────────────────────────────────────────────────
                Section::make('Requested Items')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Item')
                                    ->weight('bold'),

                                TextEntry::make('variant.size_label')
                                    ->label('Variant')
                                    ->placeholder('—'),

                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),
            ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Ref #')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('request_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Requested For')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('requestedBy.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('items_sum_quantity')
                    ->label('Total Qty')
                    ->sum('items', 'quantity')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'requested' => 'warning',
                        'completed' => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('note')
                    ->label('Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Requested',
                        'completed' => 'Completed',
                    ])
                    ->native(false),
            ])
            ->actions([
                // ── View ──────────────────────────────────────────────────
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalWidth('4xl')
                    ->modalHeading(fn (OfficeSupplyRequest $record) =>
                        'Request #' . str_pad($record->id, 6, '0', STR_PAD_LEFT)
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist(fn (Schema $schema) => static::viewSchema($schema)),

                ActionGroup::make([

                    // ── Tag as Done ───────────────────────────────────────
                    Action::make('tag_as_done')
                        ->label('Tag as Done')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (OfficeSupplyRequest $r) =>
                            $r->status === 'requested'
                            && static::userCan('release office-supply-request')
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Tag as Done?')
                        ->modalDescription('This will mark the request as completed and confirm items have been handed out.')
                        ->modalIcon('heroicon-o-check-badge')
                        ->action(function (OfficeSupplyRequest $record) {
                            $record->update(['status' => 'completed']);

                            // ── Clear all related notifications for this request ──
                            $ref = str_pad($record->id, 6, '0', STR_PAD_LEFT);

                            DB::table('notifications')
                                ->where('type', OfficeSupplyRequestedNotification::class)
                                ->where('data->body', 'like', "%Ref #{$ref}%")
                                ->delete();

                            Notification::make()
                                ->title('Request tagged as completed')
                                ->success()
                                ->send();
                        }),

                    // ── Delete ────────────────────────────────────────────
                    DeleteAction::make()
                        ->visible(fn () => static::userCan('delete office-supply-request')),

                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::userCan('delete-any office-supply-request')),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('request_date', 'desc')
            ->striped()
            ->poll('2s');
    }
}
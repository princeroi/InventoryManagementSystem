<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EmployeesTable
{
    // ── Permission helper ─────────────────────────────────────────────────
    private static function userCan(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Employee ID copied!')
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('indigo')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All employees')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('position')
                    ->label('Position')
                    ->options(fn () => \App\Models\Employee::query()
                        ->distinct()
                        ->whereNotNull('position')
                        ->pluck('position', 'position')
                        ->toArray()
                    )
                    ->searchable(),

                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                // ── Edit Action ───────────────────────────────────────────
                EditAction::make()
                    ->visible(fn ($record) => static::userCan('update employee')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    // ── Bulk Activate ─────────────────────────────────────
                    BulkAction::make('bulk_activate')
                        ->label('Activate')
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->visible(fn () => static::userCan('update employee'))
                        ->requiresConfirmation()
                        ->modalHeading('Activate Selected Employees')
                        ->modalDescription('This will mark the selected employees as active.')
                        ->modalSubmitActionLabel('Confirm')
                        ->action(function (Collection $records): void {
                            $count = $records->count();

                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }

                            Notification::make()
                                ->title("{$count} employee(s) activated successfully")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── Bulk Deactivate ───────────────────────────────────
                    BulkAction::make('bulk_deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->visible(fn () => static::userCan('update employee'))
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Selected Employees')
                        ->modalDescription('This will mark the selected employees as inactive.')
                        ->modalSubmitActionLabel('Confirm')
                        ->action(function (Collection $records): void {
                            $count = $records->count();

                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }

                            Notification::make()
                                ->title("{$count} employee(s) deactivated successfully")
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // ── Bulk Delete ───────────────────────────────────────
                    DeleteBulkAction::make()
                        ->visible(fn () => static::userCan('delete-any employee'))
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Employees')
                        ->modalDescription('Are you sure you want to delete these employees? This action cannot be undone.')
                        ->successNotificationTitle('Employees deleted successfully'),
                ]),
            ]);
    }
}
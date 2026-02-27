<?php

namespace App\Notifications;

use App\Models\OfficeSupplyRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class OfficeSupplyRequestedNotification extends Notification
{
    public function __construct(
        protected OfficeSupplyRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $ref      = '#' . str_pad($this->request->id, 6, '0', STR_PAD_LEFT);
        $employee = $this->request->employee->name ?? 'Unknown';
        $count    = $this->request->items->count();

        return FilamentNotification::make()
            ->title('New Supply Request')
            ->icon('heroicon-o-clipboard-document-list')
            ->iconColor('warning')
            ->body("{$employee} requested {$count} item(s) — Ref {$ref}")
            ->getDatabaseMessage();
    }
}
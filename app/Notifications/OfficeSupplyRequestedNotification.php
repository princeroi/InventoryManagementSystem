<?php

namespace App\Notifications;

use App\Models\OfficeSupplyRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class OfficeSupplyRequestedNotification extends Notification
{
    public function __construct(
        protected OfficeSupplyRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        $ref = str_pad($this->request->id, 6, '0', STR_PAD_LEFT);

        // ── Self-dedup guard ──────────────────────────────────────────────
        // If this exact notifiable already has a notification for this ref,
        // return an empty channel so nothing is sent — no matter how many
        // times after() fires.
        $already = DB::table('notifications')
            ->where('notifiable_id', $notifiable->id)
            ->where('notifiable_type', get_class($notifiable))
            ->where('type', static::class)
            ->where('data->body', 'like', "%Ref #{$ref}%")
            ->exists();

        if ($already) {
            return []; // empty channels = notification is silently skipped
        }

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
<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-app notice telling a reporter that their complaint moved forward.
 */
class ComplaintUpdated extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint, public string $message) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{ticket_code: string, title: string, status: string, message: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_code' => $this->complaint->ticket_code,
            'title' => $this->complaint->title,
            'status' => $this->complaint->status->value,
            'message' => $this->message,
        ];
    }
}

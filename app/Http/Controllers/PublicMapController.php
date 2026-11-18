<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\View\View;

class PublicMapController extends Controller
{
    /**
     * Public map of recent complaints. Never exposes the reporter's identity.
     */
    public function __invoke(): View
    {
        $markers = Complaint::query()
            ->with(['category:id,name', 'district:id,name'])
            ->whereIn('status', [ComplaintStatus::Pending, ComplaintStatus::Diproses, ComplaintStatus::Selesai])
            ->latest()
            ->limit(500)
            ->get(['id', 'ticket_code', 'title', 'category_id', 'district_id', 'latitude', 'longitude', 'status', 'created_at'])
            ->map(fn (Complaint $complaint): array => [
                'lat' => (float) $complaint->latitude,
                'lng' => (float) $complaint->longitude,
                'ticket' => $complaint->ticket_code,
                'title' => $complaint->title,
                'category' => $complaint->category->name,
                'district' => $complaint->district->name,
                'status' => $complaint->status->label(),
                'color' => $complaint->status->hexColor(),
                'url' => route('lacak', ['kode' => $complaint->ticket_code]),
            ]);

        return view('map', [
            'markers' => $markers,
        ]);
    }
}

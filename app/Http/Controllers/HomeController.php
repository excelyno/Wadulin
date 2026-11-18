<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Public landing page with live statistics.
     */
    public function __invoke(): View
    {
        $statusCounts = Complaint::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $finished = Complaint::query()
            ->where('status', ComplaintStatus::Selesai)
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at', 'due_at']);

        return view('beranda', [
            'stats' => [
                'total' => (int) $statusCounts->sum(),
                'pending' => (int) ($statusCounts[ComplaintStatus::Pending->value] ?? 0),
                'diproses' => (int) ($statusCounts[ComplaintStatus::Diproses->value] ?? 0),
                'selesai' => (int) ($statusCounts[ComplaintStatus::Selesai->value] ?? 0),
                'within_sla_percentage' => $finished->isEmpty() ? null
                    : (int) round($finished->filter(fn (Complaint $complaint): bool => $complaint->resolved_at->lte($complaint->due_at))->count() / $finished->count() * 100),
                'average_days' => $finished->isEmpty() ? null
                    : round($finished->avg(fn (Complaint $complaint): float => $complaint->created_at->diffInHours($complaint->resolved_at) / 24), 1),
                'districts' => District::count(),
            ],
            'categories' => Category::active()
                ->withCount(['complaints' => fn ($query) => $query->where('created_at', '>=', now()->subYear())])
                ->orderByDesc('complaints_count')
                ->orderBy('name')
                ->get(),
            'announcements' => Announcement::published()->latest('published_at')->limit(3)->get(),
            'recentComplaints' => Complaint::query()
                ->with(['category:id,name', 'district:id,name'])
                ->where('status', '!=', ComplaintStatus::Dibatalkan)
                ->latest()
                ->limit(6)
                ->get(['id', 'ticket_code', 'title', 'category_id', 'district_id', 'status', 'escalated_at', 'created_at']),
        ]);
    }
}

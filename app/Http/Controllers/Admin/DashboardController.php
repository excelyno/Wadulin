<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $complaints = fn (): Builder => Complaint::query()->visibleTo($user);

        $kpi = [
            'total' => $complaints()->count(),
            'pending' => $complaints()->where('status', ComplaintStatus::Pending)->count(),
            'diproses' => $complaints()->where('status', ComplaintStatus::Diproses)->count(),
            'selesai_bulan_ini' => $complaints()->where('status', ComplaintStatus::Selesai)
                ->where('resolved_at', '>=', now()->startOfMonth())->count(),
            'selesai' => $complaints()->where('status', ComplaintStatus::Selesai)->count(),
            'overdue' => $complaints()->overdue()->count(),
            'eskalasi' => $complaints()->whereNotNull('escalated_at')->whereIn('status', ComplaintStatus::open())->count(),
            'rating' => $complaints()->whereNotNull('rating')->avg('rating'),
            'rata_hari' => $this->averageResolutionDays($complaints()),
        ];

        $dueSoon = $complaints()
            ->with('category')
            ->whereIn('status', ComplaintStatus::open())
            ->where('due_at', '<=', now()->addDay())
            ->orderBy('due_at')
            ->limit(8)
            ->get();

        $perCategory = Category::query()
            ->withCount(['complaints' => fn (Builder $query) => $query->visibleTo($user)])
            ->orderByDesc('complaints_count')
            ->get();

        return view('admin.dashboard', [
            'kpi' => $kpi,
            'dueSoon' => $dueSoon,
            'perCategory' => $perCategory,
            'districtRanking' => $user->isAdminKabupaten() ? $this->districtRanking() : collect(),
        ]);
    }

    /**
     * Average days between creation and resolution of finished complaints.
     *
     * @param  Builder<Complaint>  $query
     */
    private function averageResolutionDays(Builder $query): ?float
    {
        $durations = $query->where('status', ComplaintStatus::Selesai)
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at'])
            ->map(fn (Complaint $complaint): float => $complaint->created_at->diffInHours($complaint->resolved_at) / 24);

        return $durations->isEmpty() ? null : round($durations->avg(), 1);
    }

    /**
     * Per-district totals for the regency ranking table.
     *
     * @return Collection<int, District>
     */
    private function districtRanking(): Collection
    {
        return District::query()
            ->withCount([
                'complaints as total_count',
                'complaints as selesai_count' => fn (Builder $query) => $query->where('status', ComplaintStatus::Selesai),
                'complaints as overdue_count' => fn (Builder $query) => $query->overdue(),
            ])
            ->get()
            ->each(function (District $district): void {
                $district->setAttribute(
                    'selesai_percentage',
                    $district->total_count > 0 ? round($district->selesai_count / $district->total_count * 100) : null,
                );
            })
            ->sortByDesc(fn (District $district): float => ($district->selesai_percentage ?? -1) - $district->overdue_count)
            ->values();
    }
}

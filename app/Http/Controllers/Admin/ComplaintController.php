<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $status = ComplaintStatus::tryFrom($request->string('status')->toString());
        $categoryId = $request->integer('kategori') ?: null;
        $districtId = $user->isAdminKabupaten() ? ($request->integer('kecamatan') ?: null) : null;
        $sla = $request->string('sla')->toString();
        $search = $request->string('q')->trim()->toString();

        $complaints = Complaint::query()
            ->visibleTo($user)
            ->with(['category', 'district', 'user'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($districtId, fn ($query) => $query->where('district_id', $districtId))
            ->when($sla === 'lewat', fn ($query) => $query->overdue())
            ->when($sla === '24jam', fn ($query) => $query->whereIn('status', ComplaintStatus::open())
                ->whereBetween('due_at', [now(), now()->addDay()]))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('ticket_code', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Complaint::query()
            ->visibleTo($user)
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.complaints.index', [
            'complaints' => $complaints,
            'statusCounts' => $statusCounts,
            'categories' => Category::orderBy('name')->get(),
            'districts' => $user->isAdminKabupaten() ? District::orderBy('name')->get() : collect(),
            'filters' => [
                'status' => $status?->value,
                'kategori' => $categoryId,
                'kecamatan' => $districtId,
                'sla' => $sla,
                'q' => $search,
            ],
        ]);
    }

    public function show(Complaint $complaint): View
    {
        Gate::authorize('view', $complaint);

        $complaint->load([
            'category',
            'district',
            'user',
            'qrLocation',
            'attachments',
            'statusHistories.changer.district',
            'dispositions.fromDistrict',
            'dispositions.toDistrict',
            'dispositions.mover',
        ]);

        return view('admin.complaints.show', [
            'complaint' => $complaint,
            'otherDistricts' => District::whereKeyNot($complaint->district_id)->orderBy('name')->get(),
        ]);
    }
}

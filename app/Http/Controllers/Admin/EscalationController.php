<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\View\View;

class EscalationController extends Controller
{
    /**
     * Escalated complaints, most overdue first.
     */
    public function __invoke(): View
    {
        $complaints = Complaint::query()
            ->with(['category', 'district'])
            ->whereNotNull('escalated_at')
            ->whereIn('status', ComplaintStatus::open())
            ->orderBy('due_at')
            ->paginate(20);

        return view('admin.escalations.index', [
            'complaints' => $complaints,
        ]);
    }
}

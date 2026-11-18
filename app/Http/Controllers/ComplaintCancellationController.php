<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;

class ComplaintCancellationController extends Controller
{
    /**
     * The reporter withdraws a complaint that is still pending.
     */
    public function __invoke(CancelComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->cancelBy($request->user(), $request->validated('reason'));

        return redirect()
            ->route('pengaduan.show', $complaint)
            ->with('success', "Pengaduan {$complaint->ticket_code} dibatalkan.");
    }
}

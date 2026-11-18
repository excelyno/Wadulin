<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRatingRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;

class ComplaintRatingController extends Controller
{
    /**
     * The reporter rates how a finished complaint was handled.
     */
    public function __invoke(StoreComplaintRatingRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update($request->validated());

        return redirect()
            ->route('pengaduan.show', $complaint)
            ->with('success', 'Terima kasih atas penilaianmu!');
    }
}

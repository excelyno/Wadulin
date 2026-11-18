<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Public ticket lookup. Never exposes the reporter's identity.
     */
    public function __invoke(Request $request): View
    {
        $code = strtoupper($request->string('kode')->trim()->toString());

        $complaint = $code === '' ? null : Complaint::query()
            ->with([
                'category',
                'district',
                'statusHistories' => fn ($query) => $query->where('is_public', true)->oldest(),
            ])
            ->where('ticket_code', $code)
            ->first();

        return view('tracking', [
            'code' => $code,
            'complaint' => $complaint,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintDispositionRequest;
use App\Models\Complaint;
use App\Models\District;
use App\Notifications\ComplaintUpdated;
use Illuminate\Http\RedirectResponse;

class ComplaintDispositionController extends Controller
{
    public function __invoke(StoreComplaintDispositionRequest $request, Complaint $complaint): RedirectResponse
    {
        $targetDistrict = District::findOrFail($request->validated('district_id'));
        $user = $request->user();

        $complaint->disposeTo($targetDistrict, $user, $request->validated('reason'));

        $complaint->user->notify(new ComplaintUpdated(
            $complaint,
            "Pengaduan kamu dialihkan ke Kecamatan {$targetDistrict->name} agar ditangani wilayah yang tepat.",
        ));

        $redirect = $user->isAdminKabupaten()
            ? redirect()->route('kabupaten.pengaduan.show', $complaint)
            : redirect()->route('kecamatan.pengaduan.index');

        return $redirect->with('success', "Pengaduan {$complaint->ticket_code} didisposisikan ke Kec. {$targetDistrict->name}.");
    }
}

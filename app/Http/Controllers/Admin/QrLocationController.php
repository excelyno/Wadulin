<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQrLocationRequest;
use App\Http\Requests\UpdateQrLocationRequest;
use App\Models\District;
use App\Models\QrLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrLocationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('admin.qr-locations.index', [
            'qrLocations' => QrLocation::query()
                ->manageableBy($user)
                ->with('district')
                ->withCount('complaints')
                ->orderBy('name')
                ->get(),
            'districts' => $user->isAdminKabupaten() ? District::orderBy('name')->get() : collect(),
        ]);
    }

    public function store(StoreQrLocationRequest $request): RedirectResponse
    {
        $user = $request->user();

        QrLocation::create([
            ...$request->validated(),
            'district_id' => $user->isAdminKabupaten() ? $request->validated('district_id') : $user->district_id,
            'code' => QrLocation::generateCode(),
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Titik QR berhasil dibuat.');
    }

    /**
     * QR points are deactivated instead of deleted so complaints keep their source.
     */
    public function update(UpdateQrLocationRequest $request, QrLocation $qrLocation): RedirectResponse
    {
        $qrLocation->update($request->user()->isAdminKabupaten()
            ? $request->validated()
            : $request->safe()->except('district_id'));

        return back()->with('success', 'Titik QR berhasil diperbarui.');
    }
}

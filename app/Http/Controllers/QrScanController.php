<?php

namespace App\Http\Controllers;

use App\Models\QrLocation;
use Illuminate\Http\RedirectResponse;

class QrScanController extends Controller
{
    /**
     * Scanning a QR poster opens the complaint form with its location filled in.
     */
    public function __invoke(QrLocation $qrLocation): RedirectResponse
    {
        abort_unless($qrLocation->is_active, 404);

        return redirect()->route('pengaduan.create', ['qr' => $qrLocation->code]);
    }
}

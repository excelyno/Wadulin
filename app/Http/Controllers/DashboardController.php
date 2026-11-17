<?php

namespace App\Http\Controllers;

use App\Enums\StatusPengaduan;
use App\Http\Requests\UpdatePengaduanStatusRequest;
use App\Models\Pengaduan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengaduan::query()->latest();

        $statusFilter = $request->string('status')->toString();
        if (StatusPengaduan::tryFrom($statusFilter) !== null) {
            $query->where('status', $statusFilter);
        }

        $pengaduans = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Pengaduan::count(),
            'pending' => Pengaduan::where('status', StatusPengaduan::Pending)->count(),
            'proses' => Pengaduan::where('status', StatusPengaduan::Proses)->count(),
            'selesai' => Pengaduan::where('status', StatusPengaduan::Selesai)->count(),
        ];

        return view('dashboard.index', [
            'pengaduans' => $pengaduans,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function geojson(): JsonResponse
    {
        $pengaduans = Pengaduan::latest()->get();

        return response()->json([
            'data' => $pengaduans->map(fn (Pengaduan $p) => [
                'id' => $p->id,
                'kode' => $p->kode_pengaduan,
                'judul' => $p->judul,
                'kategori' => $p->kategori->value,
                'kategori_label' => $p->kategori->label(),
                'alamat' => $p->alamat,
                'nama_pelapor' => $p->nama_pelapor,
                'status' => $p->status->value,
                'status_label' => $p->status->label(),
                'status_color' => $p->status->hexColor(),
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'created_at' => $p->created_at->translatedFormat('d M Y, H:i'),
                'url' => route('pengaduan.show', $p),
            ]),
        ]);
    }

    public function updateStatus(UpdatePengaduanStatusRequest $request, Pengaduan $pengaduan): RedirectResponse
    {
        $pengaduan->update(['status' => $request->validated('status')]);

        return back()->with('success', 'Status pengaduan diperbarui menjadi '.$pengaduan->fresh()->status->label().'.');
    }

    public function destroy(Pengaduan $pengaduan): RedirectResponse
    {
        if ($pengaduan->foto) {
            Storage::disk('public')->delete($pengaduan->foto);
        }

        $pengaduan->delete();

        return back()->with('success', 'Pengaduan berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\KategoriPengaduan;
use App\Enums\StatusPengaduan;
use App\Http\Requests\StorePengaduanRequest;
use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    public function track(Request $request): View|RedirectResponse
    {
        $kode = strtoupper(trim($request->string('kode')->toString()));

        if ($kode === '') {
            return view('pengaduan.track');
        }

        $pengaduan = Pengaduan::where('kode_pengaduan', $kode)->first();

        if ($pengaduan === null) {
            return back()->withInput()->withErrors(['kode' => 'Kode tiket tidak ditemukan. Periksa kembali kode pengaduan Anda.']);
        }

        return redirect()->route('pengaduan.show', $pengaduan);
    }

    public function create(): View
    {
        return view('pengaduan.create', [
            'kategoriOptions' => KategoriPengaduan::cases(),
        ]);
    }

    public function store(StorePengaduanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['kode_pengaduan'] = Pengaduan::generateKode();
        $data['status'] = StatusPengaduan::Pending;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pengaduan-foto', 'public');
        }

        $pengaduan = Pengaduan::create($data);

        return redirect()
            ->route('pengaduan.show', $pengaduan)
            ->with('success', 'Pengaduan Anda berhasil terkirim. Kode pengaduan: '.$pengaduan->kode_pengaduan);
    }

    public function show(Pengaduan $pengaduan): View
    {
        return view('pengaduan.show', [
            'pengaduan' => $pengaduan,
        ]);
    }
}

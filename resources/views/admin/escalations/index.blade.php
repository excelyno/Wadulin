@extends('layouts.admin')

@section('title', 'Eskalasi')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Eskalasi SLA</h1>
        <p class="mt-1 text-sm text-ink-700">Pengaduan yang melewati batas waktu penanganan kecamatan, diurutkan dari yang paling lama terlambat.</p>
    </div>

    <div class="overflow-hidden panel">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-paper text-left text-xs font-semibold text-ink-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Kecamatan</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Batas SLA</th>
                        <th class="px-4 py-3">Keterlambatan</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($complaints as $complaint)
                        <tr class="hover:bg-paper">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs">
                                <a href="{{ route('kabupaten.pengaduan.show', $complaint) }}" class="font-semibold text-primary-700 hover:underline">{{ $complaint->ticket_code }}</a>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3">{{ $complaint->title }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $complaint->district->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $complaint->category->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-ink-700">{{ $complaint->due_at->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3"><x-sla-chip :complaint="$complaint" /></td>
                            <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$complaint->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-ink-700">Tidak ada eskalasi. Semua kecamatan menangani pengaduan tepat waktu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $complaints->links() }}</div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard · WADULIN')

@section('content')
    <div class="container-page py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold">Halo, {{ auth()->user()->name }}</h1>
                <p class="mt-1 text-ink-700">Ringkasan pengaduan yang pernah kamu kirim.</p>
            </div>
            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">Buat pengaduan</a>
        </div>

        <div class="mt-6 grid gap-px overflow-hidden rounded-md border border-line bg-line grid-cols-2 sm:grid-cols-4">
            @foreach ([\App\Enums\ComplaintStatus::Pending, \App\Enums\ComplaintStatus::Diproses, \App\Enums\ComplaintStatus::Selesai, \App\Enums\ComplaintStatus::Ditolak] as $status)
                <a href="{{ route('pengaduan.index', ['status' => $status->value]) }}" class="bg-white p-4 hover:bg-paper">
                    <span class="flex items-center gap-2 text-sm text-ink-700">
                        <span class="h-2 w-2 rounded-full" style="background: {{ $status->hexColor() }}"></span>{{ $status->label() }}
                    </span>
                    <span class="mt-1 block text-3xl font-extrabold tabular-nums">{{ $statusCounts[$status->value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
            <section>
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-bold">Pengaduan terbaru</h2>
                    <a href="{{ route('pengaduan.index') }}" class="link text-sm">Lihat semua</a>
                </div>
                <div class="panel mt-3 divide-y divide-line">
                    @forelse ($latestComplaints as $complaint)
                        @include('complaints._card', ['complaint' => $complaint])
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold">Belum ada pengaduan</p>
                            <p class="mt-1 text-sm text-ink-700">Lihat masalah di sekitarmu? Laporkan agar segera ditangani.</p>
                            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary mt-4">Buat pengaduan pertama</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-bold">Notifikasi
                        @if ($unreadNotificationCount > 0)
                            <span class="ml-1 rounded bg-primary-600 px-1.5 py-0.5 align-middle text-xs font-semibold text-white">{{ $unreadNotificationCount }}</span>
                        @endif
                    </h2>
                    @if ($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifikasi.read') }}">
                            @csrf
                            <button class="link text-sm">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
                <ul class="panel mt-3 divide-y divide-line">
                    @forelse ($notifications as $notification)
                        <li>
                            <a href="{{ route('pengaduan.show', $notification->data['ticket_code']) }}" @class(['block px-4 py-3 hover:bg-paper', 'border-l-4 border-primary-600' => ! $notification->read_at])>
                                <span class="ticket block text-xs">{{ $notification->data['ticket_code'] }} · {{ $notification->created_at->diffForHumans() }}</span>
                                <span @class(['mt-0.5 block text-sm', 'font-semibold' => ! $notification->read_at, 'text-ink-700' => $notification->read_at])>{{ $notification->data['message'] }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-sm text-ink-700">Belum ada notifikasi. Kabar dari petugas akan muncul di sini.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection

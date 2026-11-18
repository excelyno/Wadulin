@extends('layouts.app')

@section('title', 'Pengaduan Saya · WADULIN')

@section('content')
    <div class="container-page py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-3xl font-extrabold">Pengaduan saya</h1>
            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">Buat pengaduan</a>
        </div>

        <nav class="mt-6 flex gap-1 overflow-x-auto border-b border-line" aria-label="Filter status">
            <a href="{{ route('pengaduan.index') }}" @class(['-mb-px whitespace-nowrap border-b-4 px-3 py-2 text-sm font-semibold', 'border-primary-600' => $activeStatus === null, 'border-transparent text-ink-700 hover:text-ink-900' => $activeStatus !== null])>Semua</a>
            @foreach (\App\Enums\ComplaintStatus::cases() as $status)
                <a href="{{ route('pengaduan.index', ['status' => $status->value]) }}" @class(['-mb-px whitespace-nowrap border-b-4 px-3 py-2 text-sm font-semibold', 'border-primary-600' => $activeStatus === $status, 'border-transparent text-ink-700 hover:text-ink-900' => $activeStatus !== $status])>{{ $status->label() }}</a>
            @endforeach
        </nav>

        <div class="panel mt-4 divide-y divide-line">
            @forelse ($complaints as $complaint)
                @include('complaints._card', ['complaint' => $complaint])
            @empty
                <p class="px-5 py-10 text-center text-sm text-ink-700">
                    {{ $activeStatus ? 'Tidak ada pengaduan berstatus '.$activeStatus->label().'.' : 'Kamu belum pernah membuat pengaduan.' }}
                </p>
            @endforelse
        </div>

        <div class="mt-4">{{ $complaints->links() }}</div>
    </div>
@endsection

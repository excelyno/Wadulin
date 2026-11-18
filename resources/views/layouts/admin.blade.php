@php
    $user = auth()->user();
    $panel = $user->role->panelPrefix();
    $menu = [
        ['route' => "$panel.dashboard", 'active' => "$panel.dashboard", 'label' => 'Dashboard'],
        ['route' => "$panel.pengaduan.index", 'active' => "$panel.pengaduan.*", 'label' => $user->isAdminKabupaten() ? 'Semua pengaduan' : 'Antrian pengaduan'],
        ['route' => "$panel.qr.index", 'active' => "$panel.qr.*", 'label' => 'Titik QR'],
    ];
    if ($user->isAdminKabupaten()) {
        $menu[] = ['route' => 'kabupaten.eskalasi', 'active' => 'kabupaten.eskalasi', 'label' => 'Eskalasi'];
        $menu[] = ['heading' => 'Pengaturan'];
        $menu[] = ['route' => 'kabupaten.kategori.index', 'active' => 'kabupaten.kategori.*', 'label' => 'Kategori'];
        $menu[] = ['route' => 'kabupaten.petugas.index', 'active' => 'kabupaten.petugas.*', 'label' => 'Petugas'];
        $menu[] = ['route' => 'kabupaten.pengumuman.index', 'active' => 'kabupaten.pengumuman.*', 'label' => 'Pengumuman'];
        $menu[] = ['route' => 'kabupaten.faq.index', 'active' => 'kabupaten.faq.*', 'label' => 'FAQ'];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · WADULIN</title>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <div class="lg:flex">
        <aside class="bg-ink-900 text-white lg:sticky lg:top-0 lg:h-screen lg:w-60 lg:shrink-0 lg:overflow-y-auto">
            <div class="border-b border-white/10 px-5 py-4">
                <a href="{{ route("$panel.dashboard") }}" class="block">
                    <span class="block text-lg font-extrabold">WADULIN</span>
                    <span class="block text-xs text-white/70">{{ $user->isAdminKabupaten() ? 'Panel Pemkab Jember' : 'Panel Kec. '.$user->district?->name }}</span>
                </a>
            </div>
            <nav class="flex gap-1 overflow-x-auto px-2 py-3 lg:flex-col lg:overflow-visible" aria-label="Menu panel">
                @foreach ($menu as $item)
                    @if (isset($item['heading']))
                        <p class="hidden px-3 pb-1 pt-4 text-xs font-semibold text-white/50 lg:block">{{ $item['heading'] }}</p>
                    @else
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'whitespace-nowrap rounded-md border-l-4 px-3 py-2 text-sm font-medium',
                               'border-accent-500 bg-white/10 text-white' => request()->routeIs($item['active']),
                               'border-transparent text-white/75 hover:bg-white/5 hover:text-white' => ! request()->routeIs($item['active']),
                           ])>{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </nav>
            <div class="hidden border-t border-white/10 px-5 py-4 text-xs text-white/60 lg:block">
                <a href="{{ route('beranda') }}" class="hover:text-white hover:underline">Lihat situs publik ↗</a>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="flex items-center justify-between gap-4 border-b border-line bg-white px-5 py-3 sm:px-8">
                <form method="GET" action="{{ route("$panel.pengaduan.index") }}" class="w-full max-w-sm" role="search">
                    <label for="global-search" class="sr-only">Cari kode tiket atau judul</label>
                    <input id="global-search" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket atau judul"
                           class="w-full rounded-md border border-line bg-paper px-3 py-2 text-sm">
                </form>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-semibold">{{ $user->name }}</span>
                        <span class="block text-xs text-ink-700">{{ $user->role->label() }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-secondary py-2">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="px-5 py-6 sm:px-8">
                @if (session('success'))
                    <p class="alert-success mb-5" role="status">{{ session('success') }}</p>
                @endif
                @if ($errors->any())
                    <div class="alert-error mb-5" role="alert">
                        <p class="font-semibold">Periksa kembali isian:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

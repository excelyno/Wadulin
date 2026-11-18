@php
    $navItems = [
        ['route' => 'lacak', 'label' => 'Lacak tiket'],
        ['route' => 'peta', 'label' => 'Peta aduan'],
        ['route' => 'pengumuman.index', 'label' => 'Pengumuman', 'active' => 'pengumuman.*'],
        ['route' => 'faq', 'label' => 'Bantuan'],
    ];
    $user = auth()->user();
    if ($user?->role === \App\Enums\UserRole::Masyarakat) {
        $navItems[] = ['route' => 'dashboard', 'label' => 'Dashboard'];
        $navItems[] = ['route' => 'pengaduan.index', 'label' => 'Pengaduan saya', 'active' => 'pengaduan.index'];
    } elseif ($user) {
        $navItems[] = ['route' => $user->role->homeRoute(), 'label' => 'Panel petugas'];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="WADULIN — layanan pengaduan masyarakat Kabupaten Jember. Laporkan masalah layanan publik dan pantau penanganannya.">
    <title>@yield('title', config('app.name'))</title>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-[2000] focus:bg-accent-500 focus:px-3 focus:py-2 focus:font-semibold">Langsung ke konten</a>

    <div class="bg-ink-900 text-xs text-white/80">
        <div class="container-page py-1.5">Layanan pengaduan resmi Pemerintah Kabupaten Jember</div>
    </div>

    <header class="relative z-[1200] border-b-4 border-primary-600 bg-white">
        <div class="container-page flex h-16 items-center justify-between gap-6">
            <a href="{{ route('beranda') }}" class="flex items-baseline gap-2">
                <span class="text-xl font-extrabold tracking-tight text-ink-900">WADULIN</span>
                <span class="hidden text-sm text-ink-700 sm:inline">Wadah Aduan Digital</span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Menu utama">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'px-3 py-2 text-sm font-medium',
                           'text-ink-900 underline decoration-2 underline-offset-[6px] decoration-primary-600' => request()->routeIs($item['active'] ?? $item['route']),
                           'text-ink-700 hover:text-ink-900 hover:underline hover:underline-offset-[6px]' => ! request()->routeIs($item['active'] ?? $item['route']),
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                @auth
                    <span class="text-sm text-ink-700">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-secondary py-2">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-semibold text-ink-900 hover:underline">Masuk</a>
                    <a href="{{ route('pengaduan.create') }}" class="btn btn-primary py-2">Buat pengaduan</a>
                @endauth
            </div>

            <details class="group lg:hidden">
                <summary class="btn btn-secondary cursor-pointer list-none py-2">Menu</summary>
                <div class="absolute inset-x-0 top-full border-b border-line bg-white shadow-sm">
                    <nav class="container-page flex flex-col py-2" aria-label="Menu utama">
                        @foreach ($navItems as $item)
                            <a href="{{ route($item['route']) }}" class="border-b border-line/60 py-3 text-sm font-medium last:border-0">{{ $item['label'] }}</a>
                        @endforeach
                        <div class="flex gap-2 py-3">
                            @auth
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="btn btn-secondary">Keluar ({{ $user->name }})</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
                                <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">Buat pengaduan</a>
                            @endauth
                        </div>
                    </nav>
                </div>
            </details>
        </div>
    </header>

    @if (session('success') || session('error'))
        <div class="container-page mt-5">
            @if (session('success'))
                <p class="alert-success" role="status">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p class="alert-error" role="alert">{{ session('error') }}</p>
            @endif
        </div>
    @endif

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-line bg-white">
        <div class="container-page grid gap-8 py-10 text-sm sm:grid-cols-3">
            <div>
                <p class="font-extrabold">WADULIN</p>
                <p class="mt-2 text-ink-700">Layanan pengaduan masyarakat Pemerintah Kabupaten Jember. Setiap aduan ditangani petugas kecamatan dalam {{ config('wadulin.sla_working_days') }} hari kerja.</p>
            </div>
            <div>
                <p class="font-semibold">Layanan</p>
                <ul class="mt-2 space-y-1.5 text-ink-700">
                    <li><a href="{{ route('pengaduan.create') }}" class="hover:underline">Buat pengaduan</a></li>
                    <li><a href="{{ route('lacak') }}" class="hover:underline">Lacak tiket</a></li>
                    <li><a href="{{ route('peta') }}" class="hover:underline">Peta aduan</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold">Informasi</p>
                <ul class="mt-2 space-y-1.5 text-ink-700">
                    <li><a href="{{ route('pengumuman.index') }}" class="hover:underline">Pengumuman</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:underline">Bantuan &amp; FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-line">
            <p class="container-page py-4 text-xs text-ink-700">&copy; {{ date('Y') }} Pemerintah Kabupaten Jember</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

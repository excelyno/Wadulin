<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Wadulin — Sistem Pengaduan Masyarakat. Ajukan pengaduan, pantau di dashboard dan peta live.">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">
    <header class="sticky top-0 z-[1200] border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-[74px] max-w-[1440px] items-center justify-between gap-6 px-5 sm:px-8">
            <a href="{{ route('beranda') }}" class="flex shrink-0 items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M11.54 22.35c.19.13.38.21.6.21a1 1 0 0 0 .61-.21l.06-.05C16.41 19.45 21 15.06 21 10.5A9 9 0 1 0 3 10.5c0 4.56 4.59 8.95 8.54 11.8l-.01.05ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span><span class="block text-[15px] font-extrabold tracking-tight text-slate-900">WADULIN</span><span class="block text-[10px] leading-none text-slate-500">Pemerintah Kabupaten Jember</span></span>
            </a>

            <nav class="hidden items-center gap-1 overflow-x-auto lg:flex lg:gap-1">
                @php
                    $navItems = [
                        ['route' => 'beranda', 'label' => 'Beranda'],
                        ['route' => 'pengaduan.create', 'label' => 'Cara Melapor'],
                        ['route' => 'pengaduan.track', 'label' => 'Lacak Tiket'],
                        ['route' => 'dashboard', 'label' => 'Statistik'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'text-primary-600' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <span class="mx-1 hidden h-6 w-px bg-slate-200 sm:block"></span>

                @guest
                    <a href="{{ route('login') }}"
                        class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium text-slate-900 hover:bg-slate-100">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                        class="whitespace-nowrap rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                        Laporkan
                    </a>
                @else
                    <div class="flex items-center gap-2">
                        <span class="hidden text-sm font-medium text-slate-700 sm:inline">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="whitespace-nowrap rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endguest
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-0.5 h-5 w-5 shrink-0">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.85-8.64a.75.75 0 0 0-1.22-.87l-2.6 3.64-1.49-1.49a.75.75 0 1 0-1.06 1.06l2.14 2.14a.75.75 0 0 0 1.13-.09l3.1-4.39Z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-slate-200 bg-ink-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-3 text-sm text-slate-500 sm:flex-row">
                <p>&copy; {{ date('Y') }} Wadulin — Sistem Pengaduan Masyarakat.</p>
                <p>Dibangun dengan Laravel, Tailwind CSS &amp; Leaflet.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

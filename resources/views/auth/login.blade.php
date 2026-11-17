@extends('layouts.app')

@section('title', 'Masuk · Wadulin')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Masuk ke Wadulin</h1>
            <p class="mt-2 text-sm text-slate-600">Petugas dan administrator dapat mengelola pengaduan setelah masuk.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('email') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('email')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" name="password" id="password" autocomplete="current-password" required
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('password') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('password')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-600">
                        Ingat saya
                    </label>
                </div>
                <button type="submit"
                         class="w-full rounded-md bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700">Daftar di sini</a>
            </p>

            <div class="mt-6 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">
                <p class="font-medium text-slate-600">Akun demo (seeder):</p>
                <p>admin@wadulin.test / password</p>
                <p>petugas@wadulin.test / password</p>
            </div>
        </div>
    </div>
@endsection

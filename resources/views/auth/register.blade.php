@extends('layouts.app')

@section('title', 'Daftar · Wadulin')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat Akun Petugas</h1>
            <p class="mt-2 text-sm text-slate-600">Daftar untuk mengelola pengaduan warga di dashboard.</p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name" required autofocus
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('name') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('name')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" required
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('email') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    @error('email')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" required
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset {{ $errors->has('password') ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-300 focus:ring-2 focus:ring-primary-600' }}">
                    <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
                    @error('password')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required
                            class="mt-1.5 block w-full rounded-md border-0 px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-primary-600">
                    @error('password_confirmation')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                         class="w-full rounded-md bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    Daftar
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700">Masuk di sini</a>
            </p>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Daftar · WADULIN')

@section('content')
    <div class="container-page py-12">
        <div class="max-w-md">
            <h1 class="text-3xl font-extrabold">Daftar akun warga</h1>
            <p class="mt-2 text-ink-700">Akun dipakai untuk mengirim pengaduan dan menerima kabar penanganannya. Akun petugas dibuat oleh admin kabupaten.</p>

            @if ($errors->any())
                <p class="alert-error mt-6" role="alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki.</p>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="name" class="field-label">Nama lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name" required autofocus
                           @class(['field-input', 'field-input-invalid' => $errors->has('name')])>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="field-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" required
                           @class(['field-input', 'field-input-invalid' => $errors->has('email')])>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="field-label">Nomor HP
                        <span class="field-hint">Agar petugas bisa menghubungi bila perlu klarifikasi.</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="tel" required placeholder="08xxxxxxxxxx"
                           @class(['field-input', 'field-input-invalid' => $errors->has('phone')])>
                    @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="field-label">Kata sandi
                        <span class="field-hint">Minimal 8 karakter, berisi huruf dan angka.</span>
                    </label>
                    <input type="password" name="password" id="password" autocomplete="new-password" required
                           @class(['field-input', 'field-input-invalid' => $errors->has('password')])>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="field-label">Ulangi kata sandi</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required class="field-input">
                </div>
                <button type="submit" class="btn btn-primary w-full py-3">Buat akun</button>
            </form>

            <p class="mt-6 text-sm text-ink-700">Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a></p>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Masuk · WADULIN')

@section('content')
    <div class="container-page grid gap-10 py-12 lg:grid-cols-[440px_1fr]">
        <div>
            <h1 class="text-3xl font-extrabold">Masuk</h1>
            <p class="mt-2 text-ink-700">Untuk warga dan petugas. Petugas otomatis diarahkan ke panelnya.</p>

            @if ($errors->any())
                <p class="alert-error mt-6" role="alert">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="email" class="field-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                           @class(['field-input', 'field-input-invalid' => $errors->has('email')])>
                </div>
                <div>
                    <label for="password" class="field-label">Kata sandi</label>
                    <input type="password" name="password" id="password" autocomplete="current-password" required
                           @class(['field-input', 'field-input-invalid' => $errors->has('password')])>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-ink-900/40 text-primary-600">
                    Tetap masuk di perangkat ini
                </label>
                <button type="submit" class="btn btn-primary w-full py-3">Masuk</button>
            </form>

            <p class="mt-6 text-sm text-ink-700">Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar sebagai warga</a></p>
        </div>

        @if (app()->isLocal())
            <aside class="panel h-fit p-5 text-sm">
                <h2 class="font-bold">Akun demo</h2>
                <p class="mt-1 text-ink-700">Hanya tampil di mode <code class="font-mono">local</code>. Semua kata sandi: <code class="font-mono font-semibold">password</code></p>
                <table class="mt-4 w-full text-left">
                    <thead class="text-xs text-ink-700">
                        <tr><th class="pb-2 font-medium">Peran</th><th class="pb-2 font-medium">Email</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ([['Warga', 'warga@wadulin.test'], ['Admin Kecamatan (Patrang)', 'patrang@wadulin.test'], ['Admin Kabupaten', 'kabupaten@wadulin.test']] as [$role, $email])
                            <tr>
                                <td class="py-2 pr-3">{{ $role }}</td>
                                <td class="py-2 font-mono">{{ $email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </aside>
        @endif
    </div>
@endsection

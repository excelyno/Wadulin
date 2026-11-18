<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Public registration always creates a citizen account. Officer accounts
     * are created by the regency admin.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->only(['name', 'email', 'password', 'phone']),
            'role' => UserRole::Masyarakat,
        ]);

        Auth::login($user);

        return redirect()->intended(route('dashboard'))->with('success', 'Akun berhasil dibuat. Selamat datang di WADULIN!');
    }
}

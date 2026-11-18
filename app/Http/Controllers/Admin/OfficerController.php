<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficerRequest;
use App\Http\Requests\UpdateOfficerRequest;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfficerController extends Controller
{
    public function index(): View
    {
        return view('admin.officers.index', [
            'officers' => User::query()
                ->with('district')
                ->whereIn('role', [UserRole::AdminKecamatan, UserRole::AdminKabupaten])
                ->orderBy('role')
                ->orderBy('name')
                ->get(),
            'districts' => District::orderBy('name')->get(),
        ]);
    }

    public function store(StoreOfficerRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated(),
            'role' => UserRole::AdminKecamatan,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Akun petugas berhasil dibuat.');
    }

    public function update(UpdateOfficerRequest $request, User $officer): RedirectResponse
    {
        abort_if($officer->role === UserRole::Masyarakat, 404);

        $officer->update(array_filter(
            $request->validated(),
            fn (mixed $value, string $key): bool => $key !== 'password' || filled($value),
            ARRAY_FILTER_USE_BOTH,
        ));

        return back()->with('success', 'Akun petugas berhasil diperbarui.');
    }
}

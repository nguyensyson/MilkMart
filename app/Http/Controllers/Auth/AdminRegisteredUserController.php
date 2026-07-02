<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminRegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/AdminRegister', [
            'roles' => Role::whereIn('name', ['Admin', 'Staff'])->get(['id', 'name']),
        ]);
    }

    public function store(AdminRegisterRequest $request): RedirectResponse
    {
        // Intentionally no Auth::login() here — creating a colleague's account
        // must not hijack the currently logged-in Admin's own session.
        User::create([
            'role_id' => $request->validated('role_id'),
            'fullname' => $request->validated('fullname'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password_hash' => $request->validated('password'),
            'status' => 'active',
        ]);

        return back()->with('success', 'Tạo tài khoản nội bộ thành công.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request): \Illuminate\Http\RedirectResponse
    {
        $customerRoleId = Role::where('name', 'Customer')->firstOrFail()->id;

        $user = User::create([
            'role_id' => $customerRoleId,
            'fullname' => $request->validated('fullname'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password_hash' => $request->validated('password'),
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Đăng ký thành công! Chào mừng bạn đến với MilkMart.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $roleId = $request->query('role_id') ?: null;
        $status = $request->query('status') ?: null;
        $neverOrdered = $request->boolean('never_ordered');

        $users = User::query()
            ->with('role')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('fullname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleId, fn ($query) => $query->where('role_id', $roleId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($neverOrdered, function ($query) {
                $query->whereDoesntHave('invoices')
                    ->whereHas('role', fn ($query) => $query->whereNotIn('name', ['Admin', 'Staff']));
            })
            ->orderBy('fullname')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'role_id' => $roleId,
                'status' => $status,
                'never_ordered' => $neverOrdered,
            ],
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load('role'),
        ]);
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Không thể tự khóa/mở khóa tài khoản của chính mình.');

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['active', 'locked'])],
        ]);

        $user->update(['status' => $validated['status']]);

        return back()->with('success', $validated['status'] === 'locked'
            ? 'Đã khóa tài khoản.'
            : 'Đã mở khóa tài khoản.');
    }
}

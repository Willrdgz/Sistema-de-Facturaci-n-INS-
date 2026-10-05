<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('users.form', ['account' => new User(['active' => true, 'role' => 'seller'])]);
    }

    public function edit(User $user): View
    {
        return view('users.form', ['account' => $user]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->data($request));

        return to_route('users.index')->with('success', 'Usuario creado.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->data($request, $user);
        DB::transaction(function () use ($user, $data) {
            $admins = User::where('role', 'admin')->where('active', true)->lockForUpdate()->get();
            if ($user->role === 'admin' && $user->active && ($data['role'] !== 'admin' || ! $data['active']) && $admins->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Debe existir al menos un administrador activo.']);
            }
            $user->update($data);
        });

        return to_route('users.index')->with('success', 'Usuario actualizado.');
    }

    private function data(Request $request, ?User $user = null): array
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)], 'role' => 'required|in:admin,seller', 'active' => 'required|boolean', 'password' => [$user ? 'nullable' : 'required', 'string', 'min:10', 'confirmed']]);
        if (empty($data['password'])) {
            unset($data['password']);
        }

return $data;
    }
}

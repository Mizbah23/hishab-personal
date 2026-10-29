<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search') + ['roles' => User::ROLES]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => User::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user, 'roles' => User::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($user->is(auth()->user()) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            return back()->withErrors(['role' => 'You cannot remove or disable your own administrator access.'])->withInput();
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'You cannot remove your own account.']);
        }

        if ($user->isAdmin() && ! User::where('role', 'admin')->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['user' => 'Keep at least one administrator account active.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User removed successfully.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

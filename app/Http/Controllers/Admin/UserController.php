<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Jobs\LogoutUserSessionJob;

class UserController extends Controller
{
    /**
     * Отображение списка пользователей.
     */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('role_id', $request->role);
                });
            })
            ->orderByDesc('created_at'); // сортировка: новые сверху

        $users = $users->paginate(10);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }


    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function show(User $user)
    {
        $orders = $user->orders()->latest()->limit(5)->get(); // последние 5 заказов

        return view('admin.users.show', compact('user', 'orders'));
    }

    public function destroy(User $user)
    {
        // Предотвращаем удаление самого себя
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Нельзя удалить самого себя.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удалён.');
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь создан');
    }

    public function toggleBlock(User $user)
    {
        $user->is_blocked = ! $user->is_blocked;
        $user->save();

        if (! $user->is_blocked) {
            LogoutUserSessionJob::dispatch($user->id);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Статус пользователя обновлён');
    }


}



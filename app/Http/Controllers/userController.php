<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles.permissions');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::get();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:users,username',
            'password'  => 'required|min:6|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'card_id'   => 'nullable|string|max:100|unique:users,card_id',
        ]);

        User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'password'  => bcrypt($request->password),
            'role_id'   => $request->role_id,
            'card_id'   => $request->card_id,
            'status'    => 1
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::get();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:users,username,' . $user->id,
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'card_id'   => 'nullable|string|max:100|unique:users,card_id,' . $user->id,
            'status'    => 'required|in:0,1'
        ]);

        $data = [
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'role_id'   => $request->role_id,
            'card_id'   => $request->card_id,
            'status'    => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deactivate(User $user)
    {
        $user->update(['status' => 0]);

        return back()->with('success', 'User deactivated successfully.');
    }
}

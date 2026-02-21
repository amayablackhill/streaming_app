<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if (Schema::hasColumn('users', 'name')) {
            $user->name = $request->name;
        }

        if (Schema::hasColumn('users', 'username')) {
            $user->username = $request->name;
        }

        if (Schema::hasColumn('users', 'role_id')) {
            $defaultRoleId = null;
            if (Schema::hasTable('roles')) {
                $defaultRoleId = DB::table('roles')
                    ->whereRaw('LOWER(name) = ?', ['user'])
                    ->value('id');
            }

            $user->role_id = $defaultRoleId ?? 2;
        }

        $user->save();

        if (class_exists(Role::class)) {
            $role = Role::query()->where('name', 'user')->first();
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

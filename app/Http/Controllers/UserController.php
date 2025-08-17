<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;

class UserController extends Controller
{

    public function __construct()
    {

        $this->middleware('permission:create user', ['only' => ['create', 'store']]);
        $this->middleware('permission:read user', ['only' => ['index', 'show']]);
        $this->middleware('permission:update user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete user', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndexRequest $request)
    {
        $users = User::query();
        if ($request->has('search')) {
            $users->where('name', 'LIKE', "%" . $request->search . "%")
                  ->orWhere('email', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $users->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        $role = Auth::user()?->roles->first()?->name;
        $roles = Role::get();
        if ($role != 'superadmin') {
            $users->whereHas('roles', function ($query) {
                return $query->where('name', '<>', 'superadmin');
            });
            $roles = Role::where('name', '<>', 'superadmin')->get();
        }
        return Inertia::render('User/Index', [
            'title'         => __('app.label.user'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'users'         => $users->with('roles')->paginate($perPage),
            'roles'         => $roles,
            'breadcrumbs'   => [['label' => __('app.label.user'), 'href' => route('user.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole($request->role);
            DB::commit();
            return back()->with('success', __('app.label.created_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.user')]) . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        try {
            $user->load('roles');
            $roles = Role::all();

            return Inertia::render('User/Show', [
                'title' => __('app.label.user_profile'),
                'user' => $user,
                'roles' => $roles,
                'breadcrumbs' => [
                    ['label' => __('app.label.user'), 'href' => route('user.index')],
                    ['label' => $user->name, 'href' => route('user.show', $user->id)]
                ],
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('user.index')
                ->with('error', __('app.label.user_not_found'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            $roles = Role::all();

            return Inertia::render('User/Edit', [
                'title' => __('app.label.edit_user'),
                'user' => $user,
                'roles' => $roles,
                'breadcrumbs' => [
                    ['label' => __('app.label.user'), 'href' => route('user.index')],
                    ['label' => $user->name, 'href' => route('user.show', $user->id)],
                    ['label' => __('app.label.edit'), 'href' => route('user.edit', $user->id)]
                ],
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('user.index')
                ->with('error', __('app.label.user_not_found'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $updateData = [
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => $request->password ? Hash::make($request->password) : $user->password,
            ];

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
                     Storage::delete('public/' . $user->profile_picture);
                }

                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                $updateData['profile_picture'] = $path;
            }

            $user->update($updateData);
            $user->syncRoles($request->role);
            DB::commit();
            return back()->with('success', __('app.label.updated_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $user->name]) . $th->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'active' => !$user->active
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        try {
            // Generate a random password
            $newPassword = str()->random(12);

            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // In a real application, you would send this password via email
            // For now, we'll just return success message
            return back()->with('success', 'Password reset successfully. New password: ' . $newPassword);
        } catch (\Throwable $th) {
            return back()->with('error', 'Error resetting password: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $user->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $user = User::whereIn('id', $request->id);
            $user->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.user')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.user')]) . $th->getMessage());
        }
    }

    public function loginAsUser(Request $request)
    {
        dd($request->all());
        // Only allow admin login in local environment for security
        if (!app()->environment('local')) {
            abort(403, 'This feature is only available in local environment.');
        }

        $userId = 'superadmin@superadmin.com';
        $user = User::find($userId);

        if ($user) {
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors(['User not found.']);
    }
}

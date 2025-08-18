<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // استخدام فاصل Log الصحيح
use App\Http\Requests\Permission\PermissionIndexRequest;
use App\Http\Requests\Permission\PermissionStoreRequest;
use App\Http\Requests\Permission\PermissionUpdateRequest;

class PermissionController extends Controller
{

    public function __construct()
    {

        $this->middleware('permission:create permission', ['only' => ['create', 'store']]);
        $this->middleware('permission:read permission', ['only' => ['index', 'show']]);
        $this->middleware('permission:update permission', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete permission', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PermissionIndexRequest $request)
    {
        $permissions = Permission::query();
        if ($request->has('search')) {
            $permissions->where('name', 'LIKE', "%" . $request->search . "%");
            $permissions->orWhere('guard_name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $permissions->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;
        return Inertia::render('Permission/Index', [
            'title'         => __('app.label.permission'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'permissions'   => $permissions->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.permission'), 'href' => route('permission.index')]],
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
    public function store(PermissionStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'name'          => $request->name
            ]);
            $superadmin = Role::whereName('superadmin')->first();
            $superadmin->givePermissionTo([$request->name]);
            DB::commit();
            return back()->with('success', __('app.label.created_successfully', ['name' => $permission->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.permission')]) . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionUpdateRequest $request, $id)
    {
        // Start database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Find permission manually
            $permission = Permission::find($id);

            // Verify permission exists
            if (!$permission || !$permission->id) {
                throw new \Exception('Permission not found or invalid');
            }

            // Log the request data for debugging
            Log::debug('Permission update request data', [
                'request_data' => $request->all(),
                'permission_id' => $permission->id
            ]);

            // Verify request has name
            if (!$request->has('name') || empty($request->name)) {
                throw new \Exception('Permission name is required');
            }

            // Get the superadmin role or fail if not found
            $superadmin = Role::whereName('superadmin')->firstOrFail();

            // Store old permission name before updating
            $oldPermissionName = $permission->name;
            // fund permission by id

            // Update permission if name has change
            // Only update if name has changed
            if ($oldPermissionName !== $request->name) {
                // Update permission with validation
                $updated = $permission->update([
                    'name' => $request->name,
                ]);

                // Log update result
                Log::debug('Permission update result', ['success' => $updated]);

                // Throw exception if update failed
                if (!$updated) {
                    throw new \Exception('Permission update failed');
                }

                // Revoke old permission and grant new one
                $superadmin->revokePermissionTo([$oldPermissionName]);
                $superadmin->givePermissionTo([$permission->name]);
            } else {
                Log::debug('No changes to permission name, skipping update');
            }

            // Commit transaction if everything succeeded
            DB::commit();

            // Return success response
            return back()->with('success', __('app.label.updated_successfully', ['name' => $permission->name]));
        } catch (\Throwable $th) {
            // Rollback transaction on error
            DB::rollback();

            // Log error for debugging
            Log::error('Permission update error', [
                'message' => $th->getMessage(),
                'permission_id' => $permission->id ?? null,
                'permission_name' => $permission->name ?? null,
                'request_data' => $request->all()
            ]);

            // Return error response
            return back()->with('error', __('app.label.updated_error', ['name' => $permission->name ?? 'unknown']) . ': ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        DB::beginTransaction();
        try {
            $superadmin = Role::whereName('superadmin')->first();
            $superadmin->revokePermissionTo([$permission->name]);
            $permission->delete();
            DB::commit();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $permission->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $permission->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $permission = Permission::whereIn('id', $request->id);
            $permission->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.permission')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.permission')]) . $th->getMessage());
        }
    }
}

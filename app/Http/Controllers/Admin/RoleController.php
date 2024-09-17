<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use BalajiDharma\LaravelAdminCore\Actions\Role\RoleCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Role\RoleUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Role\RoleCreateData;
use BalajiDharma\LaravelAdminCore\Data\Role\RoleUpdateData;
use BalajiDharma\LaravelFormBuilder\FormBuilder;

class RoleController extends Controller
{
    protected $title = 'Roles';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('adminViewAny', Role::class);
        $roles = (new Role)->newQuery();

        if (request()->has('search')) {
            $roles->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $roles->orderBy($attribute, $sort_order);
        } else {
            $roles->latest();
        }

        $roles = $roles->paginate(config('admin.paginate.per_page'))
            ->onEachSide(config('admin.paginate.each_side'));

        return view('admin.role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(FormBuilder $formBuilder)
    {
        $this->authorize('adminCreate', Role::class);
        $form = $formBuilder->create(\App\Forms\Admin\RoleForm::class, [
            'method' => 'POST',
            'url' => route('admin.role.store'),
        ]);
        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoleCreateData $data, RoleCreateAction $roleCreateAction)
    {
        $this->authorize('adminCreate', Role::class);
        $roleCreateAction->handle($data);

        return redirect()->route('admin.role.index')
            ->with('message', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function show(Role $role)
    {
        $this->authorize('adminView', $role);
        $permissions = Permission::all();
        $roleHasPermissions = array_column(json_decode($role->permissions, true), 'id');

        return view('admin.role.show', compact('role', 'permissions', 'roleHasPermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(Role $role, FormBuilder $formBuilder)
    {
        $this->authorize('adminUpdate', $role);

        $form = $formBuilder->create(\App\Forms\Admin\RoleForm::class, [
            'method' => 'PUT',
            'url' => route('admin.role.update', $role->id),
            'model' => $role,
        ]);
        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RoleUpdateData $data, Role $role, RoleUpdateAction $roleUpdateAction)
    {
        $this->authorize('adminUpdate', $role);
        $roleUpdateAction->handle($data, $role);

        return redirect()->route('admin.role.index')
            ->with('message', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        $this->authorize('adminDelete', $role);
        $role->delete();

        return redirect()->route('admin.role.index')
            ->with('message', __('Role deleted successfully'));
    }
}

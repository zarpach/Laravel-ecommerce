<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use BalajiDharma\LaravelAdminCore\Actions\Permission\PermissionCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Permission\PermissionUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Permission\PermissionCreateData;
use BalajiDharma\LaravelAdminCore\Data\Permission\PermissionUpdateData;
use BalajiDharma\LaravelFormBuilder\FormBuilder;

class PermissionController extends Controller
{
    protected $title = 'Permissions';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('adminViewAny', Permission::class);
        $permissions = (new Permission)->newQuery();

        if (request()->has('search')) {
            $permissions->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $permissions->orderBy($attribute, $sort_order);
        } else {
            $permissions->latest();
        }

        $permissions = $permissions->paginate(config('admin.paginate.per_page'))
            ->onEachSide(config('admin.paginate.each_side'));

        return view('admin.permission.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(FormBuilder $formBuilder)
    {
        $this->authorize('adminCreate', Permission::class);

        $form = $formBuilder->create(\App\Forms\Admin\PermissionForm::class, [
            'method' => 'POST',
            'url' => route('admin.permission.store'),
        ]);
        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PermissionCreateData $data, PermissionCreateAction $permissionCreateAction)
    {
        $this->authorize('adminCreate', Permission::class);
        $permissionCreateAction->handle($data);

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission created successfully.'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function show(Permission $permission)
    {
        $this->authorize('adminView', $permission);

        return view('admin.permission.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(Permission $permission, FormBuilder $formBuilder)
    {
        $this->authorize('adminUpdate', $permission);

        $form = $formBuilder->create(\App\Forms\Admin\PermissionForm::class, [
            'method' => 'PUT',
            'url' => route('admin.permission.update', $permission->id),
            'model' => $permission,
        ]);
        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PermissionUpdateData $data, Permission $permission, PermissionUpdateAction $permissionUpdateAction)
    {
        $this->authorize('adminUpdate', $permission);
        $permissionUpdateAction->handle($data, $permission);

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Permission $permission)
    {
        $this->authorize('adminDelete', $permission);
        $permission->delete();

        return redirect()->route('admin.permission.index')
            ->with('message', __('Permission deleted successfully'));
    }
}

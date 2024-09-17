<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BalajiDharma\LaravelAdminCore\Actions\Menu\MenuCreateAction;
use BalajiDharma\LaravelAdminCore\Actions\Menu\MenuUpdateAction;
use BalajiDharma\LaravelAdminCore\Data\Menu\MenuCreateData;
use BalajiDharma\LaravelAdminCore\Data\Menu\MenuUpdateData;
use BalajiDharma\LaravelFormBuilder\FormBuilder;
use BalajiDharma\LaravelMenu\Models\Menu;

class MenuController extends Controller
{
    protected $title = 'Menus';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('adminViewAny', Menu::class);
        $menus = (new Menu)->newQuery();

        if (request()->has('search')) {
            $menus->where('name', 'Like', '%'.request()->input('search').'%');
        }

        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';
            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }
            $menus->orderBy($attribute, $sort_order);
        } else {
            $menus->latest();
        }

        $menus = $menus->paginate(config('admin.paginate.per_page'))
            ->onEachSide(config('admin.paginate.each_side'));

        return view('admin.menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(FormBuilder $formBuilder)
    {
        $this->authorize('adminCreate', Menu::class);

        $form = $formBuilder->create(\App\Forms\Admin\MenuForm::class, [
            'method' => 'POST',
            'url' => route('admin.menu.store'),
        ]);

        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MenuCreateData $data, MenuCreateAction $menuCreateAction)
    {
        $this->authorize('adminCreate', Menu::class);
        $menuCreateAction->handle($data);

        return redirect()->route('admin.menu.index')
            ->with('message', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(Menu $menu, FormBuilder $formBuilder)
    {
        $this->authorize('adminUpdate', $menu);

        $form = $formBuilder->create(\App\Forms\Admin\MenuForm::class, [
            'method' => 'PUT',
            'url' => route('admin.menu.update', $menu->id),
            'model' => $menu,
        ]);

        $title = $this->title;

        return view('admin.form.edit', compact('form', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MenuUpdateData $data, Menu $menu, MenuUpdateAction $menuUpdateAction)
    {
        $this->authorize('adminUpdate', $menu);
        $menuUpdateAction->handle($data, $menu);

        return redirect()->route('admin.menu.index')
            ->with('message', 'Menu updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Menu $menu)
    {
        $this->authorize('adminDelete', $menu);
        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('message', __('Menu deleted successfully'));
    }
}

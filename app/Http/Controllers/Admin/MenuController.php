<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return view('admin.menus.index', ['menus' => Menu::withCount('items')->get()]);
    }

    public function create()
    {
        return view('admin.menus.create', ['menu' => new Menu]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('menus.create'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);
        $menu = Menu::create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created. Add some items.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', [
            'menu' => $menu->load('rootItems.children'),
            'pages' => Page::published()->pluck('slug', 'slug'),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $menu->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:100'],
        ]));

        return back()->with('success', 'Menu updated.');
    }

    public function destroy(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.delete'), 403);
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function storeItem(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:url,page,route'],
            'value' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
        ]);
        $data['order'] = $menu->items()->max('order') + 1;
        $menu->items()->create($data);

        return back()->with('success', 'Item added.');
    }

    public function destroyItem(Request $request, MenuItem $item)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    public function reorder(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        foreach ($request->input('order', []) as $position => $id) {
            MenuItem::where('id', $id)->where('menu_id', $menu->id)->update(['order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}

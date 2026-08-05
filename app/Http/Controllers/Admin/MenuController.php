<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $menus = $this->applyListQuery(
            Menu::withCount('items'),
            $request,
            ['name', 'location'],
            ['name', 'location', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.menus.index', compact('menus'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Menu::class, 'menus');
    }

    public function create()
    {
        abort_unless(auth()->user()->can('menus.create'), 403);

        return view('admin.menus.create', [
            'locations' => config('menus.locations'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('menus.create'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', Rule::in(array_keys(config('menus.locations'))), Rule::unique('menus', 'location')],
        ]);
        $menu = Menu::create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created. Add some items.');
    }

    public function edit(Menu $menu)
    {
        abort_unless(auth()->user()->can('menus.view'), 403);

        return view('admin.menus.edit', [
            'menu' => $menu->load(['items', 'rootItems.children']),
            'pages' => Page::published()->orderBy('title')->pluck('title', 'slug'),
            'locations' => config('menus.locations'),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $menu->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => [
                'required',
                Rule::in(array_keys(config('menus.locations'))),
                Rule::unique('menus', 'location')->ignore($menu->id),
            ],
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
        $data = $this->validateMenuItem($request, $menu);
        $data['order'] = (int) $menu->items()->max('order') + 1;
        $menu->items()->create($data);

        return back()->with('success', 'Item added.');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $item->update($this->validateMenuItem($request, $item->menu, $item));

        return back()->with('success', 'Item updated.');
    }

    public function destroyItem(Request $request, MenuItem $item)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    /**
     * @return array{label: string, type: string, value: string, parent_id: int|null}
     */
    protected function validateMenuItem(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $parentRules = [
            'nullable',
            Rule::exists('menu_items', 'id')->where(function ($q) use ($menu, $item) {
                $q->where('menu_id', $menu->id)->whereNull('parent_id');
                if ($item) {
                    $q->where('id', '!=', $item->id);
                }
            }),
        ];

        if ($item && $item->children()->exists()) {
            $parentRules[] = 'prohibited';
        }

        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:url,page,route'],
            'value' => ['required', 'string', 'max:255'],
            'parent_id' => $parentRules,
        ]);
    }

    public function reorder(Request $request, Menu $menu)
    {
        abort_unless($request->user()->can('menus.edit'), 403);
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)],
        ]);
        foreach ($data['order'] as $position => $id) {
            MenuItem::where('id', $id)->where('menu_id', $menu->id)->update(['order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    use HandlesBulkActions;
    use HandlesListQuery;

    public function index(Request $request)
    {
        abort_unless($request->user()->can('team.view'), 403);

        $teamMembers = $this->applyListQuery(
            TeamMember::query(),
            $request,
            searchable: ['name', 'role_title', 'bio', 'email'],
            sortable: ['name', 'role_title', 'sort_order', 'status', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->paginate(12)
            ->withQueryString();

        return view('admin.team.index', compact('teamMembers'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('team.create'), 403);

        return view('admin.team.create', [
            'teamMember' => new TeamMember(['status' => 'draft', 'sort_order' => 0]),
        ]);
    }

    public function store(TeamMemberRequest $request)
    {
        abort_unless($request->user()->can('team.create'), 403);

        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }
        TeamMember::create($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member created.');
    }

    public function edit(TeamMember $teamMember)
    {
        abort_unless(auth()->user()->can('team.view'), 403);

        return view('admin.team.edit', compact('teamMember'));
    }

    public function update(TeamMemberRequest $request, TeamMember $teamMember)
    {
        abort_unless($request->user()->can('team.edit'), 403);

        $data = $request->validated();
        if ($request->hasFile('photo')) {
            if ($teamMember->photo) {
                Storage::disk('public')->delete($teamMember->photo);
            }
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }
        $teamMember->update($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy(Request $request, TeamMember $teamMember)
    {
        abort_unless($request->user()->can('team.delete'), 403);

        if ($teamMember->photo) {
            Storage::disk('public')->delete($teamMember->photo);
        }
        $teamMember->delete();

        return back()->with('success', 'Team member deleted.');
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, TeamMember::class, 'team');
    }
}

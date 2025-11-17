<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\Team;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\TeamMember\TeamMemberStoreRequest;
use App\Http\Requests\TeamMember\TeamMemberUpdateRequest;
use App\Http\Requests\TeamMember\TeamMemberSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teamMembers = TeamMember::with(['team', 'user', 'invitedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get all teams for filter dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamMembers/Index', [
            'teamMembers' => $teamMembers,
            'teams' => $teams,
            'users' => $users,
            'filters' => [],
        ]);
    }

    /**
     * Search team members.
     */
    public function search(TeamMemberSearchRequest $request)
    {
        $query = TeamMember::with(['team', 'user', 'invitedBy']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('team', function ($teamQuery) use ($request) {
                    $teamQuery->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('slug', 'like', '%' . $request->search . '%');
                });
            });
        }

        // Filter by team
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('joined_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('joined_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['role', 'joined_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $teamMembers = $query->paginate($request->get('per_page', 15));

        // Get all teams for filter dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamMembers/Index', [
            'teamMembers' => $teamMembers,
            'teams' => $teams,
            'users' => $users,
            'filters' => $request->only(['search', 'team_id', 'user_id', 'role', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all teams for dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamMembers/Create', [
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeamMemberStoreRequest $request)
    {
        $validated = $request->validated();

        // Check if user is already a member of this team
        $existingMember = TeamMember::where('team_id', $validated['team_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingMember) {
            return back()->withErrors([
                'user_id' => 'This user is already a member of this team.'
            ]);
        }

        // Set invited_by to current user if not provided
        if (empty($validated['invited_by'])) {
            $validated['invited_by'] = Auth::id();
        }

        // Set joined_at to current timestamp if not provided
        if (empty($validated['joined_at'])) {
            $validated['joined_at'] = now();
        }

        $teamMember = TeamMember::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_member.created',
            'description' => "Team member added to team '{$teamMember->team->name}'",
            'metadata' => [
                'team_member_id' => $teamMember->id,
                'team_id' => $teamMember->team_id,
                'team_name' => $teamMember->team->name,
                'user_id' => $teamMember->user_id,
                'role' => $teamMember->role,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('team-members.index')
            ->with('success', 'Team member added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamMember $teamMember, Request $request)
    {
        $teamMember->load(['team.owner', 'user', 'invitedBy', 'deletedBy']);

        // Get activity logs for this team member
        $activities = UserActivityLog::where(function ($query) use ($teamMember) {
                $query->where('action', 'like', 'team_member.%')
                    ->whereJsonContains('metadata->team_member_id', $teamMember->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/TeamMembers/Show', [
            'teamMember' => $teamMember,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a team member.
     */
    public function activities(TeamMember $teamMember, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($teamMember) {
                $query->where('action', 'like', 'team_member.%')
                    ->whereJsonContains('metadata->team_member_id', $teamMember->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/TeamMembers/Show', [
            'teamMember' => $teamMember->load(['team.owner', 'user', 'invitedBy', 'deletedBy']),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamMember $teamMember)
    {
        $teamMember->load(['team', 'user']);

        // Get all teams for dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamMembers/Edit', [
            'teamMember' => $teamMember,
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeamMemberUpdateRequest $request, TeamMember $teamMember)
    {
        $validated = $request->validated();

        // Check if user is already a member of this team (excluding current record)
        if (isset($validated['team_id']) && isset($validated['user_id'])) {
            $existingMember = TeamMember::where('team_id', $validated['team_id'])
                ->where('user_id', $validated['user_id'])
                ->where('id', '!=', $teamMember->id)
                ->first();

            if ($existingMember) {
                return back()->withErrors([
                    'user_id' => 'This user is already a member of this team.'
                ]);
            }
        }

        $teamMember->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_member.updated',
            'description' => "Team member updated in team '{$teamMember->team->name}'",
            'metadata' => [
                'team_member_id' => $teamMember->id,
                'team_id' => $teamMember->team_id,
                'team_name' => $teamMember->team->name,
                'user_id' => $teamMember->user_id,
                'role' => $teamMember->role,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamMember $teamMember)
    {
        $teamName = $teamMember->team->name;
        $teamMemberId = $teamMember->id;
        $teamId = $teamMember->team_id;
        $userId = $teamMember->user_id;
        $role = $teamMember->role;

        // Set deleted_by before soft delete
        $teamMember->deleted_by = Auth::id();
        $teamMember->save();
        $teamMember->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_member.deleted',
            'description' => "Team member removed from team '{$teamName}'",
            'metadata' => [
                'team_member_id' => $teamMemberId,
                'team_id' => $teamId,
                'team_name' => $teamName,
                'user_id' => $userId,
                'role' => $role,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('team-members.index')
            ->with('success', 'Team member removed successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:team_members,id',
        ]);

        $teamMembers = TeamMember::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all members
                $teamMembers->update(['deleted_by' => Auth::id()]);
                
                // Soft delete all members
                $teamMembers->delete();
                $message = 'Team members removed successfully.';
                break;

            default:
                return back()->withErrors(['error' => 'Invalid action.']);
        }

        return back()->with('success', $message);
    }
}


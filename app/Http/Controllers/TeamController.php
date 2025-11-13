<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\UserActivityLog;
use App\Models\User;
use App\Http\Requests\Team\TeamStoreRequest;
use App\Http\Requests\Team\TeamUpdateRequest;
use App\Http\Requests\Team\TeamSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::with('owner')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get all users for owner dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Teams/Index', [
            'teams' => $teams,
            'users' => $users,
            'filters' => [],
        ]);
    }

    /**
     * Search teams.
     */
    public function search(TeamSearchRequest $request)
    {
        $query = Team::with('owner');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Filter by verification status
        if ($request->has('is_verified') && $request->is_verified !== null && $request->is_verified !== '') {
            $isVerified = $request->is_verified === '1' || $request->is_verified === 1 || $request->is_verified === true;
            $query->where('is_verified', $isVerified);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'slug', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $teams = $query->paginate($request->get('per_page', 15));

        // Get all users for owner dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Teams/Index', [
            'teams' => $teams,
            'users' => $users,
            'filters' => $request->only(['search', 'owner_id', 'is_verified', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Teams/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeamStoreRequest $request)
    {
        $validated = $request->validated();

        // Set default owner to current user if not provided
        if (empty($validated['owner_id'])) {
            $validated['owner_id'] = Auth::id();
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Team::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $team = Team::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team.created',
            'description' => "Team '{$team->name}' was created",
            'metadata' => [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'team_slug' => $team->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team, Request $request)
    {
        $team->load('owner', 'members.user', 'invitations');

        // Get activity logs for this team
        $activities = UserActivityLog::where(function ($query) use ($team) {
                $query->where('action', 'like', 'team.%')
                    ->whereJsonContains('metadata->team_id', $team->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Teams/Show', [
            'team' => $team,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a team.
     */
    public function activities(Team $team, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($team) {
                $query->where('action', 'like', 'team.%')
                    ->whereJsonContains('metadata->team_id', $team->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Teams/Show', [
            'team' => $team->load('owner', 'members.user', 'invitations'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/Teams/Edit', [
            'team' => $team,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeamUpdateRequest $request, Team $team)
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Team::where('slug', $validated['slug'])->where('id', '!=', $team->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $team->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team.updated',
            'description' => "Team '{$team->name}' was updated",
            'metadata' => [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'team_slug' => $team->slug,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        // Check if team has members
        if ($team->members()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete team with members. Please remove members first.']);
        }

        $teamName = $team->name;
        $teamId = $team->id;
        $teamSlug = $team->slug;

        $team->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team.deleted',
            'description' => "Team '{$teamName}' was deleted",
            'metadata' => [
                'team_id' => $teamId,
                'team_name' => $teamName,
                'team_slug' => $teamSlug,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:verify,unverify,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:teams,id',
        ]);

        $teams = Team::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'verify':
                $teams->update(['is_verified' => true]);
                $message = 'Teams verified successfully.';
                break;

            case 'unverify':
                $teams->update(['is_verified' => false]);
                $message = 'Teams unverified successfully.';
                break;

            case 'delete':
                // Check for dependencies
                $teamsWithMembers = $teams->whereHas('members')->count();

                if ($teamsWithMembers > 0) {
                    return back()->withErrors([
                        'error' => 'Some teams cannot be deleted due to dependencies.'
                    ]);
                }

                $teams->delete();
                $message = 'Teams deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}


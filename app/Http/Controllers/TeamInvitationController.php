<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Http\Requests\TeamInvitation\TeamInvitationStoreRequest;
use App\Http\Requests\TeamInvitation\TeamInvitationUpdateRequest;
use App\Http\Requests\TeamInvitation\TeamInvitationSearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeamInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teamInvitations = TeamInvitation::with(['team', 'invitedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get all teams for filter dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamInvitations/Index', [
            'teamInvitations' => $teamInvitations,
            'teams' => $teams,
            'users' => $users,
            'filters' => [],
        ]);
    }

    /**
     * Search team invitations.
     */
    public function search(TeamInvitationSearchRequest $request)
    {
        $query = TeamInvitation::with(['team', 'invitedBy']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                    ->orWhere('token', 'like', '%' . $request->search . '%')
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by invited_by
        if ($request->filled('invited_by')) {
            $query->where('invited_by', $request->invited_by);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter expired invitations
        if ($request->has('expired') && $request->expired !== null && $request->expired !== '') {
            if ($request->expired === '1' || $request->expired === 1 || $request->expired === true) {
                $query->where('expires_at', '<', now());
            } else {
                $query->where('expires_at', '>=', now());
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['email', 'role', 'status', 'expires_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $teamInvitations = $query->paginate($request->get('per_page', 15));

        // Get all teams for filter dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for filter dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamInvitations/Index', [
            'teamInvitations' => $teamInvitations,
            'teams' => $teams,
            'users' => $users,
            'filters' => $request->only(['search', 'team_id', 'status', 'role', 'invited_by', 'date_from', 'date_to', 'expired']),
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

        return Inertia::render('Dashboard/TeamInvitations/Create', [
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeamInvitationStoreRequest $request)
    {
        $validated = $request->validated();

        // Check if user with this email is already a member of this team
        $team = Team::findOrFail($validated['team_id']);
        $user = User::where('email', $validated['email'])->first();
        
        if ($user && $team->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors([
                'email' => 'This user is already a member of this team.'
            ]);
        }

        // Check if there's already a pending invitation for this email and team
        $existingInvitation = TeamInvitation::where('team_id', $validated['team_id'])
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return back()->withErrors([
                'email' => 'A pending invitation already exists for this email and team.'
            ]);
        }

        // Set invited_by to current user if not provided
        if (empty($validated['invited_by'])) {
            $validated['invited_by'] = Auth::id();
        }

        // Generate token if not provided
        if (empty($validated['token'])) {
            $validated['token'] = Str::random(64);
        }

        // Set default expiration (7 days from now) if not provided
        if (empty($validated['expires_at'])) {
            $validated['expires_at'] = Carbon::now()->addDays(7);
        }

        // Set default status to pending if not provided
        if (empty($validated['status'])) {
            $validated['status'] = 'pending';
        }

        $teamInvitation = TeamInvitation::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_invitation.created',
            'description' => "Team invitation sent to '{$teamInvitation->email}' for team '{$teamInvitation->team->name}'",
            'metadata' => [
                'team_invitation_id' => $teamInvitation->id,
                'team_id' => $teamInvitation->team_id,
                'team_name' => $teamInvitation->team->name,
                'email' => $teamInvitation->email,
                'role' => $teamInvitation->role,
                'status' => $teamInvitation->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('team-invitations.index')
            ->with('success', 'Team invitation sent successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamInvitation $teamInvitation, Request $request)
    {
        $teamInvitation->load(['team.owner', 'invitedBy', 'deletedBy']);

        // Get activity logs for this team invitation
        $activities = UserActivityLog::where(function ($query) use ($teamInvitation) {
                $query->where('action', 'like', 'team_invitation.%')
                    ->whereJsonContains('metadata->team_invitation_id', $teamInvitation->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/TeamInvitations/Show', [
            'teamInvitation' => $teamInvitation,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a team invitation.
     */
    public function activities(TeamInvitation $teamInvitation, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($teamInvitation) {
                $query->where('action', 'like', 'team_invitation.%')
                    ->whereJsonContains('metadata->team_invitation_id', $teamInvitation->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/TeamInvitations/Show', [
            'teamInvitation' => $teamInvitation->load(['team.owner', 'invitedBy', 'deletedBy']),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamInvitation $teamInvitation)
    {
        $teamInvitation->load(['team']);

        // Get all teams for dropdown
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);
        
        // Get all users for dropdown
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Dashboard/TeamInvitations/Edit', [
            'teamInvitation' => $teamInvitation,
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeamInvitationUpdateRequest $request, TeamInvitation $teamInvitation)
    {
        $validated = $request->validated();

        // Check if email changed and if user with new email is already a member
        if (isset($validated['email']) && $validated['email'] !== $teamInvitation->email) {
            $team = Team::findOrFail($validated['team_id'] ?? $teamInvitation->team_id);
            $user = User::where('email', $validated['email'])->first();
            
            if ($user && $team->members()->where('user_id', $user->id)->exists()) {
                return back()->withErrors([
                    'email' => 'This user is already a member of this team.'
                ]);
            }

            // Check if there's already a pending invitation for this email and team
            $existingInvitation = TeamInvitation::where('team_id', $validated['team_id'] ?? $teamInvitation->team_id)
                ->where('email', $validated['email'])
                ->where('status', 'pending')
                ->where('id', '!=', $teamInvitation->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($existingInvitation) {
                return back()->withErrors([
                    'email' => 'A pending invitation already exists for this email and team.'
                ]);
            }
        }

        $teamInvitation->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_invitation.updated',
            'description' => "Team invitation updated for '{$teamInvitation->email}' in team '{$teamInvitation->team->name}'",
            'metadata' => [
                'team_invitation_id' => $teamInvitation->id,
                'team_id' => $teamInvitation->team_id,
                'team_name' => $teamInvitation->team->name,
                'email' => $teamInvitation->email,
                'role' => $teamInvitation->role,
                'status' => $teamInvitation->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('team-invitations.index')
            ->with('success', 'Team invitation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamInvitation $teamInvitation)
    {
        $teamName = $teamInvitation->team->name;
        $teamInvitationId = $teamInvitation->id;
        $teamId = $teamInvitation->team_id;
        $email = $teamInvitation->email;
        $status = $teamInvitation->status;

        // Set deleted_by before soft delete
        $teamInvitation->deleted_by = Auth::id();
        $teamInvitation->save();
        $teamInvitation->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_invitation.deleted',
            'description' => "Team invitation deleted for '{$email}' from team '{$teamName}'",
            'metadata' => [
                'team_invitation_id' => $teamInvitationId,
                'team_id' => $teamId,
                'team_name' => $teamName,
                'email' => $email,
                'status' => $status,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('team-invitations.index')
            ->with('success', 'Team invitation deleted successfully.');
    }

    /**
     * Resend invitation.
     */
    public function resend(TeamInvitation $teamInvitation)
    {
        // Update expiration date to 7 days from now
        $teamInvitation->expires_at = Carbon::now()->addDays(7);
        $teamInvitation->status = 'pending';
        $teamInvitation->save();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'team_invitation.resent',
            'description' => "Team invitation resent to '{$teamInvitation->email}' for team '{$teamInvitation->team->name}'",
            'metadata' => [
                'team_invitation_id' => $teamInvitation->id,
                'team_id' => $teamInvitation->team_id,
                'team_name' => $teamInvitation->team->name,
                'email' => $teamInvitation->email,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Team invitation resent successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,resend',
            'ids' => 'required|array',
            'ids.*' => 'exists:team_invitations,id',
        ]);

        $teamInvitations = TeamInvitation::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                // Set deleted_by for all invitations
                $teamInvitations->update(['deleted_by' => Auth::id()]);
                
                // Soft delete all invitations
                $teamInvitations->delete();
                $message = 'Team invitations deleted successfully.';
                break;

            case 'resend':
                // Update expiration and status
                $teamInvitations->update([
                    'expires_at' => Carbon::now()->addDays(7),
                    'status' => 'pending',
                ]);
                $message = 'Team invitations resent successfully.';
                break;

            default:
                return back()->withErrors(['error' => 'Invalid action.']);
        }

        return back()->with('success', $message);
    }
}


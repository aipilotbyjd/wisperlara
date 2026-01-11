<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $teams = $request->user()->teams()->with('owner')->get();

        return TeamResource::collection($teams);
    }

    public function store(TeamRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->plan, ['business', 'enterprise'])) {
            return response()->json([
                'error' => 'plan_required',
                'message' => 'Business or Enterprise plan required to create teams.',
            ], 403);
        }

        $team = Team::create([
            'name' => $request->name,
            'owner_id' => $user->id,
            'plan' => $user->plan,
        ]);

        // Add owner as team member
        $team->members()->attach($user->id, ['role' => 'owner']);

        // Set as current team
        $user->update(['current_team_id' => $team->id]);

        return response()->json([
            'message' => 'Team created successfully.',
            'team' => new TeamResource($team->load('owner')),
        ], 201);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        return response()->json([
            'team' => new TeamResource($team->load('owner', 'members')),
            'members' => $team->members->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->full_name,
                'email' => $member->email,
                'role' => $member->pivot->role,
                'joined_at' => $member->pivot->created_at,
            ]),
        ]);
    }

    public function update(TeamRequest $request, Team $team): JsonResponse
    {
        $this->authorizeTeamOwner($request->user(), $team);

        $team->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Team updated.',
            'team' => new TeamResource($team),
        ]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamOwner($request->user(), $team);

        // Reset current_team_id for all members
        User::where('current_team_id', $team->id)->update(['current_team_id' => null]);

        $team->delete();

        return response()->json([
            'message' => 'Team deleted.',
        ]);
    }

    public function invite(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user(), $team);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['nullable', 'string', 'in:admin,member'],
        ]);

        $invitee = User::where('email', $request->email)->first();

        if ($team->members()->where('user_id', $invitee->id)->exists()) {
            return response()->json([
                'error' => 'already_member',
                'message' => 'User is already a team member.',
            ], 400);
        }

        $team->members()->attach($invitee->id, ['role' => $request->role ?? 'member']);

        return response()->json([
            'message' => 'User added to team.',
        ]);
    }

    public function removeMember(Request $request, Team $team, User $user): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user(), $team);

        if ($team->owner_id === $user->id) {
            return response()->json([
                'error' => 'cannot_remove_owner',
                'message' => 'Cannot remove team owner.',
            ], 400);
        }

        $team->members()->detach($user->id);

        if ($user->current_team_id === $team->id) {
            $user->update(['current_team_id' => null]);
        }

        return response()->json([
            'message' => 'Member removed from team.',
        ]);
    }

    public function updateMemberRole(Request $request, Team $team, User $user): JsonResponse
    {
        $this->authorizeTeamOwner($request->user(), $team);

        $request->validate([
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        if ($team->owner_id === $user->id) {
            return response()->json([
                'error' => 'cannot_change_owner_role',
                'message' => 'Cannot change owner role.',
            ], 400);
        }

        $team->members()->updateExistingPivot($user->id, ['role' => $request->role]);

        return response()->json([
            'message' => 'Member role updated.',
        ]);
    }

    public function switchTeam(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        $request->user()->update(['current_team_id' => $team->id]);

        return response()->json([
            'message' => 'Switched to team.',
            'team' => new TeamResource($team),
        ]);
    }

    private function authorizeTeamAccess(User $user, Team $team): void
    {
        if (!$team->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'You are not a member of this team.');
        }
    }

    private function authorizeTeamAdmin(User $user, Team $team): void
    {
        $member = $team->members()->where('user_id', $user->id)->first();
        if (!$member || !in_array($member->pivot->role, ['owner', 'admin'])) {
            abort(403, 'Admin access required.');
        }
    }

    private function authorizeTeamOwner(User $user, Team $team): void
    {
        if ($team->owner_id !== $user->id) {
            abort(403, 'Owner access required.');
        }
    }
}

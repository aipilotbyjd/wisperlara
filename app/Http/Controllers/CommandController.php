<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandRequest;
use App\Http\Resources\CommandResource;
use App\Models\CustomCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommandController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CommandResource::collection(
            $request->user()->customCommands()->orderBy('trigger_phrase')->paginate($request->per_page ?? 50)
        );
    }

    public function store(CommandRequest $request): JsonResponse
    {
        $command = $request->user()->customCommands()->create([
            'trigger_phrase' => $request->trigger_phrase,
            'replacement_text' => $request->replacement_text,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Command created.',
            'command' => new CommandResource($command),
        ], 201);
    }

    public function show(Request $request, CustomCommand $command): JsonResponse
    {
        $this->authorize('view', $command);

        return response()->json([
            'command' => new CommandResource($command),
        ]);
    }

    public function update(CommandRequest $request, CustomCommand $command): JsonResponse
    {
        $this->authorize('update', $command);

        $command->update([
            'trigger_phrase' => $request->trigger_phrase,
            'replacement_text' => $request->replacement_text,
        ]);

        return response()->json([
            'message' => 'Command updated.',
            'command' => new CommandResource($command),
        ]);
    }

    public function destroy(Request $request, CustomCommand $command): JsonResponse
    {
        $this->authorize('delete', $command);

        $command->delete();

        return response()->json([
            'message' => 'Command deleted.',
        ]);
    }

    public function toggle(Request $request, CustomCommand $command): JsonResponse
    {
        $this->authorize('update', $command);

        $command->update([
            'is_active' => !$command->is_active,
        ]);

        return response()->json([
            'message' => $command->is_active ? 'Command activated.' : 'Command deactivated.',
            'command' => new CommandResource($command),
        ]);
    }
}

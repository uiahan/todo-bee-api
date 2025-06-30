<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subtask;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function index($task_id)
    {
        $pending = Subtask::where('task_id', $task_id)->where('status', 'pending')->get();
        $progress = Subtask::where('task_id', $task_id)->where('status', 'progress')->get();
        $done = Subtask::where('task_id', $task_id)->where('status', 'done')->get();

        return response()->json([
            'pending' => $pending,
            'progress' => $progress,
            'done' => $done,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,progress,done',
            'task_id' => 'required|exists:tasks,id',
        ]);

        $subtask = Subtask::create($validated);

        return response()->json(['message' => 'Subtask created', 'subtask' => $subtask]);
    }

    public function update(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,progress,done',
        ]);

        $subtask->update($validated);

        return response()->json(['message' => 'Subtask updated', 'subtask' => $subtask]);
    }

    public function destroy($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->delete();

        return response()->json(['message' => 'Subtask deleted']);
    }
}

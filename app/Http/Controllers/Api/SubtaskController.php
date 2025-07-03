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
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending,progress,done',
        ]);

        $subtask = Subtask::create([
            'task_id' => $request->task_id,
            'title' => $request->title,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Subtask berhasil dibuat', 'subtask' => $subtask]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask = Subtask::findOrFail($id);
        $subtask->title = $request->title;
        $subtask->save();

        return response()->json(['message' => 'Subtask berhasil diperbarui', 'subtask' => $subtask]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,progress,done',
        ]);

        $subtask = Subtask::findOrFail($id);
        $subtask->status = $request->status;
        $subtask->save();

        return response()->json(['message' => 'Status updated successfully']);
    }


    public function destroy($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->delete();

        return response()->json(['message' => 'Subtask deleted']);
    }
}

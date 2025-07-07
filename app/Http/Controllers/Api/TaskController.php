<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $task = Task::where('user_id', $user->id)->get();

        return response()->json([
            'task' => $task
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'deadline'    => 'nullable|date',
            'image'       => 'nullable|image|max:2048',
            'video'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('images', 'public')
            : null;

        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'deadline'    => $request->deadline,
            'image'       => $imagePath ?? '',
            'video'       => $request->video ?? '',
            'user_id'     => $request->user()->id,
        ]);

        $task->image_url = $task->image ? asset('storage/' . $task->image) : null;

        return response()->json([
            'message' => 'Todo berhasil ditambahkan.',
            'task'    => $task,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'deadline' => 'required|date',
            'image' => 'nullable|image',
            'video' => 'nullable|string',
        ]);

        $task = Task::findOrFail($id);

        $task->title = $request->title;
        $task->description = $request->description;
        $task->deadline = $request->deadline;
        $task->video = $request->video;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $task->image = $imagePath;
        }

        $task->save();

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'deadline' => $task->deadline,
                'video' => $task->video,
                'completed' => $task->completed,
                'image_url' => $task->image ? asset('storage/' . $task->image) : null,
            ]
        ]);
    }

    public function delete($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$task) {
            return response()->json(['message' => 'Task tidak ditemukan atau tidak diizinkan.'], 404);
        }

        if ($task->image && \Storage::disk('public')->exists($task->image)) {
            \Storage::disk('public')->delete($task->image);
        }

        $task->delete();

        return response()->json(['message' => 'Task berhasil dihapus.']);
    }

    public function statusDone($id) {
        $task = Task::findOrFail($id);
        $task->status = "done";
        $task->save();

        return response()->json([
            'message' => 'Status berhasil di update'
        ]);
    }

    public function statusPending($id) {
        $task = Task::findOrFail($id);
        $task->status = "pending";
        $task->save();

        return response()->json([
            'message' => 'Status berhasil di update'
        ]);
    }
}
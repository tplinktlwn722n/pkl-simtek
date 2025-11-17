<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Events\TaskCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskWebController extends Controller
{
    public function index()
    {
        $tasks = Task::with(['admin:id,name', 'assignedUser:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tugas.index', compact('tasks'));
    }

    public function create()
    {
        return view('tugas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'admin_id' => Auth::id(),
            'status' => 'pending',
            'offered_at' => now(),
        ]);

        // Broadcast ke semua user yang online
        broadcast(new TaskCreated($task))->toOthers();

        return redirect()->route('tasks.index')
            ->with('success', 'Tugas berhasil dibuat dan dikirim ke user');
    }

    public function show($id)
    {
        $task = Task::with(['admin', 'assignedUser'])->findOrFail($id);
        return view('tugas.show', compact('task'));
    }
}

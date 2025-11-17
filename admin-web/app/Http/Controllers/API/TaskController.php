<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Events\TaskCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskController extends Controller
{
    // Admin: Buat tugas baru
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

        // Broadcast ke semua user yang online (check-in hari ini dan belum check-out)
        broadcast(new TaskCreated($task))->toOthers();

        // Schedule auto-assign setelah 30 detik jika tidak ada yang terima
        dispatch(function() use ($task) {
            sleep(30); // 30 detik
            $task->refresh();
            
            if ($task->status === 'pending') {
                $this->autoAssignTask($task->id);
            }
        })->afterResponse();

        return response()->json([
            'message' => 'Tugas berhasil dibuat dan dikirim',
            'task' => $task
        ], 201);
    }

    // User: Lihat tugas yang tersedia (belum ada yang ambil)
    public function available()
    {
        $userId = Auth::id();
        
        // Cek apakah user sedang mengerjakan tugas
        $hasActiveTask = Task::where('assigned_to', $userId)
            ->where('status', 'accepted')
            ->exists();

        if ($hasActiveTask) {
            return response()->json([
                'message' => 'Anda sedang mengerjakan tugas',
                'tasks' => []
            ]);
        }

        $tasks = Task::where('status', 'pending')
            ->with('admin:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    // User: Lihat tugas saya (yang sedang dikerjakan)
    public function myTasks()
    {
        $tasks = Task::where('assigned_to', Auth::id())
            ->whereIn('status', ['accepted', 'completed'])
            ->with('admin:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    // User: Terima tugas
    public function accept($id)
    {
        $task = Task::findOrFail($id);

        if ($task->status !== 'pending') {
            return response()->json([
                'message' => 'Tugas sudah diambil orang lain'
            ], 400);
        }

        // Cek apakah user sedang mengerjakan tugas lain
        $hasActiveTask = Task::where('assigned_to', Auth::id())
            ->where('status', 'accepted')
            ->exists();

        if ($hasActiveTask) {
            return response()->json([
                'message' => 'Anda masih memiliki tugas yang belum selesai'
            ], 400);
        }

        $task->update([
            'assigned_to' => Auth::id(),
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil diterima',
            'task' => $task
        ]);
    }

    // User: Tolak tugas
    public function reject($id)
    {
        $task = Task::findOrFail($id);

        if ($task->status !== 'pending') {
            return response()->json([
                'message' => 'Tugas sudah diambil orang lain'
            ], 400);
        }

        // Tidak perlu assign, tetap pending untuk user lain
        return response()->json([
            'message' => 'Tugas ditolak'
        ]);
    }

    // User: Selesaikan tugas
    public function complete($id)
    {
        $task = Task::where('id', $id)
            ->where('assigned_to', Auth::id())
            ->where('status', 'accepted')
            ->firstOrFail();

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil diselesaikan',
            'task' => $task
        ]);
    }

    // Admin: Lihat semua tugas
    public function index()
    {
        $tasks = Task::with(['admin:id,name', 'assignedUser:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    // Auto assign tugas ke user online yang idle (dipanggil setelah 5 menit)
    private function autoAssignTask($taskId)
    {
        $task = Task::find($taskId);
        
        if (!$task || $task->status !== 'pending') {
            return;
        }

        // Cari user yang online (check-in hari ini, belum check-out)
        // dan tidak sedang mengerjakan tugas
        $onlineUsers = DB::table('users')
            ->join('attendances', 'users.id', '=', 'attendances.user_id')
            ->whereDate('attendances.check_in_time', today())
            ->whereNull('attendances.check_out_time')
            ->whereNotIn('users.id', function($query) {
                $query->select('assigned_to')
                    ->from('tasks')
                    ->where('status', 'accepted')
                    ->whereNotNull('assigned_to');
            })
            ->pluck('users.id')
            ->toArray();

        if (empty($onlineUsers)) {
            // Tidak ada user online yang idle, tugas tetap pending
            return;
        }

        // Random pilih user
        $randomUserId = $onlineUsers[array_rand($onlineUsers)];

        $task->update([
            'assigned_to' => $randomUserId,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }
}

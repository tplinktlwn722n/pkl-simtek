<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Get user's attendance history
     */
    public function index(Request $request): JsonResponse
    {
        $attendances = Attendance::where('user_id', $request->user()->id)
            ->with('user:id,name,email')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Attendance history retrieved successfully',
            'data' => $attendances,
        ]);
    }

    /**
     * Check in attendance
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already checked in today
        $today = now()->startOfDay();
        $existingAttendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today',
                'data' => $existingAttendance,
            ], 400);
        }

        $checkInTime = now();
        $workStartTime = now()->setTime(8, 0, 0); // 08:00 AM
        $status = $checkInTime->gt($workStartTime) ? 'late' : 'present';

        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'check_in_time' => $checkInTime,
            'status' => $status,
            'location' => $request->location,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check in successful',
            'data' => $attendance->load('user:id,name,email'),
        ], 201);
    }

    /**
     * Check out attendance
     */
    public function checkOut(Request $request): JsonResponse
    {
        $today = now()->startOfDay();
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('check_in_time', $today)
            ->whereNull('check_out_time')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No active check-in found for today',
            ], 404);
        }

        $attendance->update([
            'check_out_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check out successful',
            'data' => $attendance->load('user:id,name,email'),
        ]);
    }

    /**
     * Get today's attendance status
     */
    public function todayStatus(Request $request): JsonResponse
    {
        $today = now()->startOfDay();
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('check_in_time', $today)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Today status retrieved',
            'data' => [
                'has_checked_in' => $attendance ? true : false,
                'attendance' => $attendance ? $attendance->load('user:id,name,email') : null,
            ],
        ]);
    }

    /**
     * Get attendance history with filters
     */
    public function history(Request $request): JsonResponse
    {
        $query = Attendance::where('user_id', $request->user()->id)
            ->with('user:id,name,email')
            ->orderBy('check_in_time', 'desc');

        // Filter by month and year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('check_in_time', $request->month)
                  ->whereYear('check_in_time', $request->year);
        }

        $attendances = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Attendance history retrieved successfully',
            'data' => $attendances,
        ]);
    }

    /**
     * Export user's attendance to Excel (download from mobile)
     */
    public function exportUser(Request $request)
    {
        // Return CSV format for easy mobile download
        $attendances = Attendance::where('user_id', $request->user()->id)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $csvData = "No,Tanggal,Waktu Masuk,Waktu Keluar,Status,Lokasi\n";
        
        foreach ($attendances as $index => $attendance) {
            $status = $attendance->status === 'present' ? 'Hadir' : 
                     ($attendance->status === 'late' ? 'Terlambat' : 'Tidak Hadir');
            
            $csvData .= sprintf(
                "%d,%s,%s,%s,%s,%s\n",
                $index + 1,
                $attendance->check_in_time->format('Y-m-d'),
                $attendance->check_in_time->format('H:i:s'),
                $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-',
                $status,
                $attendance->location ?? '-'
            );
        }

        $filename = 'riwayat_absensi_' . $request->user()->name . '_' . now()->format('Ymd') . '.csv';

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

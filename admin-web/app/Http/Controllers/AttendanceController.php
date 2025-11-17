<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('user:id,name,email')
            ->orderBy('check_in_time', 'desc');

        // Filter by date if provided
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        }

        // Filter by user if provided
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(20);

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Export attendances to CSV
     */
    public function export(Request $request)
    {
        $query = Attendance::with('user:id,name,email')
            ->orderBy('check_in_time', 'desc');

        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->get();

        // Generate CSV
        $filename = 'absensi_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['No', 'Nama', 'Email', 'Tanggal', 'Masuk', 'Keluar', 'Status', 'Lokasi']);
            
            // Data rows
            foreach ($attendances as $index => $attendance) {
                $status = $attendance->status === 'present' ? 'Hadir' : 
                         ($attendance->status === 'late' ? 'Terlambat' : 'Tidak Hadir');
                         
                fputcsv($file, [
                    $index + 1,
                    $attendance->user->name ?? '-',
                    $attendance->user->email ?? '-',
                    $attendance->check_in_time->format('Y-m-d'),
                    $attendance->check_in_time->format('H:i:s'),
                    $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-',
                    $status,
                    $attendance->location ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import attendances from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Skip header row
            fgetcsv($handle);
            
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                // Skip if not enough columns
                if (count($data) < 6) continue;
                
                // Find user by email
                $user = \App\Models\User::where('email', $data[2])->first();
                if (!$user) continue;
                
                // Parse status
                $statusMap = [
                    'Hadir' => 'present',
                    'Terlambat' => 'late',
                    'Tidak Hadir' => 'absent',
                ];
                $status = $statusMap[$data[6]] ?? 'present';
                
                // Create attendance
                Attendance::create([
                    'user_id' => $user->id,
                    'check_in_time' => \Carbon\Carbon::parse($data[3] . ' ' . $data[4]),
                    'check_out_time' => $data[5] !== '-' ? \Carbon\Carbon::parse($data[3] . ' ' . $data[5]) : null,
                    'status' => $status,
                    'location' => $data[7] ?? 'Imported',
                ]);
                
                $imported++;
            }
            
            fclose($handle);

            return redirect()->route('attendances.index')
                ->with('success', "Berhasil import {$imported} data absensi!");
        } catch (\Exception $e) {
            return redirect()->route('attendances.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Show print view
     */
    public function print(Request $request)
    {
        $query = Attendance::with('user:id,name,email')
            ->orderBy('check_in_time', 'desc');

        // Apply same filters
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->get();
        $filters = $request->only(['date', 'status']);

        return view('attendances.print', compact('attendances', 'filters'));
    }
}

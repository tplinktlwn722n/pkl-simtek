<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Absensi</title>
    <style>
        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2563eb;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .filter-info {
            background: #f3f4f6;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background: #2563eb;
            color: white;
        }
        
        table th,
        table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-present {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-late {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-absent {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        
        .btn-print {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn-print:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button class="btn-print" onclick="window.print()">🖨️ Print / Cetak</button>
        <button class="btn-print" onclick="window.close()" style="background: #6b7280;">✖ Tutup</button>
    </div>

    <div class="header">
        <h1>LAPORAN DATA ABSENSI</h1>
        <p>Sistem Informasi Manajemen PKL</p>
        <p>Tanggal Cetak: {{ now()->format('d F Y H:i') }}</p>
    </div>

    @if(isset($filters['date']) || isset($filters['status']))
    <div class="filter-info">
        <strong>Filter:</strong>
        @if(isset($filters['date']))
            Tanggal: {{ \Carbon\Carbon::parse($filters['date'])->format('d F Y') }}
        @endif
        @if(isset($filters['status']))
            | Status: 
            @if($filters['status'] === 'present') Hadir
            @elseif($filters['status'] === 'late') Terlambat
            @elseif($filters['status'] === 'absent') Tidak Hadir
            @endif
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Nama</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Masuk</th>
                <th style="width: 10%;">Keluar</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 10%;">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->user->email }}</td>
                <td>{{ $attendance->check_in_time->format('d/m/Y') }}</td>
                <td>{{ $attendance->check_in_time->format('H:i') }}</td>
                <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $attendance->status }}">
                        @if($attendance->status === 'present') Hadir
                        @elseif($attendance->status === 'late') Terlambat
                        @elseif($attendance->status === 'absent') Tidak Hadir
                        @else {{ ucfirst($attendance->status) }}
                        @endif
                    </span>
                </td>
                <td>{{ $attendance->location ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Total Data: {{ count($attendances) }}</strong></p>
        <div style="margin-top: 60px;">
            <p>_____________________</p>
            <p>Administrator</p>
        </div>
    </div>
</body>
</html>

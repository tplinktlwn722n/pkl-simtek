<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Absensi</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            font-weight: bold;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 10pt;
        }
        
        .info {
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            text-align: left;
            font-size: 10pt;
        }
        
        td {
            padding: 6px 8px;
            border: 1px solid #000;
            font-size: 9pt;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature {
            margin-top: 60px;
            display: inline-block;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            margin-top: 10px;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .status-present { background-color: #d4edda; color: #155724; }
        .status-late { background-color: #fff3cd; color: #856404; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        
        .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .btn-print:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">🖨️ Cetak Laporan</button>
    
    <div class="header">
        <h1>LAPORAN DATA ABSENSI</h1>
        <p>PT. SIMTEK INDONESIA</p>
        <p>Jl. Simpar, Panjalu, Ciamis</p>
    </div>
    
    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ now()->format('d F Y, H:i') }}</p>
        @if(isset($filters['date']) && $filters['date'])
            <p><strong>Filter Tanggal:</strong> {{ \Carbon\Carbon::parse($filters['date'])->format('d F Y') }}</p>
        @endif
        @if(isset($filters['status']) && $filters['status'])
            <p><strong>Filter Status:</strong> {{ ucfirst($filters['status']) }}</p>
        @endif
        <p><strong>Total Records:</strong> {{ $attendances->count() }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Nama</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Masuk</th>
                <th style="width: 10%;">Keluar</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 15%;">Lokasi</th>
                <th style="width: 18%;">GPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $attendance->user->name }}</strong><br>
                    <small>{{ $attendance->user->email }}</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}</td>
                <td>
                    @if($attendance->check_out_time)
                        {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $attendance->status }}">
                        {{ ucfirst($attendance->status) }}
                    </span>
                </td>
                <td>{{ $attendance->location ?? '-' }}</td>
                <td style="font-size: 8pt;">
                    @if($attendance->latitude && $attendance->longitude)
                        {{ number_format($attendance->latitude, 6) }},<br>
                        {{ number_format($attendance->longitude, 6) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data absensi</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Ciamis, {{ now()->format('d F Y') }}</p>
        <div class="signature">
            <p>Mengetahui,</p>
            <div style="height: 60px;"></div>
            <div class="signature-line"></div>
            <p><strong>HRD Manager</strong></p>
        </div>
    </div>
</body>
</html>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Action Buttons & Filters -->
                    <div class="mb-6 flex justify-between items-end flex-wrap gap-4">
                        <div class="flex gap-2">
                            <a href="{{ route('attendances.export', request()->all()) }}" style="display: inline-flex; align-items: center; padding: 10px 18px; background-color: #10b981; color: white; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10b981'">
                                <svg style="width: 18px; height: 18px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export CSV
                            </a>
                            <button onclick="document.getElementById('importModal').classList.remove('hidden')" style="display: inline-flex; align-items: center; padding: 10px 18px; background-color: #3b82f6; color: white; font-size: 14px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'">
                                <svg style="width: 18px; height: 18px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Import CSV
                            </button>
                            <button onclick="window.print()" style="display: inline-flex; align-items: center; padding: 10px 18px; background-color: #8b5cf6; color: white; font-size: 14px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#7c3aed'" onmouseout="this.style.backgroundColor='#8b5cf6'">
                                <svg style="width: 18px; height: 18px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </button>
                        </div>

                        <form method="GET" action="{{ route('attendances.index') }}" id="filterForm" class="flex gap-3">
                            <div>
                                <input type="date" name="date" id="filterDate" value="{{ request('date') }}" placeholder="Pilih Tanggal"
                                    style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; min-width: 160px; background: white;" 
                                    onchange="this.form.submit()">
                            </div>
                            <div>
                                <select name="status" id="filterStatus" 
                                    style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; min-width: 140px; background: white;"
                                    onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Attendance Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Pengguna
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Masuk
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Keluar
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Lokasi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($attendances as $attendance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $attendance->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->user->email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->check_in_time->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $attendance->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                            @if($attendance->status === 'present')
                                                Hadir
                                            @elseif($attendance->status === 'late')
                                                Terlambat
                                            @elseif($attendance->status === 'absent')
                                                Tidak Hadir
                                            @else
                                                {{ ucfirst($attendance->status) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $attendance->location ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('attendances.show', $attendance->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No attendance records found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Import Data Absensi</h3>
                <form action="{{ route('attendances.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih File CSV</label>
                        <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <p class="mt-1 text-xs text-gray-500">Format: CSV, TXT (Max 5MB)</p>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div id="printModal" class="hidden fixed inset-0 z-50" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            
            <!-- Modal Content -->
            <div id="modalContent" class="bg-white rounded-lg shadow-xl w-full" style="max-width: 900px;">
                
                <!-- Action Buttons -->
                <div class="px-6 py-3 bg-white border-b border-gray-200 no-print flex justify-end gap-2">
                    <button onclick="printContent()" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700">
                        Print
                    </button>
                    <button onclick="closePrintModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50">
                        Cancel
                    </button>
                </div>

                <!-- Print Content -->
                <div class="p-6" style="max-height: 75vh; overflow-y: auto;">
                    <div id="printContent">
                <style>
                    @keyframes modalFadeIn {
                        from { opacity: 0; transform: scale(0.95); }
                        to { opacity: 1; transform: scale(1); }
                    }
                    #modalContent {
                        animation: modalFadeIn 0.2s ease-out;
                    }
                    @media print {
                        .no-print { display: none !important; }
                        @page { margin: 1.5cm; }
                        body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                    }
                    .print-header {
                        text-align: center;
                        margin-bottom: 30px;
                        padding-bottom: 20px;
                        border-bottom: 2px solid #000;
                    }
                    .print-header h1 {
                        margin: 0 0 8px 0;
                        font-size: 20px;
                        color: #000;
                        font-weight: 600;
                        text-transform: uppercase;
                    }
                    .print-header p {
                        margin: 3px 0;
                        color: #666;
                        font-size: 12px;
                    }
                    .print-summary {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        gap: 12px;
                        margin-bottom: 25px;
                        padding: 20px;
                        background: #f9fafb;
                        border: 1px solid #e5e7eb;
                        border-radius: 4px;
                    }
                    .print-summary-item {
                        text-align: center;
                        padding: 12px;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 3px;
                    }
                    .print-summary-value {
                        font-size: 24px;
                        font-weight: 600;
                        margin-bottom: 4px;
                        color: #000;
                    }
                    .print-summary-label {
                        font-size: 11px;
                        color: #6b7280;
                        font-weight: 500;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .print-filter-info {
                        background: #f3f4f6;
                        padding: 10px 15px;
                        margin-bottom: 20px;
                        border: 1px solid #d1d5db;
                        border-radius: 3px;
                        font-size: 12px;
                        color: #374151;
                    }
                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                        font-size: 12px;
                        border: 1px solid #d1d5db;
                    }
                    .print-table thead {
                        background: #f9fafb;
                    }
                    .print-table th {
                        padding: 10px 8px;
                        text-align: left;
                        font-size: 11px;
                        font-weight: 600;
                        text-transform: uppercase;
                        color: #374151;
                        border-bottom: 2px solid #d1d5db;
                        border-right: 1px solid #e5e7eb;
                    }
                    .print-table th:last-child {
                        border-right: none;
                    }
                    .print-table td {
                        padding: 8px;
                        border-bottom: 1px solid #e5e7eb;
                        border-right: 1px solid #e5e7eb;
                        color: #1f2937;
                    }
                    .print-table td:last-child {
                        border-right: none;
                    }
                    .print-table tbody tr:hover {
                        background: #f9fafb;
                    }
                    .print-status-badge {
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 10px;
                        font-weight: 600;
                        display: inline-block;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                    }
                    .print-status-present {
                        background: #f0fdf4;
                        color: #166534;
                        border: 1px solid #bbf7d0;
                    }
                    .print-status-late {
                        background: #fffbeb;
                        color: #92400e;
                        border: 1px solid #fde68a;
                    }
                    .print-status-absent {
                        background: #fef2f2;
                        color: #991b1b;
                        border: 1px solid #fecaca;
                    }
                    .print-footer {
                        margin-top: 30px;
                        padding-top: 15px;
                        border-top: 1px solid #d1d5db;
                        font-size: 12px;
                        color: #6b7280;
                    }
                    .print-footer-signature {
                        margin-top: 50px;
                        text-align: right;
                    }
                    .print-footer-signature p {
                        margin: 3px 0;
                        color: #374151;
                    }
                </style>

                <div class="print-header">
                    <h1>Laporan Rekap Absensi</h1>
                    <p>Sistem Informasi Manajemen PKL</p>
                    <p>Dicetak pada: <span id="printDate"></span></p>
                </div>

                <!-- Summary Statistics -->
                <div class="print-summary">
                    <div class="print-summary-item">
                        <div class="print-summary-value" id="summaryTotalEmployees">0</div>
                        <div class="print-summary-label">Total Karyawan</div>
                    </div>
                    <div class="print-summary-item">
                        <div class="print-summary-value" id="summaryTotalPresent">0</div>
                        <div class="print-summary-label">Hadir</div>
                    </div>
                    <div class="print-summary-item">
                        <div class="print-summary-value" id="summaryTotalLate">0</div>
                        <div class="print-summary-label">Terlambat</div>
                    </div>
                    <div class="print-summary-item">
                        <div class="print-summary-value" id="summaryTotalAbsent">0</div>
                        <div class="print-summary-label">Tidak Hadir</div>
                    </div>
                </div>

                <div id="filterInfo" class="print-filter-info" style="display: none;">
                    <strong>Filter:</strong> <span id="filterText"></span>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 18%;">Nama</th>
                            <th style="width: 20%;">Email</th>
                            <th style="width: 12%;">Tanggal</th>
                            <th style="width: 10%;">Masuk</th>
                            <th style="width: 10%;">Keluar</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 13%;">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody id="printTableBody">
                        <!-- Data akan diisi via JavaScript -->
                    </tbody>
                </table>

                <div class="print-footer">
                    <p><strong>Total Data: <span id="totalData">0</span> record</strong></p>
                    <div class="print-footer-signature">
                        <p>____________________</p>
                        <p><strong>Administrator</strong></p>
                    </div>
                </div>
            </div>
                </div>
                
            </div>
        </div>
    </div>

    <script>
        function openPrintModal() {
            // Ambil data dari tabel yang sedang ditampilkan
            const tableRows = document.querySelectorAll('tbody.bg-white tr');
            const printTableBody = document.getElementById('printTableBody');
            printTableBody.innerHTML = '';
            
            // Inisialisasi counter statistik
            let dataCount = 0;
            let totalPresent = 0;
            let totalLate = 0;
            let totalAbsent = 0;
            const uniqueUsers = new Set();
            
            tableRows.forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 1) { // Pastikan bukan baris "No records"
                    dataCount++;
                    const newRow = document.createElement('tr');
                    
                    // No
                    const noCell = document.createElement('td');
                    noCell.style.textAlign = 'center';
                    noCell.textContent = dataCount;
                    newRow.appendChild(noCell);
                    
                    // Nama (dari cell 0)
                    const namaCell = document.createElement('td');
                    const namaText = cells[0].querySelector('.text-sm.font-medium').textContent.trim();
                    namaCell.textContent = namaText;
                    uniqueUsers.add(namaText);
                    newRow.appendChild(namaCell);
                    
                    // Email (dari cell 0)
                    const emailCell = document.createElement('td');
                    emailCell.textContent = cells[0].querySelector('.text-sm.text-gray-500').textContent.trim();
                    newRow.appendChild(emailCell);
                    
                    // Tanggal (dari cell 1 - check_in_time)
                    const tanggalCell = document.createElement('td');
                    const checkInTime = cells[1].textContent.trim();
                    tanggalCell.textContent = checkInTime.split(' ')[0];
                    newRow.appendChild(tanggalCell);
                    
                    // Masuk (dari cell 1 - jam saja)
                    const masukCell = document.createElement('td');
                    masukCell.textContent = checkInTime.split(' ')[1];
                    newRow.appendChild(masukCell);
                    
                    // Keluar (dari cell 2)
                    const keluarCell = document.createElement('td');
                    const checkOutTime = cells[2].textContent.trim();
                    keluarCell.textContent = checkOutTime === '-' ? '-' : checkOutTime.split(' ')[1];
                    newRow.appendChild(keluarCell);
                    
                    // Status (dari cell 3)
                    const statusCell = document.createElement('td');
                    const statusBadge = cells[3].querySelector('span');
                    const statusText = statusBadge.textContent.trim();
                    
                    let statusClass = '';
                    if (statusBadge.classList.contains('bg-green-100')) {
                        statusClass = 'print-status-present';
                        totalPresent++;
                    } else if (statusBadge.classList.contains('bg-yellow-100')) {
                        statusClass = 'print-status-late';
                        totalLate++;
                    } else {
                        statusClass = 'print-status-absent';
                        totalAbsent++;
                    }
                    
                    statusCell.innerHTML = `<span class="print-status-badge ${statusClass}">${statusText}</span>`;
                    newRow.appendChild(statusCell);
                    
                    // Lokasi (dari cell 4)
                    const lokasiCell = document.createElement('td');
                    lokasiCell.textContent = cells[4].textContent.trim();
                    newRow.appendChild(lokasiCell);
                    
                    printTableBody.appendChild(newRow);
                }
            });
            
            // Update total data
            document.getElementById('totalData').textContent = dataCount;
            
            // Update statistik summary
            document.getElementById('summaryTotalEmployees').textContent = uniqueUsers.size;
            document.getElementById('summaryTotalPresent').textContent = totalPresent;
            document.getElementById('summaryTotalLate').textContent = totalLate;
            document.getElementById('summaryTotalAbsent').textContent = totalAbsent;
            
            // Set tanggal cetak
            const now = new Date();
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dateStr = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            document.getElementById('printDate').textContent = dateStr;
            
            // Tampilkan filter info jika ada
            const filterDate = document.getElementById('filterDate').value;
            const filterStatus = document.getElementById('filterStatus').value;
            let filterText = '';
            
            if (filterDate || filterStatus) {
                if (filterDate) {
                    const dateObj = new Date(filterDate);
                    filterText += `Tanggal: ${dateObj.getDate()} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
                }
                if (filterStatus) {
                    const statusMap = {
                        'present': 'Hadir',
                        'late': 'Terlambat',
                        'absent': 'Tidak Hadir'
                    };
                    filterText += (filterText ? ' | ' : '') + 'Status: ' + statusMap[filterStatus];
                }
                document.getElementById('filterInfo').style.display = 'block';
                document.getElementById('filterText').textContent = filterText;
            } else {
                document.getElementById('filterInfo').style.display = 'none';
            }
            
            // Tampilkan modal
            document.getElementById('printModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closePrintModal() {
            document.getElementById('printModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        function printContent() {
            window.print();
        }
        
        // Tutup modal jika klik di luar
        document.getElementById('printModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePrintModal();
            }
        });
        
        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePrintModal();
            }
        });
    </script>
</x-app-layout>

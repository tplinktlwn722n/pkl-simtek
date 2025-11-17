<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Detail Absensi</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('attendances.index') }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition-colors border border-gray-300 dark:border-gray-600" title="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                
                <!-- User -->
                <div class="mb-6 pb-6 border-b">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $attendance->user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $attendance->user->email }}</p>
                </div>

                <!-- Info -->
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $attendance->check_in_time->format('d M Y') }}</span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Waktu Masuk</span>
                        <span class="text-sm font-bold text-green-600">{{ $attendance->check_in_time->format('H:i:s') }}</span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $attendance->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $attendance->status === 'present' ? 'Hadir' : ($attendance->status === 'late' ? 'Terlambat' : 'Tidak Hadir') }}
                        </span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Lokasi</span>
                        <span class="text-sm text-gray-900 dark:text-white text-right max-w-xs">{{ $attendance->location ?? '-' }}</span>
                    </div>

                    @if($attendance->notes)
                    <div class="py-3">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400 block mb-2">Catatan</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded">{{ $attendance->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Photos -->
                @if(!empty($attendance->photo_check_in))
                <div class="pt-6 border-t">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Bukti Foto</h4>
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Foto Masuk</p>
                        <img src="{{ Storage::url($attendance->photo_check_in) }}" alt="Check In" 
                            class="w-full h-64 object-cover rounded border cursor-pointer hover:opacity-75 transition" 
                            onclick="showPhoto('{{ Storage::url($attendance->photo_check_in) }}', 'Foto Check In')">
                    </div>
                </div>
                @else
                <div class="pt-6 border-t">
                    <p class="text-sm text-gray-500 italic">Tidak ada bukti foto</p>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="photoModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4" onclick="closePhoto()">
        <div class="relative max-w-4xl w-full">
            <button onclick="closePhoto()" class="absolute -top-12 right-0 text-white text-4xl hover:text-gray-300">×</button>
            <img id="photoImg" src="" alt="" class="w-full h-auto rounded-lg">
            <p id="photoTitle" class="text-white text-center mt-2"></p>
        </div>
    </div>

    <script>
        function showPhoto(src, title) {
            document.getElementById('photoImg').src = src;
            document.getElementById('photoTitle').textContent = title;
            document.getElementById('photoModal').classList.remove('hidden');
        }
        function closePhoto() {
            document.getElementById('photoModal').classList.add('hidden');
        }
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closePhoto(); });
    </script>
</x-app-layout>

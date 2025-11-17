<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('attendances.index') }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                            ← Kembali ke Daftar
                        </a>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Pengguna</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $attendance->user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $attendance->user->email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Waktu Masuk</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $attendance->check_in_time->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Waktu Keluar</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : 'Belum absen keluar' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <p class="mt-1">
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
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $attendance->location ?? '-' }}</p>
                            </div>
                        </div>

                        @if($attendance->notes)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $attendance->notes }}</p>
                        </div>
                        @endif

                        @if($attendance->check_in_time && $attendance->check_out_time)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Durasi</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $attendance->check_in_time->diffForHumans($attendance->check_out_time, true) }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

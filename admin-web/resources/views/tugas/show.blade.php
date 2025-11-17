<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Tugas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                            ← Kembali ke Daftar Tugas
                        </a>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $task->title }}</h3>
                            <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>Dibuat: {{ $task->created_at->format('d M Y H:i') }}</span>
                                @if ($task->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Menunggu
                                    </span>
                                @elseif ($task->status === 'accepted')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Dikerjakan
                                    </span>
                                @elseif ($task->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi / Pesan:</h4>
                            <p class="text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $task->description }}</p>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dikirim Oleh:</h4>
                                <p class="text-gray-900 dark:text-gray-100">{{ $task->admin->name ?? '-' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diterima Oleh:</h4>
                                <p class="text-gray-900 dark:text-gray-100">{{ $task->assignedUser->name ?? 'Belum ada' }}</p>
                            </div>
                        </div>

                        @if ($task->accepted_at)
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diterima Pada:</h4>
                                <p class="text-gray-900 dark:text-gray-100">{{ $task->accepted_at->format('d M Y H:i:s') }}</p>
                            </div>
                        @endif

                        @if ($task->completed_at)
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diselesaikan Pada:</h4>
                                <p class="text-gray-900 dark:text-gray-100">{{ $task->completed_at->format('d M Y H:i:s') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

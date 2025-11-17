<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kirim Tugas Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('tasks.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Judul Tugas <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('title') border-red-500 @enderror"
                                value="{{ old('title') }}"
                                placeholder="Misal: Sortir Dokumen PKL"
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deskripsi / Pesan <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                name="description" 
                                id="description" 
                                required
                                rows="6"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('description') border-red-500 @enderror"
                                placeholder="Jelaskan detail tugas yang harus dikerjakan..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Tugas akan dikirim ke semua siswa PKL yang sedang online. Jika tidak ada yang menerima dalam 30 detik, sistem akan otomatis menugaskan ke siswa yang tersedia.</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('tasks.index') }}" style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #e5e7eb; color: #374151; font-size: 13px; font-weight: 600; border-radius: 6px; text-decoration: none; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#d1d5db'" onmouseout="this.style.backgroundColor='#e5e7eb'">
                                Batal
                            </a>
                            <button type="submit" style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #2563eb; color: white; font-size: 13px; font-weight: 600; border-radius: 6px; border: none; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                                Kirim Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

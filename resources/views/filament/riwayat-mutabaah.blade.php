<div class="space-y-4">
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
        Riwayat setoran untuk: <span class="font-semibold text-gray-800 dark:text-white">{{ $record->name }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3">Tanggal Setor</th>
                    <th scope="col" class="px-4 py-3">Progress</th>
                    <th scope="col" class="px-4 py-3">Halaman</th>
                    <th scope="col" class="px-4 py-3">Juz</th>
                    <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $item)
                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $item->pertama_setor ? $item->pertama_setor->format('d M Y') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            Surah: {{ $item->from_surah }} Ayat: {{ $item->from_ayat }} s.d {{ $item->to_surah }} Ayat: {{ $item->to_ayat }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->total_halaman }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $item->total_juz }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button 
                                type="button"
                                wire:click="deleteMutabaah({{ $item->id }})" 
                                wire:confirm="Apakah Anda yakin ingin menghapus data setoran ini?"
                                class="font-medium text-danger-600 dark:text-danger-500 hover:underline"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            Belum ada riwayat setoran mutabaah quran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
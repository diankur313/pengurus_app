<x-filament-panels::page>
    <div x-data="{ tab: 'lama' }">
        <x-filament::tabs class="mb-6">
            <x-filament::tabs.item
                alpine-active="tab === 'lama'"
                x-on:click="tab = 'lama'"
                icon="heroicon-o-users"
            >
                Data Member Lama
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="tab === 'ppab'"
                x-on:click="tab = 'ppab'"
                icon="heroicon-o-academic-cap"
            >
                Data > 2024
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div x-show="tab === 'lama'">
            {{ $this->table }}
        </div>

        <div x-show="tab === 'ppab'" style="display: none;" x-cloak>
            @livewire('ppab-table')
        </div>
    </div>

    <!-- Modal Preview Foto (Pure Alpine) -->
    <x-filament::modal id="preview-photo-modal" width="md">
        <div class="flex justify-center" x-data="{ url: '' }" x-on:open-preview.window="url = $event.detail.url">
            <img :src="url" class="rounded-lg max-w-full h-auto" style="max-height: 400px;" alt="Preview Foto">
        </div>
    </x-filament::modal>
</x-filament-panels::page>

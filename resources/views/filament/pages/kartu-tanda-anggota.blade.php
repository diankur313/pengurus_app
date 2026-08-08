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
            <div class="flex items-center gap-4 mb-4">
                {{ $this->form }}
                
                <x-filament::button 
                    wire:click="downloadSelected" 
                    color="primary"
                    :disabled="count($selectedRows) === 0 && empty($selectedAngkatan)"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="downloadSelected">Download All</span>
                    <span wire:loading wire:target="downloadSelected">Processing...</span>
                </x-filament::button>
            </div>

            {{ $this->table }}
        </div>

        <div x-show="tab === 'ppab'" style="display: none;" x-cloak>
            @livewire('kta-ppab-table')
        </div>
    </div>

    {{-- Modal Preview Foto Profil --}}
    <x-filament::modal id="preview-photo-modal" width="md" x-on:modal-closed.window="if ($event.detail.id === 'preview-photo-modal') { $nextTick(() => { document.body.style.overflow = ''; document.documentElement.style.overflow = '' }) }">
        <div class="flex justify-center" x-data="{ url: '' }" x-on:open-preview.window="url = $event.detail.url">
            <img :src="url" class="rounded-lg max-w-full h-auto" style="max-height: 400px;" alt="Preview Foto">
        </div>
    </x-filament::modal>
</x-filament-panels::page>

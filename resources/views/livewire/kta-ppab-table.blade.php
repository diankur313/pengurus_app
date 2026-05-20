<div>
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

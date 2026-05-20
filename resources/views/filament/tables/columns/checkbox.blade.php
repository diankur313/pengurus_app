<div class="fi-ta-cell px-3 py-4">
    <div class="flex items-center justify-center">
        <input 
            type="checkbox" 
            wire:model.live="selectedRows" 
            value="{{ $getRecord()->id ?? $getRecord()->member_no ?? $getRecord()->id_member }}" 
            class="rounded border-gray-300 shadow-sm text-primary-600 focus:ring-primary-500"
        >
    </div>
</div>

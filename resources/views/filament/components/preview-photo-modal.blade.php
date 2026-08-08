<x-filament::modal id="preview-photo-modal" width="md" x-on:modal-closed.window="if ($event.detail.id === 'preview-photo-modal') { $nextTick(() => { document.body.style.overflow = ''; document.documentElement.style.overflow = '' }) }">
    <div class="flex justify-center" x-data="{ url: '' }" x-on:open-preview.window="url = $event.detail.url">
        <img :src="url" class="rounded-lg max-w-full h-auto" style="max-height: 400px;" alt="Preview Foto">
    </div>
</x-filament::modal>

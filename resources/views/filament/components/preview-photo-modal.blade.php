<x-filament::modal id="preview-photo-modal" width="md">
    <div class="flex justify-center" x-data="{ url: '' }" x-on:open-preview.window="url = $event.detail.url">
        <img :src="url" class="rounded-lg max-w-full h-auto" style="max-height: 400px;" alt="Preview Foto">
    </div>
</x-filament::modal>

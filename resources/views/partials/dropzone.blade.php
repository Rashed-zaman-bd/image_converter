{{-- resources/views/partials/dropzone.blade.php
     Optional variables: $name, $multiple, $accept, $title, $hint --}}
<input type="file"
    name="{{ $name ?? 'image' }}{{ !empty($multiple) ? '[]' : '' }}"
    id="imageInput"
    accept="{{ $accept ?? 'image/jpeg,image/png,image/webp' }}"
    @if (!empty($multiple)) multiple @endif
    class="sr-only">

<label for="imageInput" id="dropArea"
    class="drop-area flex min-h-[260px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center hover:border-brand-500 hover:bg-brand-50 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100">
    <span class="mb-5 grid h-16 w-16 place-items-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/30">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 16V4M7 9l5-5 5 5" />
            <path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
        </svg>
    </span>
    <span class="font-display text-xl font-semibold">{{ $title ?? 'Drop an image here, or click to browse' }}</span>
    <span class="mt-2 text-sm text-slate-500">{{ $hint ?? 'JPG, PNG or WebP. Up to 50MB.' }}</span>
</label>
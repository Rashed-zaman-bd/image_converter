@extends('layouts.app')

@section('title', 'Image Enlarger - Make your images bigger')

@section('content')

    <header class="mb-8 sm:mb-10">
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
            Make your images bigger
        </h1>
        <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
            Enlarge an image to 2x, 3x or 4x its size and download it in the same format.
        </p>
    </header>

    <form action="{{ route('enlarger.process') }}"
        method="POST"
        enctype="multipart/form-data"
        id="toolForm"
        class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:gap-6">

        @csrf

        <!-- Left: upload + preview -->
        <section class="lg:col-span-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6">

                @if ($errors->any())
                    <div role="alert" class="mb-4 space-y-1 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div id="clientError" role="alert" aria-live="polite"
                    class="mb-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"></div>

                @include('partials.dropzone')

                <div id="previewBox" class="hidden">
                    <div class="flex items-center justify-center rounded-2xl bg-slate-100 p-3">
                        <img id="previewImage" alt="Image to enlarge" class="max-h-[420px] w-auto max-w-full rounded-lg object-contain">
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <p id="originalInfo" class="text-sm text-slate-500"></p>
                        <label for="imageInput"
                            class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Change image
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right: options -->
        <aside class="lg:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6 lg:sticky lg:top-24">

                <fieldset>
                    <legend class="font-display text-lg font-semibold">Enlarge by</legend>

                    <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1">
                        @foreach ([2, 3, 4] as $value)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="scale" value="{{ $value }}"
                                    @checked((int) old('scale', 2) === $value) class="peer sr-only">
                                <span class="block rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-500 transition
                                    peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm
                                    peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100">
                                    {{ $value }}x
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <label class="mt-5 flex cursor-pointer items-start gap-2.5 text-sm text-slate-600">
                    <input type="checkbox" name="sharpen" value="1" checked
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                    <span>
                        Sharpen after enlarging
                        <span class="block text-slate-500">Keeps edges crisper in the bigger image.</span>
                    </span>
                </label>

                <p id="summary" class="mt-5 rounded-xl bg-brand-50 px-3 py-2.5 text-sm font-medium text-brand-700">
                    Add an image to see the new size.
                </p>

                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Enlarging makes an image bigger, but it cannot add detail that was never there. Very small images may look soft.
                </p>

                <div class="mt-5 space-y-3">
                    <button type="submit" id="submitButton" disabled
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3.5 font-semibold text-white shadow-sm transition
                        hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100
                        disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                        <span id="submitLabel">Enlarge and download</span>
                    </button>

                    <button type="button" id="resetButton"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-600 transition
                        hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                        Start over
                    </button>
                </div>
            </div>
        </aside>

    </form>

@endsection

@push('scripts')
<script>
    const MAX_SIZE = 50 * 1024 * 1024;
    const MAX_SIDE = 10000;
    const MAX_PIXELS = 60000000;
    const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    const form = document.getElementById('toolForm');
    const imageInput = document.getElementById('imageInput');
    const dropArea = document.getElementById('dropArea');
    const previewBox = document.getElementById('previewBox');
    const previewImage = document.getElementById('previewImage');
    const originalInfo = document.getElementById('originalInfo');
    const clientError = document.getElementById('clientError');
    const summary = document.getElementById('summary');
    const submitButton = document.getElementById('submitButton');
    const submitLabel = document.getElementById('submitLabel');
    const resetButton = document.getElementById('resetButton');

    let previewUrl = null;
    let origW = 0, origH = 0;

    const scale = () => parseInt(document.querySelector('input[name="scale"]:checked').value, 10);

    function formatSize(bytes) {
        return bytes < 1024 * 1024
            ? (bytes / 1024).toFixed(0) + ' KB'
            : (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.remove('hidden');
    }

    function updateSummary() {
        if (!origW) {
            summary.textContent = 'Add an image to see the new size.';
            return;
        }

        const w = origW * scale();
        const h = origH * scale();
        const tooBig = w > MAX_SIDE || h > MAX_SIDE || w * h > MAX_PIXELS;

        summary.textContent = tooBig
            ? `${w} × ${h} px is too large. Choose a smaller scale.`
            : `${origW} × ${origH} px → ${w} × ${h} px`;

        submitButton.disabled = tooBig;
    }

    function loadFile(file) {

        clientError.classList.add('hidden');

        if (!file) return;

        if (!VALID_TYPES.includes(file.type)) {
            showError('Only JPG, PNG and WebP images are allowed.');
            return;
        }

        if (file.size > MAX_SIZE) {
            showError('The image is larger than 50 MB.');
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);

        previewImage.onload = function () {
            origW = previewImage.naturalWidth;
            origH = previewImage.naturalHeight;
            originalInfo.textContent = `${file.name} · ${origW} × ${origH} px · ${formatSize(file.size)}`;
            updateSummary();
        };
        previewImage.src = previewUrl;

        dropArea.classList.add('hidden');
        previewBox.classList.remove('hidden');
        submitButton.disabled = false;
    }

    imageInput.addEventListener('change', function () {
        loadFile(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(type => {
        dropArea.addEventListener(type, e => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });
    });

    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));

    dropArea.addEventListener('drop', function (e) {
        e.preventDefault();
        dropArea.classList.remove('dragover');

        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            loadFile(e.dataTransfer.files[0]);
        }
    });

    document.querySelectorAll('input[name="scale"]').forEach(input => {
        input.addEventListener('change', updateSummary);
    });

    form.addEventListener('submit', function (e) {

        if (!imageInput.files.length) {
            e.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitLabel.innerHTML =
            '<svg class="spinner h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>' +
            '<path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>' +
            '<span>Enlarging…</span>';

        // The response is a file download, so the page stays put
        setTimeout(() => {
            submitLabel.textContent = 'Enlarge and download';
            updateSummary();
        }, 4000);
    });

    resetButton.addEventListener('click', function () {
        imageInput.value = '';
        origW = origH = 0;
        previewImage.removeAttribute('src');
        previewBox.classList.add('hidden');
        dropArea.classList.remove('hidden');
        clientError.classList.add('hidden');
        submitButton.disabled = true;
        updateSummary();
    });
</script>
@endpush
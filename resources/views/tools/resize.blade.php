@extends('layouts.tool')

@section('title', 'Image Resizer - Resize an image to any size')

@section('content')

    <header class="mb-8 sm:mb-10">
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
            Resize an image to any size
        </h1>
        <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
            Set exact pixels or a percentage, keep the proportions, and download the result.
        </p>
    </header>

    <form action="{{ route('resizer.process') }}"
        method="POST"
        enctype="multipart/form-data"
        id="resizeForm"
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

                <input type="file" name="image" id="imageInput"
                    accept="image/jpeg,image/png,image/webp" class="sr-only">

                <label for="imageInput" id="dropArea"
                    class="drop-area flex min-h-[260px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center hover:border-brand-500 hover:bg-brand-50 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100">
                    <span class="mb-5 grid h-16 w-16 place-items-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/30">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 16V4M7 9l5-5 5 5" />
                            <path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
                        </svg>
                    </span>
                    <span class="font-display text-xl font-semibold">Drop an image here, or click to browse</span>
                    <span class="mt-2 text-sm text-slate-500">JPG, PNG or WebP. Up to 50MB.</span>
                </label>

                <div id="previewBox" class="hidden">
                    <div class="flex items-center justify-center rounded-2xl bg-slate-100 p-3">
                        <img id="previewImage" alt="Image to resize" class="max-h-[420px] w-auto max-w-full rounded-lg object-contain">
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
                    <legend class="font-display text-lg font-semibold">Resize by</legend>

                    <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
                        @foreach (['pixels' => 'Pixels', 'percent' => 'Percentage'] as $value => $label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="mode" value="{{ $value }}"
                                    @checked(old('mode', 'pixels') === $value) class="peer sr-only">
                                <span class="block rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-500 transition
                                    peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm
                                    peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <!-- Pixels -->
                <div id="pixelFields" class="mt-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="width" class="mb-1.5 block text-sm font-medium text-slate-600">Width (px)</label>
                            <input type="number" name="width" id="width" min="1" max="10000" value="{{ old('width') }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div>
                            <label for="height" class="mb-1.5 block text-sm font-medium text-slate-600">Height (px)</label>
                            <input type="number" name="height" id="height" min="1" max="10000" value="{{ old('height') }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-100">
                        </div>
                    </div>

                    <label class="mt-4 flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="keep_ratio" id="keepRatio" value="1" checked
                            class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                        Keep aspect ratio
                    </label>
                </div>

                <!-- Percentage -->
                <div id="percentFields" class="mt-5 hidden">
                    <label for="percent" class="mb-1.5 block text-sm font-medium text-slate-600">Scale (%)</label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="percentRange" min="1" max="200" value="50" class="w-full accent-brand-500">
                        <input type="number" name="percent" id="percent" min="1" max="500" value="50"
                            class="w-24 rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-100">
                    </div>
                </div>

                <p id="summary" class="mt-5 rounded-xl bg-brand-50 px-3 py-2.5 text-sm font-medium text-brand-700">
                    Add an image to see the new size.
                </p>

                <div class="mt-5 space-y-3">
                    <button type="submit" id="submitButton" disabled
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3.5 font-semibold text-white shadow-sm transition
                        hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100
                        disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                        <span id="submitLabel">Resize and download</span>
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
    const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    const form = document.getElementById('resizeForm');
    const imageInput = document.getElementById('imageInput');
    const dropArea = document.getElementById('dropArea');
    const previewBox = document.getElementById('previewBox');
    const previewImage = document.getElementById('previewImage');
    const originalInfo = document.getElementById('originalInfo');
    const clientError = document.getElementById('clientError');
    const widthInput = document.getElementById('width');
    const heightInput = document.getElementById('height');
    const keepRatio = document.getElementById('keepRatio');
    const percentInput = document.getElementById('percent');
    const percentRange = document.getElementById('percentRange');
    const pixelFields = document.getElementById('pixelFields');
    const percentFields = document.getElementById('percentFields');
    const summary = document.getElementById('summary');
    const submitButton = document.getElementById('submitButton');
    const submitLabel = document.getElementById('submitLabel');
    const resetButton = document.getElementById('resetButton');
    const modeInputs = document.querySelectorAll('input[name="mode"]');

    let origW = 0, origH = 0, ratio = 1, previewUrl = null;

    const mode = () => document.querySelector('input[name="mode"]:checked').value;

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

        let w, h;

        if (mode() === 'pixels') {
            w = parseInt(widthInput.value, 10);
            h = parseInt(heightInput.value, 10);
        } else {
            const p = parseInt(percentInput.value, 10) || 0;
            w = Math.round(origW * p / 100);
            h = Math.round(origH * p / 100);
        }

        summary.textContent = (w && h)
            ? `${origW} × ${origH} px → ${w} × ${h} px`
            : 'Enter a width and height.';
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

        const probe = new Image();

        probe.onload = function () {
            origW = probe.naturalWidth;
            origH = probe.naturalHeight;
            ratio = origW / origH;

            widthInput.value = origW;
            heightInput.value = origH;

            previewImage.src = previewUrl;
            originalInfo.textContent = `${file.name} · ${origW} × ${origH} px · ${formatSize(file.size)}`;

            dropArea.classList.add('hidden');
            previewBox.classList.remove('hidden');
            submitButton.disabled = false;

            updateSummary();
        };

        probe.src = previewUrl;
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

    widthInput.addEventListener('input', function () {
        if (keepRatio.checked && widthInput.value) {
            heightInput.value = Math.max(1, Math.round(widthInput.value / ratio));
        }
        updateSummary();
    });

    heightInput.addEventListener('input', function () {
        if (keepRatio.checked && heightInput.value) {
            widthInput.value = Math.max(1, Math.round(heightInput.value * ratio));
        }
        updateSummary();
    });

    keepRatio.addEventListener('change', function () {
        if (keepRatio.checked && widthInput.value) {
            heightInput.value = Math.max(1, Math.round(widthInput.value / ratio));
            updateSummary();
        }
    });

    percentRange.addEventListener('input', function () {
        percentInput.value = this.value;
        updateSummary();
    });

    percentInput.addEventListener('input', function () {
        percentRange.value = Math.min(200, this.value || 1);
        updateSummary();
    });

    modeInputs.forEach(input => {
        input.addEventListener('change', function () {
            const isPixels = mode() === 'pixels';
            pixelFields.classList.toggle('hidden', !isPixels);
            percentFields.classList.toggle('hidden', isPixels);
            updateSummary();
        });
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
            '<span>Resizing…</span>';

        // The response is a file download, so the page stays put
        setTimeout(() => {
            submitButton.disabled = false;
            submitLabel.textContent = 'Resize and download';
        }, 3000);
    });

    resetButton.addEventListener('click', function () {
        imageInput.value = '';
        origW = origH = 0;
        previewImage.removeAttribute('src');
        previewBox.classList.add('hidden');
        dropArea.classList.remove('hidden');
        clientError.classList.add('hidden');
        widthInput.value = '';
        heightInput.value = '';
        submitButton.disabled = true;
        updateSummary();
    });
</script>
@endpush
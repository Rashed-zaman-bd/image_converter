@extends('layouts.app')

@section('title', 'Crop Image - Crop unwanted areas from your image')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
@endpush

@section('content')

    <header class="mb-8 sm:mb-10">
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
            Crop unwanted areas from your image
        </h1>
        <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
            Drag the frame, pick a ratio if you need one, and download the cropped image.
        </p>
    </header>

    <form action="{{ route('crop.process') }}"
        method="POST"
        enctype="multipart/form-data"
        id="cropForm"
        class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:gap-6">

        @csrf

        <input type="hidden" name="x" id="cropX">
        <input type="hidden" name="y" id="cropY">
        <input type="hidden" name="width" id="cropWidth">
        <input type="hidden" name="height" id="cropHeight">

        <!-- Left: upload + cropper -->
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

                <div id="cropBox" class="hidden">
                    <div class="h-[55vh] overflow-hidden rounded-2xl bg-slate-100 sm:h-[62vh]">
                        <img id="cropImage" alt="Image to crop" class="block max-w-full">
                    </div>
                    <div class="mt-3 flex justify-end">
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

                <h2 class="font-display text-lg font-semibold">Crop shape</h2>
                <p class="mt-1 text-sm text-slate-500">Lock the frame to a ratio, or leave it free.</p>

                <div id="ratioButtons" class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ([['Free', 'NaN'], ['1:1', '1'], ['4:3', '1.3333333'], ['3:2', '1.5'], ['16:9', '1.7777778'], ['9:16', '0.5625']] as [$label, $value])
                        <button type="button" data-ratio="{{ $value }}"
                            class="ratio-btn rounded-xl border-2 border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition
                            hover:border-slate-300 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <p id="summary" class="mt-5 rounded-xl bg-brand-50 px-3 py-2.5 text-sm font-medium text-brand-700">
                    Add an image to start cropping.
                </p>

                <div class="mt-5 space-y-3">
                    <button type="submit" id="submitButton" disabled
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3.5 font-semibold text-white shadow-sm transition
                        hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100
                        disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                        <span id="submitLabel">Crop and download</span>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    const MAX_SIZE = 50 * 1024 * 1024;
    const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    const form = document.getElementById('cropForm');
    const imageInput = document.getElementById('imageInput');
    const dropArea = document.getElementById('dropArea');
    const cropBox = document.getElementById('cropBox');
    const cropImage = document.getElementById('cropImage');
    const clientError = document.getElementById('clientError');
    const summary = document.getElementById('summary');
    const submitButton = document.getElementById('submitButton');
    const submitLabel = document.getElementById('submitLabel');
    const resetButton = document.getElementById('resetButton');
    const ratioButtons = document.querySelectorAll('.ratio-btn');

    const fields = {
        x: document.getElementById('cropX'),
        y: document.getElementById('cropY'),
        width: document.getElementById('cropWidth'),
        height: document.getElementById('cropHeight'),
    };

    let cropper = null;
    let imageUrl = null;

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.remove('hidden');
    }

    function markRatio(button) {
        ratioButtons.forEach(b => {
            b.classList.remove('border-brand-500', 'bg-brand-50', 'text-brand-700');
            b.classList.add('border-slate-200', 'text-slate-600');
        });
        button.classList.remove('border-slate-200', 'text-slate-600');
        button.classList.add('border-brand-500', 'bg-brand-50', 'text-brand-700');
    }

    function onCrop() {
        // Values are in the original image's pixels, which is what the server needs
        const d = cropper.getData(true);

        fields.x.value = Math.max(0, d.x);
        fields.y.value = Math.max(0, d.y);
        fields.width.value = d.width;
        fields.height.value = d.height;

        summary.textContent = `Crop size: ${d.width} × ${d.height} px`;
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

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (imageUrl) URL.revokeObjectURL(imageUrl);
        imageUrl = URL.createObjectURL(file);

        dropArea.classList.add('hidden');
        cropBox.classList.remove('hidden');

        cropImage.onload = function () {
            cropper = new Cropper(cropImage, {
                viewMode: 1,
                autoCropArea: 0.85,
                background: false,
                responsive: true,
                crop: onCrop,
            });

            markRatio(ratioButtons[0]);
            submitButton.disabled = false;
        };

        cropImage.src = imageUrl;
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

    ratioButtons.forEach(button => {
        button.addEventListener('click', function () {
            if (!cropper) return;
            cropper.setAspectRatio(parseFloat(button.dataset.ratio));
            markRatio(button);
        });
    });

    form.addEventListener('submit', function (e) {

        if (!cropper || !imageInput.files.length) {
            e.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitLabel.innerHTML =
            '<svg class="spinner h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>' +
            '<path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>' +
            '<span>Cropping…</span>';

        // The response is a file download, so the page stays put
        setTimeout(() => {
            submitButton.disabled = false;
            submitLabel.textContent = 'Crop and download';
        }, 3000);
    });

    resetButton.addEventListener('click', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        imageInput.value = '';
        cropImage.removeAttribute('src');
        cropBox.classList.add('hidden');
        dropArea.classList.remove('hidden');
        clientError.classList.add('hidden');
        summary.textContent = 'Add an image to start cropping.';
        submitButton.disabled = true;
    });
</script>
@endpush
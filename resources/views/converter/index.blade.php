<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Image Converter</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                        display: ['"Bricolage Grotesque"', '"DM Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ink: '#141733',
                        brand: {
                            50: '#eef0ff',
                            100: '#e0e4ff',
                            500: '#5b5bf0',
                            600: '#4a4ae0',
                            700: '#3b3bc4',
                        },
                    },
                },
            },
        };
    </script>

    <style>
        body {
            background-color: #f6f7fb;
            background-image: radial-gradient(#d5d9e8 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .drop-area.dragover {
            border-color: #5b5bf0;
            background-color: #eef0ff;
            transform: scale(1.005);
        }

        .drop-area.dragover .drop-icon {
            transform: translateY(-4px);
        }

        .drop-icon,
        .drop-area {
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            animation: spin .8s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .drop-area,
            .drop-icon,
            .spinner {
                transition: none;
                animation: none;
            }
        }
    </style>
</head>

<body class="min-h-screen font-sans text-ink antialiased">

    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:py-14">

        <!-- Header -->
        <header class="mb-8 sm:mb-10 lg:mb-12">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-ink text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="3" />
                        <circle cx="9" cy="10" r="1.6" />
                        <path d="m4 18 5-5 4 4 3-3 4 4" />
                    </svg>
                </span>
                <span class="font-display text-lg font-semibold tracking-tight">Image Converter</span>
            </div>

            <h1 class="mt-6 max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
                Convert images to WebP, AVIF, PNG or JPG
            </h1>
            <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
                Drop in your images, pick a format and download. Files stay private and are never stored.
            </p>
        </header>

        <!-- Steps -->
        <nav aria-label="Progress" class="mb-6 sm:mb-8">
            <ol id="steps" class="grid grid-cols-4 gap-2 sm:gap-4">
                @foreach (['Upload', 'Select format', 'Convert', 'Download'] as $step)
                    <li data-step="{{ $loop->index }}" class="flex flex-col items-center gap-2 sm:flex-row sm:gap-3">
                        <span data-dot
                            class="grid h-8 w-8 shrink-0 place-items-center rounded-full border-2 border-slate-200 bg-white text-sm font-semibold text-slate-400 transition">
                            {{ $loop->iteration }}
                        </span>
                        <span data-label class="text-center text-xs font-medium text-slate-400 transition sm:text-left sm:text-sm">
                            {{ $step }}
                        </span>
                        @if (!$loop->last)
                            <span class="hidden h-px flex-1 bg-slate-200 sm:block" aria-hidden="true"></span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <!-- Converter -->
        <form action="{{ route('converter.convert') }}"
            method="POST"
            enctype="multipart/form-data"
            id="converterForm"
            class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:gap-6">

            @csrf

            <!-- Left: upload + previews -->
            <section class="lg:col-span-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6">

                    @if ($errors->any())
                        <div role="alert" class="mb-4 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5M12 16h.01" />
                            </svg>
                            <div class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div id="clientError" role="alert" aria-live="polite"
                        class="mb-4 hidden gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    </div>

                    <input
                        type="file"
                        name="images[]"
                        id="imageInput"
                        accept="image/jpeg,image/png,image/webp,image/avif"
                        multiple
                        class="sr-only"
                    />

                    <label
                        for="imageInput"
                        id="dropArea"
                        class="drop-area flex min-h-[240px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center hover:border-brand-500 hover:bg-brand-50 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100 sm:min-h-[280px]">

                        <span class="drop-icon mb-5 grid h-16 w-16 place-items-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/30">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 16V4M7 9l5-5 5 5" />
                                <path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
                            </svg>
                        </span>

                        <span class="font-display text-xl font-semibold">
                            Drop images here, or click to browse
                        </span>

                        <span class="mt-2 text-sm text-slate-500">
                            JPG, PNG, WebP or AVIF. Up to 50MB in total.
                        </span>
                    </label>

                    <!-- Image preview -->
                    <div id="previewSection" class="mt-6 hidden">
                        <div class="mb-3 flex items-end justify-between gap-3">
                            <h2 class="font-display text-lg font-semibold">Selected images</h2>
                            <p id="fileCount" class="text-sm text-slate-500"></p>
                        </div>

                        <div class="mb-4 h-1.5 overflow-hidden rounded-full bg-slate-100"
                            role="progressbar" aria-label="Upload size used" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="sizeBarWrap">
                            <div id="sizeBar" class="h-full w-0 rounded-full bg-brand-500 transition-all"></div>
                        </div>

                        <ul id="previewContainer"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Right: format + actions -->
            <aside class="lg:col-span-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6 lg:sticky lg:top-6">

                    <fieldset>
                        <legend class="font-display text-lg font-semibold">Output format</legend>
                        <p class="mt-1 text-sm text-slate-500">Choose what your images become.</p>

                        <div class="mt-4 grid grid-cols-2 gap-3">

                            @foreach ([
                                ['value' => 'webp', 'name' => 'WebP', 'note' => 'Small files for websites', 'badge' => 'Recommended'],
                                ['value' => 'avif', 'name' => 'AVIF', 'note' => 'Smallest files, newest format', 'badge' => null],
                                ['value' => 'png',  'name' => 'PNG',  'note' => 'Lossless, keeps transparency', 'badge' => null],
                                ['value' => 'jpg',  'name' => 'JPG',  'note' => 'Opens everywhere', 'badge' => null],
                            ] as $format)

                                <label class="relative block cursor-pointer">

                                    <input type="radio" name="format"
                                        value="{{ $format['value'] }}"
                                        @checked($format['value'] === 'webp')
                                        class="peer sr-only">

                                    <span class="flex h-full flex-col rounded-2xl border-2 border-slate-200 bg-white p-3 transition
                                        hover:border-slate-300
                                        peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:shadow-sm
                                        peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100">

                                        <span class="flex items-center justify-between gap-2">
                                            <span class="font-display text-base font-bold">{{ $format['name'] }}</span>
                                            <span class="format-check grid h-5 w-5 place-items-center rounded-full border-2 border-slate-300 text-transparent transition">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3"
                                                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="m5 12 5 5 9-10" />
                                                </svg>
                                            </span>
                                        </span>

                                        <span class="mt-1 text-xs leading-snug text-slate-500">{{ $format['note'] }}</span>

                                        @if ($format['badge'])
                                            <span class="mt-2 inline-block w-fit rounded-full bg-brand-100 px-2 py-0.5 text-[11px] font-semibold text-brand-700">
                                                {{ $format['badge'] }}
                                            </span>
                                        @endif
                                    </span>
                                </label>

                            @endforeach

                        </div>
                    </fieldset>

                    <!-- Actions -->
                    <div class="mt-6 space-y-3">

                        <button
                            type="submit"
                            id="convertButton"
                            disabled
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3.5 font-semibold text-white shadow-sm transition
                            hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100
                            disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                            <span id="convertLabel">Convert and download</span>
                        </button>

                        <button
                            type="button"
                            id="resetButton"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-600 transition
                            hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                            Start over
                        </button>
                    </div>
                </div>
            </aside>

        </form>

        <!-- Footer -->
        <footer class="mt-10 text-center text-sm text-slate-500">
            Free online image converter for JPG, PNG, WebP and AVIF.
        </footer>

    </main>

    <script>
        const MAX_SIZE = 50 * 1024 * 1024;
        const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

        const form = document.getElementById('converterForm');
        const imageInput = document.getElementById('imageInput');
        const dropArea = document.getElementById('dropArea');
        const previewSection = document.getElementById('previewSection');
        const previewContainer = document.getElementById('previewContainer');
        const fileCount = document.getElementById('fileCount');
        const sizeBar = document.getElementById('sizeBar');
        const sizeBarWrap = document.getElementById('sizeBarWrap');
        const convertButton = document.getElementById('convertButton');
        const convertLabel = document.getElementById('convertLabel');
        const resetButton = document.getElementById('resetButton');
        const clientError = document.getElementById('clientError');
        const formatInputs = document.querySelectorAll('input[name="format"]');
        const stepItems = document.querySelectorAll('#steps [data-step]');

        let selectedFiles = [];
        let previewUrls = [];
        let resetTimer = null;

        /* ---------- Helpers ---------- */

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function totalSize() {
            return selectedFiles.reduce((sum, file) => sum + file.size, 0);
        }

        function currentFormat() {
            const checked = document.querySelector('input[name="format"]:checked');
            return checked ? checked.value : 'webp';
        }

        function showError(message) {
            clientError.textContent = message;
            clientError.classList.remove('hidden');
            clientError.classList.add('flex');
        }

        function clearError() {
            clientError.textContent = '';
            clientError.classList.add('hidden');
            clientError.classList.remove('flex');
        }

        /* ---------- Steps ---------- */

        // current = index of the active step; steps before it are done
        function setStep(current, allDone = false) {
            stepItems.forEach((item, index) => {
                const dot = item.querySelector('[data-dot]');
                const label = item.querySelector('[data-label]');
                const done = allDone || index < current;
                const active = !allDone && index === current;

                dot.className = 'grid h-8 w-8 shrink-0 place-items-center rounded-full border-2 text-sm font-semibold transition ' +
                    (done
                        ? 'border-brand-500 bg-brand-500 text-white'
                        : active
                            ? 'border-brand-500 bg-white text-brand-600 ring-4 ring-brand-100'
                            : 'border-slate-200 bg-white text-slate-400');

                label.className = 'text-center text-xs font-medium transition sm:text-left sm:text-sm ' +
                    (done || active ? 'text-ink' : 'text-slate-400');

                dot.textContent = done ? '✓' : String(index + 1);

                if (active) {
                    item.setAttribute('aria-current', 'step');
                } else {
                    item.removeAttribute('aria-current');
                }
            });
        }

        /* ---------- Rendering ---------- */

        function updateConvertLabel() {
            const format = currentFormat().toUpperCase();
            const count = selectedFiles.length;

            convertLabel.textContent = count
                ? `Convert ${count} ${count === 1 ? 'image' : 'images'} to ${format}`
                : 'Convert and download';
        }

        function syncInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            imageInput.files = dataTransfer.files;
        }

        function updatePreviews() {

            previewUrls.forEach(url => URL.revokeObjectURL(url));
            previewUrls = [];
            previewContainer.innerHTML = '';

            selectedFiles.forEach((file, index) => {

                const url = URL.createObjectURL(file);
                previewUrls.push(url);

                const item = document.createElement('li');
                item.className = 'group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50';

                const image = document.createElement('img');
                image.src = url;
                image.alt = file.name;
                image.className = 'h-28 w-full bg-white object-contain sm:h-32';

                const meta = document.createElement('div');
                meta.className = 'border-t border-slate-200 bg-white px-2.5 py-2';

                const name = document.createElement('p');
                name.textContent = file.name;
                name.title = file.name;
                name.className = 'truncate text-xs font-medium text-ink';

                const size = document.createElement('p');
                size.textContent = formatSize(file.size);
                size.className = 'text-xs text-slate-500';

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove ' + file.name);
                remove.className = 'absolute right-1.5 top-1.5 grid h-7 w-7 place-items-center rounded-full bg-ink/80 text-white ' +
                    'transition hover:bg-red-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100';
                remove.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" ' +
                    'stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
                remove.addEventListener('click', () => removeFile(index));

                meta.appendChild(name);
                meta.appendChild(size);
                item.appendChild(image);
                item.appendChild(meta);
                item.appendChild(remove);

                previewContainer.appendChild(item);
            });

            const hasFiles = selectedFiles.length > 0;
            const percent = Math.min(100, (totalSize() / MAX_SIZE) * 100);

            previewSection.classList.toggle('hidden', !hasFiles);

            fileCount.textContent = hasFiles
                ? `${selectedFiles.length} ${selectedFiles.length === 1 ? 'image' : 'images'} · ${formatSize(totalSize())} of 50 MB`
                : '';

            sizeBar.style.width = percent + '%';
            sizeBarWrap.setAttribute('aria-valuenow', Math.round(percent));

            convertButton.disabled = !hasFiles;

            updateConvertLabel();
            setStep(hasFiles ? 1 : 0);
        }

        /* ---------- File handling ---------- */

        function addFiles(files) {

            clearError();

            const incoming = Array.from(files);
            const accepted = [];
            let rejected = 0;

            incoming.forEach(file => {

                const validType = VALID_TYPES.includes(file.type);
                const validSize = file.size <= MAX_SIZE;

                const duplicate = selectedFiles.some(existing =>
                    existing.name === file.name &&
                    existing.size === file.size &&
                    existing.lastModified === file.lastModified
                );

                if (!validType || !validSize) {
                    rejected++;
                    return;
                }

                if (!duplicate) {
                    accepted.push(file);
                }
            });

            const next = selectedFiles.concat(accepted);
            const nextTotal = next.reduce((sum, file) => sum + file.size, 0);

            if (nextTotal > MAX_SIZE) {
                showError('The total size of your images cannot exceed 50 MB. Remove some images and try again.');
                syncInput();
                return;
            }

            selectedFiles = next;
            syncInput();
            updatePreviews();

            if (rejected > 0) {
                showError(`${rejected} ${rejected === 1 ? 'file was' : 'files were'} skipped. Only JPG, PNG, WebP and AVIF images are allowed.`);
            }
        }

        function removeFile(index) {
            clearError();
            selectedFiles.splice(index, 1);
            syncInput();
            updatePreviews();
        }

        /* ---------- Events ---------- */

        imageInput.addEventListener('change', function () {
            addFiles(this.files);
        });

        ['dragenter', 'dragover'].forEach(type => {
            dropArea.addEventListener(type, function (e) {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });
        });

        dropArea.addEventListener('dragleave', function () {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', function (e) {
            e.preventDefault();
            dropArea.classList.remove('dragover');
            addFiles(e.dataTransfer.files);
        });

        formatInputs.forEach(input => {

            input.addEventListener('change', function () {

                // Selected format tick
                document.querySelectorAll('.format-check').forEach(check => {
                    check.classList.remove('border-brand-500', 'bg-brand-500', 'text-white');
                    check.classList.add('border-slate-300', 'text-transparent');
                });

                const check = this.parentElement.querySelector('.format-check');
                check.classList.remove('border-slate-300', 'text-transparent');
                check.classList.add('border-brand-500', 'bg-brand-500', 'text-white');

                updateConvertLabel();
            });
        });

        form.addEventListener('submit', function (e) {

            if (selectedFiles.length === 0) {
                e.preventDefault();
                return;
            }

            // The response is a file download, so the page stays put.
            // Show progress briefly, then return to a ready state.
            convertButton.disabled = true;
            convertLabel.innerHTML =
                '<svg class="spinner h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">' +
                '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/>' +
                '<path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>' +
                '<span>Converting…</span>';

            setStep(2);

            clearTimeout(resetTimer);
            resetTimer = setTimeout(() => {
                convertButton.disabled = selectedFiles.length === 0;
                updateConvertLabel();
                setStep(3, true);
            }, 4000);
        });

        // Restore the button if the browser serves the page from its back/forward cache
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                clearTimeout(resetTimer);
                convertButton.disabled = selectedFiles.length === 0;
                updateConvertLabel();
                setStep(selectedFiles.length ? 1 : 0);
            }
        });

        resetButton.addEventListener('click', function () {

            clearTimeout(resetTimer);
            clearError();

            selectedFiles = [];
            imageInput.value = '';

            formatInputs.forEach(input => {
                input.checked = input.value === 'webp';
                if (input.checked) {
                    input.dispatchEvent(new Event('change'));
                }
            });

            updatePreviews();
        });

        // Initial state
        document.querySelector('input[name="format"]:checked')
            .dispatchEvent(new Event('change'));
        updatePreviews();

    </script>

</body>
</html>
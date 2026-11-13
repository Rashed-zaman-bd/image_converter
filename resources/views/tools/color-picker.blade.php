@extends('layouts.app')

@section('title', 'Color Picker - Pick a color from your image')

@section('content')

    <header class="mb-8 sm:mb-10">
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
            Pick a color from your image
        </h1>
        <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
            Click anywhere on your image to get its HEX, RGB and HSL values. Your image never leaves your browser.
        </p>
    </header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:gap-6">

        <!-- Left: image -->
        <section class="lg:col-span-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6">

                <div id="clientError" role="alert" aria-live="polite"
                    class="mb-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"></div>

                @include('partials.dropzone', [
                    'name' => 'image',
                    'accept' => 'image/jpeg,image/png,image/webp,image/avif',
                    'hint' => 'JPG, PNG, WebP or AVIF. Up to 50MB.',
                ])

                <div id="canvasBox" class="hidden">
                    <div id="canvasWrap" class="relative flex items-center justify-center rounded-2xl bg-slate-100 p-3">
                        <canvas id="pickCanvas" class="max-h-[460px] w-auto max-w-full touch-none cursor-crosshair rounded-lg"></canvas>

                        <div id="loupe" class="pointer-events-none absolute z-10 hidden" style="width:112px;height:112px;">
                            <canvas id="loupeCanvas" width="110" height="110"
                                class="h-full w-full rounded-full border-4 border-white shadow-lg ring-1 ring-slate-300"></canvas>
                            <span id="loupeHex"
                                class="absolute left-1/2 top-full mt-1 -translate-x-1/2 rounded-md bg-ink px-2 py-0.5 text-xs font-semibold text-white"></span>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm text-slate-500">Click or drag on the image to pick a color.</p>
                        <label for="imageInput"
                            class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Change image
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right: color values -->
        <aside class="lg:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6 lg:sticky lg:top-24">

                <h2 class="font-display text-lg font-semibold">Picked color</h2>

                <div id="swatch" class="mt-3 h-28 rounded-2xl border border-slate-200" style="background:#5b5bf0"></div>

                <dl class="mt-4 space-y-2.5">
                    @foreach (['hex' => 'HEX', 'rgb' => 'RGB', 'hsl' => 'HSL'] as $key => $label)
                        <div class="flex items-center gap-3">
                            <dt class="w-10 text-sm font-semibold text-slate-500">{{ $label }}</dt>
                            <dd class="min-w-0 flex-1">
                                <output id="{{ $key }}Value"
                                    class="block truncate rounded-lg bg-slate-50 px-3 py-2 font-mono text-sm text-ink"></output>
                            </dd>
                            <button type="button" data-copy="{{ $key }}"
                                class="w-[4.5rem] rounded-lg border border-slate-200 px-2 py-2 text-sm font-semibold text-slate-600 transition
                                hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                                Copy
                            </button>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <label for="wheel" class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600">
                        <input type="color" id="wheel" value="#5b5bf0"
                            class="h-10 w-12 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                        Color wheel
                    </label>

                    <button type="button" id="eyeButton"
                        class="hidden rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition
                        hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                        Pick from screen
                    </button>
                </div>

                <div id="historyBox" class="mt-6 hidden">
                    <h3 class="font-display text-base font-semibold">Recent colors</h3>
                    <div id="history" class="mt-2 flex flex-wrap gap-2"></div>
                </div>

                <p id="copyStatus" class="sr-only" aria-live="polite"></p>
            </div>
        </aside>

    </div>

@endsection

@push('scripts')
<script>
    const MAX_SIZE = 50 * 1024 * 1024;
    const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
    const MAX_CANVAS = 4096;

    const imageInput = document.getElementById('imageInput');
    const dropArea = document.getElementById('dropArea');
    const canvasBox = document.getElementById('canvasBox');
    const canvasWrap = document.getElementById('canvasWrap');
    const canvas = document.getElementById('pickCanvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const loupe = document.getElementById('loupe');
    const loupeCanvas = document.getElementById('loupeCanvas');
    const loupeCtx = loupeCanvas.getContext('2d');
    const loupeHex = document.getElementById('loupeHex');
    const clientError = document.getElementById('clientError');
    const swatch = document.getElementById('swatch');
    const wheel = document.getElementById('wheel');
    const eyeButton = document.getElementById('eyeButton');
    const historyBox = document.getElementById('historyBox');
    const historyList = document.getElementById('history');
    const copyStatus = document.getElementById('copyStatus');
    const outputs = {
        hex: document.getElementById('hexValue'),
        rgb: document.getElementById('rgbValue'),
        hsl: document.getElementById('hslValue'),
    };

    let imageUrl = null;
    let dragging = false;
    let history = [];
    let current = { r: 91, g: 91, b: 240 };

    /* ---------- Color helpers ---------- */

    const hex2 = n => n.toString(16).padStart(2, '0');
    const toHex = c => '#' + hex2(c.r) + hex2(c.g) + hex2(c.b);

    function hexToRgb(hex) {
        const value = parseInt(hex.slice(1), 16);
        return { r: (value >> 16) & 255, g: (value >> 8) & 255, b: value & 255 };
    }

    function toHsl(c) {
        const r = c.r / 255, g = c.g / 255, b = c.b / 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b);
        const l = (max + min) / 2;
        let h = 0, s = 0;

        if (max !== min) {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

            if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
            else if (max === g) h = (b - r) / d + 2;
            else h = (r - g) / d + 4;

            h *= 60;
        }

        return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) };
    }

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.remove('hidden');
    }

    /* ---------- Rendering ---------- */

    function renderColor() {
        const hsl = toHsl(current);

        swatch.style.background = toHex(current);
        wheel.value = toHex(current);

        outputs.hex.textContent = toHex(current).toUpperCase();
        outputs.rgb.textContent = `rgb(${current.r}, ${current.g}, ${current.b})`;
        outputs.hsl.textContent = `hsl(${hsl.h}, ${hsl.s}%, ${hsl.l}%)`;
    }

    function renderHistory() {
        historyList.innerHTML = '';
        historyBox.classList.toggle('hidden', history.length === 0);

        history.forEach(hex => {
            const button = document.createElement('button');
            button.type = 'button';
            button.title = hex.toUpperCase();
            button.setAttribute('aria-label', 'Use ' + hex.toUpperCase());
            button.className = 'h-9 w-9 rounded-lg border border-slate-200 transition hover:scale-105 ' +
                'focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100';
            button.style.background = hex;
            button.addEventListener('click', () => setColor(hexToRgb(hex), false));
            historyList.appendChild(button);
        });
    }

    function setColor(rgb, addToHistory) {
        current = rgb;
        renderColor();

        if (addToHistory) {
            const hex = toHex(rgb);
            history = [hex].concat(history.filter(item => item !== hex)).slice(0, 10);
            renderHistory();
        }
    }

    /* ---------- Canvas picking ---------- */

    function pixelAt(e) {
        const rect = canvas.getBoundingClientRect();
        const x = Math.floor((e.clientX - rect.left) * canvas.width / rect.width);
        const y = Math.floor((e.clientY - rect.top) * canvas.height / rect.height);

        return {
            x: Math.min(canvas.width - 1, Math.max(0, x)),
            y: Math.min(canvas.height - 1, Math.max(0, y)),
        };
    }

    function colorAt(x, y) {
        const d = ctx.getImageData(x, y, 1, 1).data;
        return { r: d[0], g: d[1], b: d[2] };
    }

    function updateLoupe(e, point) {
        const wrapRect = canvasWrap.getBoundingClientRect();
        const size = 112;

        let left = e.clientX - wrapRect.left - size / 2;
        let top = e.clientY - wrapRect.top - size - 24;

        if (top < 0) top = e.clientY - wrapRect.top + 24;

        left = Math.min(wrapRect.width - size, Math.max(0, left));

        loupe.style.left = left + 'px';
        loupe.style.top = top + 'px';
        loupe.classList.remove('hidden');

        // 11 x 11 pixel area, zoomed 10 times
        loupeCtx.imageSmoothingEnabled = false;
        loupeCtx.clearRect(0, 0, 110, 110);
        loupeCtx.drawImage(canvas, point.x - 5, point.y - 5, 11, 11, 0, 0, 110, 110);
        loupeCtx.lineWidth = 2;
        loupeCtx.strokeStyle = '#ffffff';
        loupeCtx.strokeRect(50, 50, 10, 10);
        loupeCtx.lineWidth = 1;
        loupeCtx.strokeStyle = '#141733';
        loupeCtx.strokeRect(49, 49, 12, 12);

        loupeHex.textContent = toHex(colorAt(point.x, point.y)).toUpperCase();
    }

    canvas.addEventListener('pointerdown', function (e) {
        dragging = true;
        canvas.setPointerCapture(e.pointerId);
        updateLoupe(e, pixelAt(e));
    });

    canvas.addEventListener('pointermove', function (e) {
        updateLoupe(e, pixelAt(e));
    });

    canvas.addEventListener('pointerup', function (e) {
        if (!dragging) return;
        dragging = false;

        const point = pixelAt(e);
        setColor(colorAt(point.x, point.y), true);

        if (e.pointerType !== 'mouse') loupe.classList.add('hidden');
    });

    canvas.addEventListener('pointerleave', function () {
        if (!dragging) loupe.classList.add('hidden');
    });

    /* ---------- Loading an image ---------- */

    function loadFile(file) {

        clientError.classList.add('hidden');

        if (!file) return;

        if (!VALID_TYPES.includes(file.type)) {
            showError('Only JPG, PNG, WebP and AVIF images are allowed.');
            return;
        }

        if (file.size > MAX_SIZE) {
            showError('The image is larger than 50 MB.');
            return;
        }

        if (imageUrl) URL.revokeObjectURL(imageUrl);
        imageUrl = URL.createObjectURL(file);

        const img = new Image();

        img.onload = function () {
            // Very large images are scaled down so the browser can handle them
            const ratio = Math.min(1, MAX_CANVAS / Math.max(img.naturalWidth, img.naturalHeight));

            canvas.width = Math.round(img.naturalWidth * ratio);
            canvas.height = Math.round(img.naturalHeight * ratio);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            dropArea.classList.add('hidden');
            canvasBox.classList.remove('hidden');
        };

        img.onerror = () => showError('This image could not be opened.');
        img.src = imageUrl;
    }

    imageInput.addEventListener('change', function () {
        loadFile(this.files[0]);
        this.value = '';
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
        loadFile(e.dataTransfer.files[0]);
    });

    /* ---------- Wheel, screen picker, copy ---------- */

    wheel.addEventListener('input', function () {
        setColor(hexToRgb(this.value), false);
    });

    wheel.addEventListener('change', function () {
        setColor(hexToRgb(this.value), true);
    });

    if ('EyeDropper' in window) {
        eyeButton.classList.remove('hidden');

        eyeButton.addEventListener('click', async function () {
            try {
                const result = await new EyeDropper().open();
                setColor(hexToRgb(result.sRGBHex), true);
            } catch (error) {
                // The person cancelled the picker
            }
        });
    }

    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', async function () {
            const text = outputs[button.dataset.copy].textContent;

            try {
                await navigator.clipboard.writeText(text);
            } catch (error) {
                const helper = document.createElement('textarea');
                helper.value = text;
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                helper.remove();
            }

            button.textContent = 'Copied';
            copyStatus.textContent = text + ' copied';
            setTimeout(() => { button.textContent = 'Copy'; }, 1500);
        });
    });

    renderColor();
</script>
@endpush
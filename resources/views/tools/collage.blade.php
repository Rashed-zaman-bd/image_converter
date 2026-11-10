@extends('layouts.tool')

@section('title', 'Collage Maker - Create a photo collage')

@section('content')

    <header class="mb-8 sm:mb-10">
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
            Create a beautiful photo collage
        </h1>
        <p class="mt-3 max-w-xl text-base text-slate-600 sm:text-lg">
            Add 2 to 12 photos, choose a layout and download the finished collage. Your photos never leave your browser.
        </p>
    </header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:gap-6">

        <!-- Left: photos + preview -->
        <section class="lg:col-span-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6">

                <div id="clientError" role="alert" aria-live="polite"
                    class="mb-4 hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"></div>

                @include('partials.dropzone', [
                    'name' => 'images',
                    'multiple' => true,
                    'accept' => 'image/jpeg,image/png,image/webp,image/avif',
                    'title' => 'Drop your photos here, or click to browse',
                    'hint' => 'Add 2 to 12 photos. JPG, PNG, WebP or AVIF.',
                ])

                <div id="editor" class="hidden">
                    <div class="rounded-2xl bg-slate-100 p-3">
                        <canvas id="collageCanvas" class="mx-auto block h-auto max-h-[520px] w-auto max-w-full rounded-lg"></canvas>
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-3">
                        <h2 class="font-display text-lg font-semibold">Your photos</h2>
                        <p id="photoCount" class="text-sm text-slate-500"></p>
                    </div>

                    <ul id="thumbs" class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6"></ul>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <label for="imageInput" id="addMore"
                            class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Add more photos
                        </label>
                        <button type="button" id="shuffleButton"
                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                            Shuffle order
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right: options -->
        <aside class="lg:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-6 lg:sticky lg:top-24">

                <fieldset>
                    <legend class="font-display text-lg font-semibold">Layout</legend>
                    <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1">
                        @foreach (['grid' => 'Grid', 'row' => 'Row', 'column' => 'Column'] as $value => $label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="layout" value="{{ $value }}" @checked($value === 'grid') class="peer sr-only">
                                <span class="block rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-500 transition
                                    peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm
                                    peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-5">
                    <label for="ratio" class="mb-1.5 block text-sm font-medium text-slate-600">Shape</label>
                    <select id="ratio"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-100">
                        <option value="1:1">Square (1:1)</option>
                        <option value="4:3">Landscape (4:3)</option>
                        <option value="16:9">Wide (16:9)</option>
                        <option value="3:4">Portrait (3:4)</option>
                        <option value="9:16">Tall (9:16)</option>
                    </select>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div>
                        <label for="gap" class="mb-1.5 block text-sm font-medium text-slate-600">Spacing</label>
                        <input type="range" id="gap" min="0" max="40" value="10" class="w-full accent-brand-500">
                    </div>
                    <div>
                        <label for="radius" class="mb-1.5 block text-sm font-medium text-slate-600">Rounded corners</label>
                        <input type="range" id="radius" min="0" max="60" value="12" class="w-full accent-brand-500">
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <label for="bg" class="text-sm font-medium text-slate-600">Background color</label>
                    <input type="color" id="bg" value="#ffffff"
                        class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                </div>

                <fieldset class="mt-5">
                    <legend class="text-sm font-medium text-slate-600">Download as</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
                        @foreach (['png' => 'PNG', 'jpg' => 'JPG'] as $value => $label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="outFormat" value="{{ $value }}" @checked($value === 'png') class="peer sr-only">
                                <span class="block rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-500 transition
                                    peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-sm
                                    peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-6 space-y-3">
                    <button type="button" id="downloadButton" disabled
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3.5 font-semibold text-white shadow-sm transition
                        hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100
                        disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                        <span id="downloadLabel">Add at least 2 photos</span>
                    </button>

                    <button type="button" id="resetButton"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-600 transition
                        hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                        Start over
                    </button>
                </div>
            </div>
        </aside>

    </div>

@endsection

@push('scripts')
<script>
    const MAX_PHOTOS = 12;
    const MAX_FILE = 50 * 1024 * 1024;
    const LONG_SIDE = 2000;
    const VALID_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    const imageInput = document.getElementById('imageInput');
    const dropArea = document.getElementById('dropArea');
    const editor = document.getElementById('editor');
    const canvas = document.getElementById('collageCanvas');
    const ctx = canvas.getContext('2d');
    const thumbs = document.getElementById('thumbs');
    const photoCount = document.getElementById('photoCount');
    const clientError = document.getElementById('clientError');
    const ratioSelect = document.getElementById('ratio');
    const gapInput = document.getElementById('gap');
    const radiusInput = document.getElementById('radius');
    const bgInput = document.getElementById('bg');
    const shuffleButton = document.getElementById('shuffleButton');
    const downloadButton = document.getElementById('downloadButton');
    const downloadLabel = document.getElementById('downloadLabel');
    const resetButton = document.getElementById('resetButton');

    let items = [];
    let uid = 0;
    let frame = null;

    const layout = () => document.querySelector('input[name="layout"]:checked').value;
    const outFormat = () => document.querySelector('input[name="outFormat"]:checked').value;

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.remove('hidden');
    }

    /* ---------- Layout maths ---------- */

    function rowCounts(n, W, H) {
        if (layout() === 'row') return [n];
        if (layout() === 'column') return Array(n).fill(1);

        const cols = Math.max(1, Math.ceil(Math.sqrt(n * W / H)));
        const counts = [];
        let left = n;

        while (left > 0) {
            counts.push(Math.min(cols, left));
            left -= cols;
        }

        return counts;
    }

    function roundedRect(x, y, w, h, r) {
        r = Math.min(r, w / 2, h / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
    }

    function draw() {
        const [rw, rh] = ratioSelect.value.split(':').map(Number);
        const W = rw >= rh ? LONG_SIDE : Math.round(LONG_SIDE * rw / rh);
        const H = rw >= rh ? Math.round(LONG_SIDE * rh / rw) : LONG_SIDE;

        canvas.width = W;
        canvas.height = H;

        const gap = parseInt(gapInput.value, 10) * W / 1000;
        const radius = parseInt(radiusInput.value, 10) * W / 1000;

        ctx.fillStyle = bgInput.value;
        ctx.fillRect(0, 0, W, H);

        if (!items.length) return;

        const counts = rowCounts(items.length, W, H);
        const rows = counts.length;
        const cellH = (H - gap * (rows + 1)) / rows;
        let index = 0;

        counts.forEach((count, r) => {
            const cellW = (W - gap * (count + 1)) / count;

            for (let i = 0; i < count; i++) {
                const img = items[index++].img;
                const x = gap + i * (cellW + gap);
                const y = gap + r * (cellH + gap);

                // Fill the cell and crop the overflow from the centre
                const scale = Math.max(cellW / img.naturalWidth, cellH / img.naturalHeight);
                const sw = cellW / scale;
                const sh = cellH / scale;

                ctx.save();
                roundedRect(x, y, cellW, cellH, radius);
                ctx.clip();
                ctx.drawImage(img, (img.naturalWidth - sw) / 2, (img.naturalHeight - sh) / 2, sw, sh, x, y, cellW, cellH);
                ctx.restore();
            }
        });
    }

    function scheduleDraw() {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(draw);
    }

    /* ---------- Photos ---------- */

    function renderThumbs() {
        thumbs.innerHTML = '';

        items.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = 'relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50';

            const img = document.createElement('img');
            img.src = item.url;
            img.alt = item.name;
            img.className = 'aspect-square w-full object-cover';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', 'Remove ' + item.name);
            remove.className = 'absolute right-1 top-1 grid h-6 w-6 place-items-center rounded-full bg-ink/80 text-white transition ' +
                'hover:bg-red-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100';
            remove.innerHTML = '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" ' +
                'stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
            remove.addEventListener('click', () => removeItem(index));

            li.appendChild(img);
            li.appendChild(remove);
            thumbs.appendChild(li);
        });
    }

    function refresh() {
        const has = items.length > 0;

        dropArea.classList.toggle('hidden', has);
        editor.classList.toggle('hidden', !has);
        photoCount.textContent = has ? `${items.length} of ${MAX_PHOTOS} photos` : '';
        shuffleButton.disabled = items.length < 2;

        downloadButton.disabled = items.length < 2;
        downloadLabel.textContent = items.length < 2 ? 'Add at least 2 photos' : 'Download collage';

        renderThumbs();
        scheduleDraw();
    }

    function removeItem(index) {
        URL.revokeObjectURL(items[index].url);
        items.splice(index, 1);
        refresh();
    }

    function loadOne(file) {
        return new Promise(resolve => {
            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => resolve({ id: ++uid, name: file.name, url, img });
            img.onerror = () => { URL.revokeObjectURL(url); resolve(null); };
            img.src = url;
        });
    }

    async function addFiles(fileList) {

        clientError.classList.add('hidden');

        const all = Array.from(fileList);
        const valid = all.filter(f => VALID_TYPES.includes(f.type) && f.size <= MAX_FILE);
        const room = MAX_PHOTOS - items.length;
        const chosen = valid.slice(0, Math.max(0, room));

        if (valid.length < all.length) {
            showError('Some files were skipped. Only JPG, PNG, WebP and AVIF images up to 50 MB are allowed.');
        } else if (valid.length > room) {
            showError(`You can add up to ${MAX_PHOTOS} photos. The extra photos were skipped.`);
        }

        const loaded = (await Promise.all(chosen.map(loadOne))).filter(Boolean);

        items = items.concat(loaded);
        refresh();
    }

    imageInput.addEventListener('change', function () {
        addFiles(this.files);
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
        addFiles(e.dataTransfer.files);
    });

    /* ---------- Options ---------- */

    [ratioSelect, gapInput, radiusInput, bgInput].forEach(el => {
        el.addEventListener('input', scheduleDraw);
    });

    document.querySelectorAll('input[name="layout"]').forEach(input => {
        input.addEventListener('change', scheduleDraw);
    });

    shuffleButton.addEventListener('click', function () {
        for (let i = items.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [items[i], items[j]] = [items[j], items[i]];
        }
        refresh();
    });

    /* ---------- Download ---------- */

    downloadButton.addEventListener('click', function () {
        draw();

        const jpg = outFormat() === 'jpg';

        canvas.toBlob(function (blob) {
            if (!blob) {
                showError('The collage could not be created. Try fewer or smaller photos.');
                return;
            }

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = 'collage.' + (jpg ? 'jpg' : 'png');
            document.body.appendChild(link);
            link.click();
            link.remove();

            setTimeout(() => URL.revokeObjectURL(url), 1000);
        }, jpg ? 'image/jpeg' : 'image/png', 0.92);
    });

    resetButton.addEventListener('click', function () {
        items.forEach(item => URL.revokeObjectURL(item.url));
        items = [];
        clientError.classList.add('hidden');
        refresh();
    });
</script>
@endpush
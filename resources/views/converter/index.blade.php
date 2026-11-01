
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Image Converter</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .format-card:has(input:checked) {
            border-color: #16a34a;
            background-color: #f0fdf4;
        }

        .drop-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>
</head>

<body class="min-h-screen bg-[#f1f6fd]">

    <main class="max-w-5xl mx-auto px-4 py-8 md:py-12">

        <!-- Header -->
        <header class="mb-7">
            <h1 class="text-2xl md:text-3xl font-bold text-blue-500 flex items-center gap-3">
                <span class="bg-blue-500 text-white p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </span>
                Online Image Converter
            </h1>
        </header>

        <!-- Steps -->
        <section class="mb-8">
            <div class="grid grid-cols-4 gap-2">

                @foreach ([
                    ['icon' => '↑', 'name' => 'Upload'],
                    ['icon' => '▣', 'name' => 'Select Format'],
                    ['icon' => '⟳', 'name' => 'Convert'],
                    ['icon' => '⇩', 'name' => 'Download']
                ] as $step)

                    <div class="relative flex items-center justify-center">

                        <div class="flex items-center justify-center gap-2
                            bg-white shadow-sm rounded-full w-full py-3 px-1
                            text-gray-600 font-semibold text-xs sm:text-sm">

                            <span class="text-blue-500 text-lg">
                                {{ $step['icon'] }}
                            </span>

                            {{ $step['name'] }}

                        </div>

                    </div>

                @endforeach

            </div>
        </section>

        <!-- Main Converter -->
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-8">

            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 p-4 text-red-600">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('converter.convert') }}"
                method="POST"
                enctype="multipart/form-data"
                id="converterForm">

                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">

                    <!-- Upload Area -->
                    <div>

                        <input
                            type="file"
                            name="images[]"
                            id="imageInput"
                            accept="image/jpeg,image/png,image/webp,image/avif"
                            multiple
                            class="hidden"
                        />

                        <label
                            for="imageInput"
                            id="dropArea"
                            class="drop-area flex flex-col items-center justify-center
                            min-h-[230px] border-2 border-dashed border-gray-300
                            rounded-xl bg-gray-50 p-6 text-center cursor-pointer
                            transition hover:border-blue-400 hover:bg-blue-50">

                            <svg class="w-14 h-14 text-blue-500 mb-4"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2a7 7 0 0 0-6.93 6.03A5.5 5.5 0 0 0 6.5 19H18a4 4 0 0 0 .48-7.97A6.5 6.5 0 0 0 12 2Zm0 5 4 4h-3v5h-2v-5H8l4-4Z"/>
                            </svg>

                            <h3 class="font-bold text-gray-800 text-lg">
                                Drag & Drop or Click to Upload
                            </h3>

                            <p class="text-sm text-gray-500 mt-2">
                                JPG, PNG, WebP or Avif. Max 50MB
                            </p>

                        </label>

                        <p id="fileCount"
                            class="text-sm text-blue-600 mt-3 text-center">
                        </p>

                    </div>

                    <!-- Format Selection -->
                    <div>

                        <h3 class="font-semibold text-gray-700 mb-4 text-center">
                            Select Output Format
                        </h3>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                            <!-- JPG -->
                            <label class="format-card border-2 border-gray-200 rounded-xl
                                p-2 cursor-pointer text-center transition">

                                <input type="radio" name="format"
                                    value="jpg" class="sr-only">

                                <span class="font-bold text-gray-600 block mb-3">
                                    JPG
                                </span>

                                <div class="bg-white rounded-lg border p-2">
                                    <svg viewBox="0 0 100 75" class="w-full">
                                        <rect x="2" y="2" width="96" height="71"
                                            fill="white" stroke="#94a3b8" stroke-width="2"/>
                                        <circle cx="23" cy="22" r="9" fill="#facc15"/>
                                        <path d="M5 65 L37 33 L53 48 L70 25 L95 65Z"
                                            fill="#60a5fa"/>
                                    </svg>
                                </div>

                            </label>

                            <!-- PNG -->
                            <label class="format-card border-2 border-gray-200 rounded-xl
                                p-2 cursor-pointer text-center transition">

                                <input type="radio" name="format"
                                    value="png" class="sr-only">

                                <span class="font-bold text-gray-600 block mb-3">
                                    PNG
                                </span>

                                <div class="bg-white rounded-lg border p-2">
                                    <svg viewBox="0 0 100 75" class="w-full">
                                        <rect x="2" y="2" width="96" height="71"
                                            fill="white" stroke="#94a3b8" stroke-width="2"/>
                                        <circle cx="23" cy="22" r="9" fill="#facc15"/>
                                        <path d="M5 65 L37 33 L53 48 L70 25 L95 65Z"
                                            fill="#4ade80"/>
                                    </svg>
                                </div>

                            </label>

                            <!-- WEBP -->
                            <label class="format-card border-2 border-green-500
                                bg-green-50 rounded-xl p-2 cursor-pointer
                                text-center transition">

                                <input type="radio" name="format"
                                    value="webp" checked class="sr-only">

                                <span class="font-bold text-green-700 block mb-3">
                                    WebP
                                </span>

                                <div class="bg-green-600 rounded-lg p-2">
                                    <svg viewBox="0 0 100 75" class="w-full">
                                        <rect x="2" y="2" width="96" height="71"
                                            fill="none" stroke="white" stroke-width="3"/>
                                        <circle cx="23" cy="22" r="9" fill="white"/>
                                        <path d="M5 65 L37 33 L53 48 L70 25 L95 65Z"
                                            fill="white"/>
                                    </svg>
                                </div>

                            </label>

                            <!-- AVIF -->
                            <label class="format-card border-2 border-gray-200 rounded-xl
                                p-2 cursor-pointer text-center transition">

                                <input type="radio" name="format"
                                    value="avif" class="sr-only">

                                <span class="font-bold text-gray-600 block mb-3">
                                    AVIF
                                </span>

                                <div class="bg-white rounded-lg border p-2">
                                    <div class="h-[75px] flex items-center justify-center
                                        text-3xl font-bold text-purple-600">
                                        AVIF
                                    </div>
                                </div>

                            </label>

                        </div>

                    </div>

                </div>

                <!-- Image Preview -->
                <div id="previewSection" class="hidden mt-7">

                    <h3 class="font-semibold text-gray-700 mb-3">
                        Selected Images
                    </h3>

                    <div id="previewContainer"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    </div>

                </div>

                <!-- Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-7">

                    <div class="sm:col-span-1">
                        <button
                            type="button"
                            id="resetButton"
                            class="w-full border border-gray-200 bg-gray-50
                            hover:bg-gray-100 text-gray-600 rounded-lg
                            py-3 font-semibold transition">

                            ↻ Start Over

                        </button>
                    </div>

                    <div class="sm:col-span-2">

                        <button
                            type="submit"
                            id="convertButton"
                            disabled
                            class="w-full bg-green-600 hover:bg-green-700
                            disabled:bg-gray-300 disabled:cursor-not-allowed
                            text-white font-semibold rounded-lg py-3
                            transition shadow-sm">

                            ⇩ Convert & Download Images

                        </button>

                    </div>

                </div>

            </form>

        </section>

        <!-- Footer -->
        <footer class="text-center mt-8 text-sm text-gray-500">
            Free Online Image Converter
            <p class="mt-1">Convert JPG, PNG, WebP or AVIF images easily.</p>
        </footer>

    </main>

    <script>

        const imageInput = document.getElementById('imageInput');
        const dropArea = document.getElementById('dropArea');
        const previewSection = document.getElementById('previewSection');
        const previewContainer = document.getElementById('previewContainer');
        const fileCount = document.getElementById('fileCount');
        const convertButton = document.getElementById('convertButton');
        const resetButton = document.getElementById('resetButton');
        const formatInputs = document.querySelectorAll('input[name="format"]');

        let selectedFiles = [];

        function updatePreviews() {

            previewContainer.innerHTML = '';

            selectedFiles.forEach(file => {

                const url = URL.createObjectURL(file);

                const card = document.createElement('div');

                card.className = 'border rounded-lg p-2 bg-gray-50';

                const image = document.createElement('img');

                image.src = url;
                image.className = 'w-full h-28 object-contain rounded';

                const name = document.createElement('p');

                name.textContent = file.name;
                name.className = 'text-xs text-gray-600 mt-2 truncate';

                card.appendChild(image);
                card.appendChild(name);

                previewContainer.appendChild(card);

            });

            previewSection.classList.toggle('hidden', selectedFiles.length === 0);

            fileCount.textContent = selectedFiles.length
                ? `${selectedFiles.length} image(s) selected`
                : '';

            convertButton.disabled = selectedFiles.length === 0;
        }

        function setFiles(files) {

            const validTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/avif'
            ];

            const validFiles = Array.from(files).filter(file => {

                return validTypes.includes(file.type) &&
                    file.size <= 50 * 1024 * 1024;

            });

            const totalSize = validFiles.reduce(
                (total, file) => total + file.size, 0
            );

            if (validFiles.length !== files.length) {
                alert('Only JPG, PNG and WebP images up to 50MB each are allowed.');
            }

            if (totalSize > 50 * 1024 * 1024) {
                alert('Total upload size cannot exceed 50MB.');
                return;
            }

            selectedFiles = validFiles;

            const dataTransfer = new DataTransfer();

            selectedFiles.forEach(file => dataTransfer.items.add(file));

            imageInput.files = dataTransfer.files;

            updatePreviews();
        }

        imageInput.addEventListener('change', function () {
            setFiles(this.files);
        });

        dropArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', function () {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', function (e) {
            e.preventDefault();
            dropArea.classList.remove('dragover');

            setFiles(e.dataTransfer.files);
        });

        formatInputs.forEach(input => {

            input.addEventListener('change', function () {

                document.querySelectorAll('.format-card').forEach(card => {

                    card.classList.remove(
                        'border-green-500',
                        'bg-green-50'
                    );

                    card.classList.add('border-gray-200');

                });

                this.closest('.format-card').classList.remove('border-gray-200');

                this.closest('.format-card').classList.add(
                    'border-green-500',
                    'bg-green-50'
                );

            });

        });

        resetButton.addEventListener('click', function () {

            selectedFiles = [];

            imageInput.value = '';

            previewContainer.innerHTML = '';

            previewSection.classList.add('hidden');

            fileCount.textContent = '';

            convertButton.disabled = true;

            formatInputs.forEach(input => {
                input.checked = input.value === 'webp';
            });

            document.querySelectorAll('.format-card').forEach(card => {

                card.classList.remove(
                    'border-green-500',
                    'bg-green-50'
                );

                card.classList.add('border-gray-200');

            });

            const webpCard = document.querySelector(
                'input[value="webp"]'
            ).closest('.format-card');

            webpCard.classList.remove('border-gray-200');

            webpCard.classList.add(
                'border-green-500',
                'bg-green-50'
            );

        });

    </script>

</body>
</html>

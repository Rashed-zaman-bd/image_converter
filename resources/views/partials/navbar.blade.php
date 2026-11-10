{{-- resources/views/partials/navbar.blade.php --}}
@php
    // Change the URLs to your real routes. Keep this list in sync with partials/image-tools.blade.php
    $converterLinks = [
        ['name' => 'Convert to WebP', 'url' => url('/') . '?format=webp', 'desc' => 'Small files for websites'],
        ['name' => 'Convert to AVIF', 'url' => url('/') . '?format=avif', 'desc' => 'Smallest files, newest format'],
        ['name' => 'Convert to PNG',  'url' => url('/') . '?format=png',  'desc' => 'Lossless, keeps transparency'],
        ['name' => 'Convert to JPG',  'url' => url('/') . '?format=jpg',  'desc' => 'Opens everywhere'],
    ];

    $toolLinks = [
        ['name' => 'Image Resizer',    'url' => url('/image-resizer'),    'desc' => 'Resize an image to any size'],
        ['name' => 'Crop Image',       'url' => url('/crop-image'),       'desc' => 'Crop unwanted areas'],
        ['name' => 'Image Compressor', 'url' => url('/image-compressor'), 'desc' => 'Reduce file size by up to 80 to 90%'],
        ['name' => 'Color Picker',     'url' => url('/color-picker'),     'desc' => 'Pick a color from your image'],
        ['name' => 'Image Enlarger',   'url' => url('/image-enlarger'),   'desc' => 'Make your images bigger'],
        ['name' => 'Collage Maker',    'url' => url('/collage-maker'),    'desc' => 'Create a photo collage'],
    ];

    $menus = [
        ['id' => 'menu-converter', 'label' => 'Image Converter', 'links' => $converterLinks],
        ['id' => 'menu-tools',     'label' => 'Image Tools',     'links' => $toolLinks],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur">
    <nav aria-label="Main" class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">

        <!-- Brand -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
            <img src="/images/logo.png" alt="" class="h-9 w-9 object-contain">
            <span class="font-display text-lg font-semibold tracking-tight">Image Converter</span>
        </a>

        <!-- Desktop menu -->
        <ul class="hidden items-center gap-1 md:flex">
            @foreach ($menus as $menu)
                <li class="relative">
                    <button type="button"
                        data-dropdown-toggle
                        aria-expanded="false"
                        aria-controls="{{ $menu['id'] }}"
                        class="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold text-slate-700 transition
                        hover:bg-slate-100 hover:text-ink focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                        {{ $menu['label'] }}
                        <svg class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div id="{{ $menu['id'] }}"
                        class="absolute right-0 z-50 mt-2 hidden w-72 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_20px_50px_-12px_rgba(20,23,51,.25)]">
                        @foreach ($menu['links'] as $link)
                            <a href="{{ $link['url'] }}"
                                class="block rounded-xl px-3 py-2.5 transition hover:bg-brand-50 focus-visible:bg-brand-50 focus-visible:outline-none">
                                <span class="block text-sm font-semibold text-ink">{{ $link['name'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $link['desc'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>

        <!-- Mobile toggle -->
        <button type="button"
            id="navToggle"
            aria-expanded="false"
            aria-controls="mobileMenu"
            aria-label="Open menu"
            class="grid h-10 w-10 place-items-center rounded-lg text-ink transition hover:bg-slate-100 md:hidden
            focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
            <svg id="navIconOpen" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
            <svg id="navIconClose" class="hidden h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </nav>

    <!-- Mobile menu -->
    <div id="mobileMenu" class="hidden max-h-[calc(100vh-4rem)] overflow-y-auto border-t border-slate-200 bg-white px-4 pb-5 pt-3 md:hidden">
        @foreach ($menus as $menu)
            <div class="{{ $loop->first ? '' : 'mt-4' }}">
                <p class="px-2 pb-1 text-sm font-semibold text-slate-400">{{ $menu['label'] }}</p>
                <ul>
                    @foreach ($menu['links'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                                class="block rounded-xl px-2 py-2.5 transition hover:bg-brand-50">
                                <span class="block text-sm font-semibold text-ink">{{ $link['name'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $link['desc'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</header>

<script>
    (function () {
        const toggles = document.querySelectorAll('[data-dropdown-toggle]');
        const navToggle = document.getElementById('navToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const iconOpen = document.getElementById('navIconOpen');
        const iconClose = document.getElementById('navIconClose');

        function closeDropdowns() {
            toggles.forEach(btn => {
                btn.setAttribute('aria-expanded', 'false');
                btn.querySelector('svg').classList.remove('rotate-180');
                document.getElementById(btn.getAttribute('aria-controls')).classList.add('hidden');
            });
        }

        function setMobileMenu(open) {
            mobileMenu.classList.toggle('hidden', !open);
            iconOpen.classList.toggle('hidden', open);
            iconClose.classList.toggle('hidden', !open);
            navToggle.setAttribute('aria-expanded', String(open));
            navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        }

        toggles.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();

                const wasOpen = btn.getAttribute('aria-expanded') === 'true';
                closeDropdowns();

                if (!wasOpen) {
                    btn.setAttribute('aria-expanded', 'true');
                    btn.querySelector('svg').classList.add('rotate-180');
                    document.getElementById(btn.getAttribute('aria-controls')).classList.remove('hidden');
                }
            });
        });

        navToggle.addEventListener('click', function () {
            setMobileMenu(navToggle.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-dropdown-toggle]') && !e.target.closest('#menu-converter, #menu-tools')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDropdowns();
                setMobileMenu(false);
            }
        });

        // Close the mobile menu if the window grows to desktop width
        window.matchMedia('(min-width: 768px)').addEventListener('change', function (e) {
            if (e.matches) setMobileMenu(false);
        });
    })();
</script>
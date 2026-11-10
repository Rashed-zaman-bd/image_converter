{{-- resources/views/partials/image-tools.blade.php --}}
@php
    // Change the URLs to your real routes, e.g. route('resizer.index')
    $tools = [
        ['name' => 'Image Resizer',    'url' => url('/image-resizer'),    'desc' => 'Quick and easy way to resize an image to any size'],
        ['name' => 'Crop Image',       'url' => url('/crop-image'),       'desc' => 'Use this tool to crop unwanted areas from your image'],
        ['name' => 'Image Compressor', 'url' => url('/image-compressor'), 'desc' => 'Reduce image file size by up to 80 to 90% using this tool'],
        ['name' => 'Color Picker',     'url' => url('/color-picker'),     'desc' => 'Quickly pick a color from the color wheel or from your image online'],
        ['name' => 'Image Enlarger',   'url' => url('/image-enlarger'),   'desc' => 'A fast way to make your images bigger'],
        ['name' => 'Collage Maker',    'url' => url('/collage-maker'),    'desc' => 'Create a beautiful photo collage from your photos'],
    ];
@endphp

<section class="mt-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_1px_0_rgba(20,23,51,.04),0_16px_40px_-16px_rgba(20,23,51,.15)] sm:p-8">

    <h2 class="font-display text-xl font-bold tracking-tight sm:text-2xl">
        Valuable Image Tools
    </h2>

    <p class="mt-3 text-slate-600">
        Here is a list of image tools to further edit your images.
    </p>

    <ol class="mt-5 list-decimal space-y-3 pl-6 marker:font-semibold marker:text-brand-500">
        @foreach ($tools as $tool)
            <li class="pl-2 leading-relaxed text-slate-700">
                <a href="{{ $tool['url'] }}"
                    class="font-medium text-brand-500 underline-offset-4 transition hover:text-brand-700 hover:underline focus-visible:rounded focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                    {{ $tool['name'] }}
                </a>
                <span class="text-slate-500">- {{ $tool['desc'] }}</span>
            </li>
        @endforeach
    </ol>

</section>
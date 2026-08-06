@props(['class' => 'w-20 h-20 fill-current text-gray-800 dark:text-gray-200'])

{{-- Ganti logo Laravel bawaan Breeze -> teks nama app doang (branding
     minimal, cepat). Kalau nanti mau logo gambar beneran, taruh file
     PNG/SVG di public/ dan ganti isi component ini jadi <img>. --}}
<div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}>
    <span class="text-3xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-400">
        CetakPro
    </span>
</div>

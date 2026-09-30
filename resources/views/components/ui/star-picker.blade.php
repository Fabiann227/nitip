@props(['name' => 'rating', 'value' => 5])
<div x-data="{ rating: {{ (int) old($name, $value) }}, hover: 0 }" class="flex items-center gap-1">
    <input type="hidden" name="{{ $name }}" :value="rating">
    @foreach (range(1, 5) as $star)
        <button type="button" class="p-0.5 transition-transform hover:scale-110" x-on:click="rating = {{ $star }}" x-on:mouseenter="hover = {{ $star }}" x-on:mouseleave="hover = 0" aria-label="{{ $star }} bintang">
            <span class="material-symbols-outlined text-[30px]" :class="(hover || rating) >= {{ $star }} ? 'filled text-amber-400' : 'text-slate-300'">star</span>
        </button>
    @endforeach
    <span class="ml-2 text-sm font-semibold text-on-surface" x-text="['', 'Buruk', 'Kurang', 'Cukup', 'Baik', 'Luar biasa'][hover || rating]"></span>
</div>

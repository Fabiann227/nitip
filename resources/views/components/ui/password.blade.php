@props(['name' => 'password', 'label' => 'Kata Sandi', 'placeholder' => 'Minimal 8 karakter', 'required' => true, 'hint' => null, 'icon' => 'lock', 'autocomplete' => 'current-password', 'id' => null, 'labelSlot' => null])
@php $id ??= $name; $hasError = $errors->has($name); @endphp
<div x-data="{ show: false }">
    @if ($label)
        <div class="flex items-center justify-between mb-1.5">
            <x-ui.label :for="$id" :required="$required" class="mb-0">{{ $label }}</x-ui.label>
            {{ $labelSlot ?? '' }}
        </div>
    @endif
    <div class="relative flex items-center">
        <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-placeholder pointer-events-none">{{ $icon }}</span>
        <input :type="show ? 'text' : 'password'" name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete }}"
               @if($required) required @endif @if($hasError) aria-invalid="true" @endif
               {{ $attributes->merge(['class' => 'input input-icon-left input-icon-right'.($hasError ? ' input-error' : '')]) }}>
        <button type="button" class="absolute right-3 text-slate-400 hover:text-slate-600 flex items-center" x-on:click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
            <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
    @if ($hint && ! $hasError)<p class="hint">{{ $hint }}</p>@endif
    <x-ui.field-error :name="$name" />
</div>

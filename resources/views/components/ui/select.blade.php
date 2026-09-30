@props(['name', 'label' => null, 'hint' => null, 'required' => false, 'optional' => false, 'id' => null, 'icon' => null, 'labelIcon' => null])
@php
    $id ??= str_replace(['[', ']'], ['-', ''], $name);
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $hasError = $errors->has($dotName);
@endphp
<div>
    @if ($label)
        <x-ui.label :for="$id" :required="$required" :optional="$optional" :icon="$labelIcon">{{ $label }}</x-ui.label>
    @endif
    <div class="relative flex items-center">
        @if ($icon)<span class="material-symbols-outlined absolute left-3.5 text-[20px] text-placeholder pointer-events-none z-10">{{ $icon }}</span>@endif
        <select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif @if($hasError) aria-invalid="true" @endif
                {{ $attributes->merge(['class' => 'input'.($icon ? ' input-icon-left' : '').($hasError ? ' input-error' : '')]) }}>
            {{ $slot }}
        </select>
    </div>
    @if ($hint && ! $hasError)<p class="hint">{{ $hint }}</p>@endif
    <x-ui.field-error :name="$name" />
</div>

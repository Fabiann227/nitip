@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'icon' => null, 'hint' => null, 'required' => false, 'optional' => false, 'id' => null, 'labelIcon' => null, 'labelHint' => null, 'prefix' => null, 'wrapperClass' => ''])
@php
    $id ??= str_replace(['[', ']'], ['-', ''], $name);
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $hasError = $errors->has($dotName);
    $val = $type === 'password' ? null : old($dotName, $value);
@endphp
<div class="{{ $wrapperClass }}">
    @if ($label)
        <x-ui.label :for="$id" :required="$required" :optional="$optional" :icon="$labelIcon" :hint="$labelHint">{{ $label }}</x-ui.label>
    @endif
    <div class="relative flex items-center">
        @if ($icon)
            <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-placeholder pointer-events-none">{{ $icon }}</span>
        @elseif ($prefix)
            <span class="absolute left-4 text-sm font-semibold text-muted pointer-events-none">{{ $prefix }}</span>
        @endif
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" @if($val !== null) value="{{ $val }}" @endif
               @if($required) required @endif
               @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
               {{ $attributes->merge(['class' => 'input'.($icon || $prefix ? ' input-icon-left' : '').($hasError ? ' input-error' : '')]) }}>
        {{ $slot }}
    </div>
    @if ($hint && ! $hasError)<p class="hint">{{ $hint }}</p>@endif
    <x-ui.field-error :name="$name" :id="$id.'-error'" />
</div>

@props(['name', 'value' => '1', 'checked' => false, 'id' => null, 'type' => 'checkbox'])
@php
    $id ??= str_replace(['[', ']'], ['-', ''], $name).'-'.$value;
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $old = old($dotName);
    $isChecked = $old !== null ? (is_array($old) ? in_array($value, $old) : (string) $old === (string) $value) : $checked;
@endphp
<label for="{{ $id }}" {{ $attributes->merge(['class' => 'flex items-start gap-3 cursor-pointer select-none']) }}>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked) class="{{ $type === 'radio' ? 'radio' : 'checkbox' }} mt-0.5 shrink-0">
    <span class="text-sm text-slate-600 leading-snug">{{ $slot }}</span>
</label>

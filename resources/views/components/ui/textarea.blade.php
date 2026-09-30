@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'optional' => false, 'id' => null, 'rows' => 3, 'labelIcon' => null])
@php
    $id ??= str_replace(['[', ']'], ['-', ''], $name);
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $hasError = $errors->has($dotName);
@endphp
<div>
    @if ($label)
        <x-ui.label :for="$id" :required="$required" :optional="$optional" :icon="$labelIcon">{{ $label }}</x-ui.label>
    @endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" @if($required) required @endif @if($hasError) aria-invalid="true" @endif
              {{ $attributes->merge(['class' => 'input'.($hasError ? ' input-error' : '')]) }}>{{ old($dotName, $value) }}</textarea>
    @if ($hint && ! $hasError)<p class="hint">{{ $hint }}</p>@endif
    <x-ui.field-error :name="$name" />
</div>

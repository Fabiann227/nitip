@props(['name', 'label', 'locations' => [], 'value' => null, 'placeholder' => 'Ketik atau pilih lokasi', 'required' => true, 'hint' => null, 'icon' => 'location_on', 'listId' => null])
@php $listId ??= 'list-'.str_replace(['[', ']'], ['-', ''], $name); @endphp
<x-ui.input :name="$name" :label="$label" :value="$value" :icon="$icon" :placeholder="$placeholder" :required="$required" :hint="$hint" list="{{ $listId }}" autocomplete="off" />
<datalist id="{{ $listId }}">
    @foreach ($locations as $location)
        <option value="{{ $location }}"></option>
    @endforeach
</datalist>

@props(['name', 'id' => null])
@php $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.'); @endphp
@error($dotName)
    <p class="error-text" @if($id) id="{{ $id }}" @endif role="alert">
        <span class="material-symbols-outlined text-[14px]">error</span>
        <span>{{ $message }}</span>
    </p>
@enderror

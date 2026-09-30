@props(['title' => null, 'bodyClass' => 'bg-surface'])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#006041">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Nitip') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="min-h-full flex flex-col {{ $bodyClass }}">
    {{-- Alpine root: every x-* directive (incl. teleported modals) must live inside an x-data tree --}}
    <div x-data class="min-h-full flex-1 flex flex-col">
        {{ $slot }}

        <x-ui.toast />

        {{-- Flash messages become toasts --}}
        @if (session('success'))
            <div x-init="$store.toast.push({ type: 'success', message: @js(session('success')) })"></div>
        @endif
        @if (session('error'))
            <div x-init="$store.toast.push({ type: 'error', message: @js(session('error')), timeout: 8000 })"></div>
        @endif
        @if (session('status'))
            <div x-init="$store.toast.push({ type: 'info', message: @js(session('status')), timeout: 8000 })"></div>
        @endif
        @if (session('warning'))
            <div x-init="$store.toast.push({ type: 'warning', message: @js(session('warning')), timeout: 8000 })"></div>
        @endif
        @if ($errors->any())
            <div x-init="$store.toast.push({ type: 'error', title: 'Periksa kembali isianmu', message: @js($errors->first()) })"></div>
        @endif
    </div>

    {{ $scripts ?? '' }}
</body>
</html>

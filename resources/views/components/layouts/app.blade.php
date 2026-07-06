<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @isset($page)
        <link rel="icon" href="{{ asset('storage/' . $page->logo) }}">
        <title>{{ $page->name }} | {{ config('app.name', 'Undangan Kita') }}</title>
        
        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $page->name }}">
        <meta property="og:description" content="{{ $page->description ?? 'Undangan Pernikahan Digital' }}">
        <meta property="og:image" content="{{ $page->logo ? asset(Storage::url($page->logo)) : asset('assets/img/default-page.png') }}">
        <meta property="og:image:width" content="300">
        <meta property="og:image:height" content="300">
    @else
        <title>{{ config('app.name', 'Undangan Kita') }}</title>
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'Undangan Kita') }}">
        <meta property="og:image" content="{{ asset('assets/img/default-page.png') }}">
    @endisset
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-200 antialiased">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')
</body>
</html>

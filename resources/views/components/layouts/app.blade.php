<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @isset($page)
        <link rel="icon" href="{{ asset('storage/' . $page->logo) }}">
        <title>{{ $page->name }} | {{ config('app.name', 'Undangan Kita') }}</title>
    @else
        <title>{{ config('app.name', 'Undangan Kita') }}</title>
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

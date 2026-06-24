<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'NutriLens') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F0F7F4] flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('login') }}" class="text-2xl font-bold text-[#2D6A4F]">NutriLens</a>
            <p class="text-sm text-gray-500 mt-1">Catat nutrisi harianmu dengan mudah</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-8">
            {{ $slot }}
        </div>
    </div>

</body>
</html>

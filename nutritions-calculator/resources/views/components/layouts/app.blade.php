<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="yolo-api-url" content="{{ config('services.yolo.url') }}">
    <title>{{ $title ?? config('app.name', 'NutriLens') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="min-h-screen bg-[#F0F7F4]">

    {{-- Navbar --}}
    <header class="bg-[#F0F7F4] sticky top-0 z-40 border-b border-[#E0EEE8]">
        <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">

            <a href="{{ route('home') }}" class="text-xl font-bold text-[#2D6A4F]">NutriLens</a>

            <nav class="flex items-center bg-white/60 rounded-full p-1 gap-0.5 shadow-sm">
                <a href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'bg-white shadow text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700' }} px-6 py-1.5 rounded-full text-sm transition-all">
                    Home
                </a>
                <a href="{{ route('history') }}"
                    class="{{ request()->routeIs('history') ? 'bg-white shadow text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700' }} px-6 py-1.5 rounded-full text-sm transition-all">
                    Histori
                </a>
                <a href="{{ route('profile') }}"
                    class="{{ request()->routeIs('profile') ? 'bg-white shadow text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700' }} px-6 py-1.5 rounded-full text-sm transition-all">
                    Profile
                </a>
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="bg-[#2D6A4F] hover:bg-[#245740] text-white px-5 py-2 rounded-full text-sm font-semibold transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </header>

    {{-- Camera Scan Modal --}}
    <div id="camera-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        onclick="if(event.target===this)closeUploadModal()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Scan Makanan</h3>
                <button type="button" onclick="closeUploadModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <p id="camera-error-label"
                class="hidden mb-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Makan</label>
                <select id="camera-meal-type"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]">
                    <option value="pagi">Makan Pagi</option>
                    <option value="siang">Makan Siang</option>
                    <option value="malam">Makan Malam</option>
                </select>
            </div>

            <div class="relative rounded-xl overflow-hidden bg-gray-900 mb-4 aspect-video">
                <video id="camera-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <canvas id="camera-canvas" class="hidden"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4 p-4 bg-[#F0F7F4] rounded-xl">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Terdeteksi</p>
                    <p id="camera-detection-label" class="font-semibold text-gray-900 text-sm">—</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Confidence</p>
                    <p id="camera-confidence-label" class="font-semibold text-[#2D6A4F] text-sm">—</p>
                </div>
            </div>

            <p id="camera-status-label" class="text-xs text-gray-500 mb-4">Membuka kamera...</p>

            <div class="flex gap-3">
                <button type="button" onclick="closeUploadModal()"
                    class="flex-1 py-2.5 border border-gray-200 text-gray-700 font-semibold rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="button" id="camera-confirm-btn" disabled
                    class="flex-1 py-2.5 bg-[#2D6A4F] hover:bg-[#245740] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-sm transition-colors">
                    Simpan Deteksi
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="max-w-6xl mx-auto px-6 pt-4">
        @if (session('success'))
            <div class="p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 mb-2">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 mb-2">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <main class="max-w-6xl mx-auto px-6 py-6">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>

</html>
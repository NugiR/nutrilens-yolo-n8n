<x-layouts.auth title="Login — NutriLens">

    <h2 class="text-xl font-bold text-gray-900 mb-6">Masuk ke akun kamu</h2>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F] @error('email') border-red-400 @else border-gray-200 @enderror"
                placeholder="email@contoh.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]"
                placeholder="••••••••"
            >
        </div>

        <div class="flex items-center gap-2">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                class="w-4 h-4 rounded border-gray-300 text-[#2D6A4F] focus:ring-[#2D6A4F]"
            >
            <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#2D6A4F] hover:bg-[#245740] text-white font-semibold rounded-xl text-sm transition-colors"
        >
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-[#2D6A4F] font-medium hover:underline">Daftar</a>
    </p>

</x-layouts.auth>

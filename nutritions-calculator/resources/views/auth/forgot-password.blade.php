<x-layouts.auth title="Lupa Password — NutriLens">

    <h2 class="text-xl font-bold text-gray-900 mb-2">Lupa password?</h2>
    <p class="text-sm text-gray-500 mb-6">
        Masukkan email kamu dan kami akan kirim link reset password.
    </p>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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
                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F] @error('email') border-red-400 @else border-gray-200 @enderror"
                placeholder="email@contoh.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#2D6A4F] hover:bg-[#245740] text-white font-semibold rounded-xl text-sm transition-colors"
        >
            Kirim Link Reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="text-[#2D6A4F] font-medium hover:underline">← Kembali ke login</a>
    </p>

</x-layouts.auth>

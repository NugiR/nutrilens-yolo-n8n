<x-layouts.app title="Profile — NutriLens">

@php
    $initial = strtoupper(substr($user->full_name ?? $user->name ?? 'U', 0, 1));

    $bmi       = null;
    $bmiLabel  = null;
    $bmiColor  = null;
    if ($user->height_cm && $user->weight_kg) {
        $h        = $user->height_cm / 100;
        $bmi      = round($user->weight_kg / ($h * $h), 1);
        $bmiLabel = match(true) {
            $bmi < 18.5 => 'Kurus',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            default     => 'Obesitas',
        };
        $bmiColor = match(true) {
            $bmi < 18.5 => 'text-blue-500',
            $bmi < 25.0 => 'text-[#2D6A4F]',
            $bmi < 30.0 => 'text-orange-500',
            default     => 'text-red-500',
        };
    }
@endphp

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Profile</h1>
    <p class="text-gray-500 mt-1">Informasi personal dan BMI yang dihitung di Laravel Blade.</p>
</div>

{{-- Flash --}}
@if (session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

{{-- Profile Card --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

    {{-- Avatar + Name + BMI --}}
    <div class="flex items-start gap-5 mb-8">
        <div class="shrink-0">
            @if ($user->photo_path)
                <img src="{{ asset('storage/' . $user->photo_path) }}"
                     alt="{{ $user->full_name ?? $user->name }}"
                     class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-[#D4EDDA] flex items-center justify-center">
                    <span class="text-2xl font-bold text-[#2D6A4F]">{{ $initial }}</span>
                </div>
            @endif
        </div>

        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-900">{{ $user->full_name ?? $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>

        @if ($bmi)
            <div class="shrink-0 bg-[#F0F7F4] rounded-2xl px-6 py-4 text-center">
                <p class="text-xs font-medium text-gray-500 mb-1">BMI</p>
                <p class="text-3xl font-bold text-gray-900">{{ $bmi }}</p>
                <p class="text-sm font-semibold {{ $bmiColor }} mt-0.5">{{ $bmiLabel }}</p>
            </div>
        @endif
    </div>

    {{-- Fields --}}
    <dl class="divide-y divide-gray-100">
        <div class="py-3.5 flex items-center gap-4">
            <dt class="w-36 text-sm text-gray-400 shrink-0">Nama lengkap</dt>
            <dd class="text-sm font-semibold text-gray-900">{{ $user->full_name ?? '—' }}</dd>
        </div>
        <div class="py-3.5 flex items-center gap-4">
            <dt class="w-36 text-sm text-gray-400 shrink-0">Gender</dt>
            <dd class="text-sm font-semibold text-gray-900">{{ $user->gender?->label() ?? '—' }}</dd>
        </div>
        <div class="py-3.5 flex items-center gap-4">
            <dt class="w-36 text-sm text-gray-400 shrink-0">Email</dt>
            <dd class="text-sm font-semibold text-gray-900">{{ $user->email }}</dd>
        </div>
        <div class="py-3.5 flex items-center gap-4">
            <dt class="w-36 text-sm text-gray-400 shrink-0">Tinggi badan</dt>
            <dd class="text-sm font-semibold text-gray-900">
                {{ $user->height_cm ? $user->height_cm . ' cm' : '—' }}
            </dd>
        </div>
        <div class="py-3.5 flex items-center gap-4">
            <dt class="w-36 text-sm text-gray-400 shrink-0">Berat badan</dt>
            <dd class="text-sm font-semibold text-gray-900">
                {{ $user->weight_kg ? $user->weight_kg . ' kg' : '—' }}
            </dd>
        </div>
    </dl>

    <div class="mt-6">
        <button onclick="document.getElementById('edit-form').classList.toggle('hidden')"
                class="px-5 py-2 bg-[#2D6A4F] hover:bg-[#245740] text-white rounded-xl text-sm font-semibold transition-colors">
            Edit Profil
        </button>
    </div>
</div>

{{-- Edit Form --}}
<div id="edit-form" class="hidden mt-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h3 class="text-lg font-bold text-gray-900 mb-5">Edit Profil</h3>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]">
                @error('full_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]">
                    <option value="">— Pilih gender —</option>
                    @foreach (\App\Enums\Gender::cases() as $g)
                        <option value="{{ $g->value }}" {{ old('gender', $user->gender?->value) === $g->value ? 'selected' : '' }}>
                            {{ $g->label() }}
                        </option>
                    @endforeach
                </select>
                @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi Badan (cm)</label>
                <input type="number" name="height_cm" value="{{ old('height_cm', $user->height_cm) }}" min="50" max="250"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]"
                       placeholder="165">
                @error('height_cm') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berat Badan (kg)</label>
                <input type="number" name="weight_kg" value="{{ old('weight_kg', $user->weight_kg) }}" min="10" max="300" step="0.1"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F]"
                       placeholder="58">
                @error('weight_kg') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-[#F0F7F4] file:text-[#2D6A4F] file:font-medium hover:file:bg-green-100">
                @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-6 py-2.5 bg-[#2D6A4F] hover:bg-[#245740] text-white rounded-xl text-sm font-semibold transition-colors">
                Simpan
            </button>
            <button type="button" onclick="document.getElementById('edit-form').classList.add('hidden')"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition-colors">
                Batal
            </button>
        </div>
    </form>
</div>

</x-layouts.app>

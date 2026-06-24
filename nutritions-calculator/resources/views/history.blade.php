<x-layouts.app title="Riwayat Nutrisi — NutriLens">


<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Riwayat Nutrisi</h1>
    <p class="text-gray-500 mt-1">Lihat data makanan, detail nutrisi, dan kesimpulan AI yang pernah disimpan.</p>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('history') }}" class="flex flex-wrap items-end gap-3 mb-8">
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal tertentu</label>
        <input type="date" name="date" value="{{ request('date') }}"
                class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F] bg-white">
    </div>
    <div class="flex items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F] bg-white">
        </div>
        <span class="text-gray-400 pb-2">—</span>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2D6A4F]/40 focus:border-[#2D6A4F] bg-white">
        </div>
    </div>
    <div class="flex gap-2">
        <button type="submit"
                class="px-6 py-2 bg-[#2D6A4F] hover:bg-[#245740] text-white rounded-xl text-sm font-semibold transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['date', 'date_from', 'date_to']))
            <a href="{{ route('history') }}"
                class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition-colors">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- Log List --}}
@if ($logs->isEmpty())
    <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
        <div class="text-5xl mb-3">🍽️</div>
        <p class="text-gray-500 font-medium">Belum ada riwayat makanan</p>
        <p class="text-gray-400 text-sm mt-1">Scan makanan dengan kamera di halaman Home untuk mulai mencatat.</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2 bg-[#2D6A4F] text-white rounded-xl text-sm font-semibold hover:bg-[#245740] transition-colors">
            Ke Halaman Home
        </a>
    </div>
@else
    <div class="space-y-4">
        @foreach ($logs as $log)
            @php
                $ai     = $log->aiResult;
                $status = $ai?->calorie_status;
                $bgClass = $status?->bgClass() ?? 'bg-gray-50 border-gray-200';
            @endphp
            <div class="rounded-2xl border p-5 {{ $bgClass }} transition-shadow hover:shadow-md">
                <div class="flex gap-4">

                    {{-- Photo --}}
                    <div class="shrink-0">
                        @if (filled($log->photo_path))
                            <img src="{{ asset('storage/' . $log->photo_path) }}"
                                alt="Foto makanan"
                                class="w-24 h-24 rounded-xl object-cover bg-white/50">
                        @else
                            <div class="w-24 h-24 rounded-xl bg-white/50 flex flex-col items-center justify-center text-center px-1">
                                <span class="text-2xl">🍽️</span>
                                @if ($log->detected_food_name)
                                    <span class="text-[10px] text-gray-500 mt-1 line-clamp-2 leading-tight">{{ $log->detected_food_name }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                @if ($log->status === \App\Enums\MealLogStatus::Pending)
                                    <p class="font-bold text-gray-700">{{ $log->detected_food_name ?? 'Menganalisis...' }}</p>
                                    @if ($log->detection_confidence)
                                        <p class="text-xs text-amber-600 mt-0.5">Confidence {{ number_format($log->detection_confidence * 100, 0) }}% — menunggu nutrisi</p>
                                    @endif
                                @elseif ($log->status === \App\Enums\MealLogStatus::Failed)
                                    <p class="font-bold text-red-600">Analisis gagal</p>
                                @elseif ($ai)
                                    <p class="font-bold text-gray-900">{{ $status?->label() ?? 'Selesai' }}</p>
                                @else
                                    <p class="font-bold text-gray-700">Belum dianalisis</p>
                                @endif

                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $log->meal_type->label() }} &bull;
                                    {{ \Carbon\Carbon::parse($log->date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                                </p>

                                @if ($ai)
                                    <p class="font-bold text-gray-900 mt-2">{{ $ai->food_name }}</p>
                                @endif
                            </div>

                            {{-- Nutrients --}}
                            @if ($ai)
                                <div class="text-sm text-gray-700 space-y-0.5">
                                    <p>Kalori: <span class="font-medium">{{ number_format($ai->calories, 0) }} kcal</span></p>
                                    <p>Protein: <span class="font-medium">{{ number_format($ai->protein_g, 0) }} g</span></p>
                                    <p>Karbohidrat: <span class="font-medium">{{ number_format($ai->carbs_g, 0) }} g</span></p>
                                    <p>Lemak: <span class="font-medium">{{ number_format($ai->fat_g, 0) }} g</span></p>
                                    @if ($ai->fiber_g)
                                        <p>Serat: <span class="font-medium">{{ number_format($ai->fiber_g, 0) }} g</span></p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- AI Summary --}}
                        @if ($ai?->summary)
                            <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                <span class="font-medium text-gray-700">AI:</span> {{ $ai->summary }}
                            </p>
                        @endif
                    </div>

                    {{-- Delete --}}
                    <div class="shrink-0">
                        <form method="POST" action="{{ route('meal-logs.destroy', $log->id) }}"
                            onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

</x-layouts.app>

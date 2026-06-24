<x-layouts.app title="Home — NutriLens">

@php
    $pagiLog  = $todayLogs->first(fn($l) => $l->meal_type === \App\Enums\MealType::Pagi);
    $siangLog = $todayLogs->first(fn($l) => $l->meal_type === \App\Enums\MealType::Siang);
    $malamLog = $todayLogs->first(fn($l) => $l->meal_type === \App\Enums\MealType::Malam);

    $mealSlots = [
        ['key' => 'pagi',  'label' => 'Makan Pagi',  'log' => $pagiLog],
        ['key' => 'siang', 'label' => 'Makan Siang', 'log' => $siangLog],
        ['key' => 'malam', 'label' => 'Makan Malam', 'log' => $malamLog],
    ];
@endphp

{{-- Greeting --}}
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Hallo, {{ auth()->user()->full_name ?? auth()->user()->name }} 👋</h1>
    <p class="text-gray-500 mt-1">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
</div>

{{-- Meal Slots --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach ($mealSlots as $slot)
        @php $log = $slot['log']; @endphp
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col"
             @if($log) data-meal-log-id="{{ $log->id }}" data-meal-status="{{ $log->status->value }}" @endif>

            {{-- Card Header --}}
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-base">{{ $slot['label'] }}</h2>
                @if (!$log)
                    <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full font-medium">Kosong</span>
                @elseif ($log->status === \App\Enums\MealLogStatus::Pending)
                    <span class="text-xs text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full font-medium">Analisis...</span>
                @elseif ($log->status === \App\Enums\MealLogStatus::Failed)
                    <span class="text-xs text-red-500 bg-red-50 px-2.5 py-1 rounded-full font-medium">Gagal</span>
                @else
                    <span class="text-xs text-[#2D6A4F] bg-[#F0F7F4] px-2.5 py-1 rounded-full font-medium">Selesai</span>
                @endif
            </div>

            {{-- Card Body --}}
            <div class="flex gap-4 flex-1">

                {{-- Photo --}}
                <div class="shrink-0">
                    @if ($log && filled($log->photo_path))
                        <img src="{{ asset('storage/' . $log->photo_path) }}"
                             alt="Foto {{ $slot['label'] }}"
                             class="w-[72px] h-[72px] rounded-xl object-cover bg-gray-100">
                    @elseif ($log && $log->detected_food_name)
                        <div class="w-[72px] h-[72px] rounded-xl bg-[#F0F7F4] border border-[#2D6A4F]/20 flex items-center justify-center text-2xl">
                            🍽️
                        </div>
                    @else
                        <button onclick="openUploadModal('{{ $slot['key'] }}')"
                                class="w-[72px] h-[72px] rounded-xl bg-gray-100 hover:bg-[#F0F7F4] border-2 border-dashed border-gray-200 hover:border-[#2D6A4F] flex items-center justify-center transition-colors group"
                                title="Scan dengan kamera">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-300 group-hover:text-[#2D6A4F] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Detail --}}
                <div class="flex-1 min-w-0 flex flex-col justify-center">
                    @if ($log && $log->status === \App\Enums\MealLogStatus::Done && $log->aiResult)
                        @php $ai = $log->aiResult; @endphp
                        <p class="font-semibold text-gray-900 text-sm leading-tight mb-2 truncate">{{ $ai->food_name }}</p>
                        <div class="space-y-0.5 text-xs text-gray-500">
                            <p><span class="font-medium text-gray-700">Kalori</span> {{ number_format($ai->calories, 0) }} kkal</p>
                            <p><span class="font-medium text-gray-700">Protein</span> {{ number_format($ai->protein_g, 1) }} g</p>
                            <p><span class="font-medium text-gray-700">Karbo</span> {{ number_format($ai->carbs_g, 1) }} g</p>
                            <p><span class="font-medium text-gray-700">Lemak</span> {{ number_format($ai->fat_g, 1) }} g</p>
                        </div>
                        <form method="POST" action="{{ route('meal-logs.destroy', $log->id) }}" class="mt-2"
                              onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Hapus</button>
                        </form>
                    @elseif ($log && $log->status === \App\Enums\MealLogStatus::Pending)
                        <p class="font-semibold text-gray-900 text-sm leading-tight mb-1 truncate">
                            {{ $log->detected_food_name ?? 'Makanan terdeteksi' }}
                        </p>
                        @if ($log->detection_confidence)
                            <p class="text-xs text-gray-500 mb-2">
                                Confidence {{ number_format($log->detection_confidence * 100, 0) }}%
                            </p>
                        @endif
                        <p class="text-xs text-gray-400 leading-relaxed">Menunggu analisis nutrisi dari n8n...</p>
                        <div class="mt-2 flex gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:0s"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.15s"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay:.3s"></span>
                        </div>
                        <form method="POST" action="{{ route('meal-logs.destroy', $log->id) }}" class="mt-2"
                              onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Hapus</button>
                        </form>
                    @else
                        <p class="font-medium text-gray-700 text-sm mb-1">Detail nutrisi</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Scan makanan dengan kamera agar detail nutrisi tampil otomatis.</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Calorie Insight --}}
@if ($hasInsight)
    @php
        $totalCalories = $dailySummary?->total_calories
            ?? $todayLogs->sum(fn($l) => $l->aiResult?->calories ?? 0);
        $status = match(true) {
            $totalCalories < 1200 => ['label' => 'Kurang',    'class' => 'bg-orange-100 text-orange-700'],
            $totalCalories > 2400 => ['label' => 'Kelebihan', 'class' => 'bg-red-100 text-red-700'],
            default               => ['label' => 'Cukup',     'class' => 'bg-[#D4EDDA] text-[#2D6A4F]'],
        };
    @endphp
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Total Kalori Hari Ini</p>
            <p class="text-3xl font-bold text-[#2D6A4F] mt-0.5">{{ number_format($totalCalories, 0, ',', '.') }} <span class="text-lg font-medium">kkal</span></p>
        </div>
        <span class="px-4 py-2 {{ $status['class'] }} rounded-xl text-sm font-semibold">{{ $status['label'] }}</span>
    </div>
@endif

{{-- Nutrition Chart --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

    {{-- Filter Pills --}}
    <div class="flex flex-wrap gap-2 mb-6 justify-center">
        @php
            $nutrients = [
                ['key' => 'protein',    'label' => 'Protein',     'color' => '#2D6A4F'],
                ['key' => 'vitamin',    'label' => 'Vitamin',     'color' => '#4299E1'],
                ['key' => 'karbo',      'label' => 'Karbohidrat', 'color' => '#ED8936'],
                ['key' => 'lemak',      'label' => 'Lemak',       'color' => '#ED64A6'],
                ['key' => 'serat',      'label' => 'Serat',       'color' => '#38B2AC'],
            ];
        @endphp
        @foreach ($nutrients as $n)
            <button data-nutrient="{{ $n['key'] }}"
                    onclick="toggleNutrient('{{ $n['key'] }}')"
                    class="nutrient-pill flex items-center gap-2 px-4 py-2 rounded-full border-2 text-sm font-medium transition-all"
                    style="border-color: {{ $n['color'] }}; color: {{ $n['color'] }};">
                <span class="w-3 h-3 rounded-full" style="background: {{ $n['color'] }};"></span>
                {{ $n['label'] }}
            </button>
        @endforeach
    </div>

    <h2 class="text-lg font-bold text-gray-900 mb-1">Grafik nutrisi bulan ini</h2>
    <p class="text-xs text-gray-400 mb-4">Sumbu kiri: jumlah nutrisi. Sumbu bawah: tanggal bulan berjalan.</p>

    <div class="relative h-64">
        <canvas id="nutrition-chart"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const COLORS = {
    protein: '#2D6A4F',
    vitamin: '#4299E1',
    karbo:   '#ED8936',
    lemak:   '#ED64A6',
    serat:   '#38B2AC',
};

let chart = null;
let activeNutrients = new Set(['protein', 'vitamin', 'karbo', 'lemak', 'serat']);

async function loadChart(month) {
    const res  = await fetch(`/api/chart-data?month=${month}`);
    const data = await res.json();

    const labels  = data.labels ?? [];
    const datasets = [
        { key: 'protein', label: 'Protein' },
        { key: 'vitamin', label: 'Vitamin' },
        { key: 'karbo',   label: 'Karbohidrat' },
        { key: 'lemak',   label: 'Lemak' },
        { key: 'serat',   label: 'Serat' },
    ].map(n => ({
        label:           n.label,
        data:            data[n.key] ?? [],
        borderColor:     COLORS[n.key],
        backgroundColor: COLORS[n.key] + '22',
        borderWidth:     2,
        pointRadius:     3,
        tension:         0.3,
        hidden:          !activeNutrients.has(n.key),
        nutrientKey:     n.key,
    }));

    if (chart) chart.destroy();

    const ctx = document.getElementById('nutrition-chart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#f3f4f6' } },
                y: { grid: { color: '#f3f4f6' }, beginAtZero: true },
            },
        },
    });
}

function toggleNutrient(key) {
    const btn = document.querySelector(`[data-nutrient="${key}"]`);
    if (activeNutrients.has(key)) {
        activeNutrients.delete(key);
        btn.style.opacity = '0.4';
    } else {
        activeNutrients.add(key);
        btn.style.opacity = '1';
    }
    if (chart) {
        const ds = chart.data.datasets.find(d => d.nutrientKey === key);
        if (ds) { ds.hidden = !activeNutrients.has(key); chart.update(); }
    }
}

const currentMonth = '{{ now()->format('Y-m') }}';
loadChart(currentMonth);
</script>
@endpush

</x-layouts.app>

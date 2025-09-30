<x-filament-widgets::widget>
    <div class="flex overflow-x-auto space-x-4 pb-2">
        @foreach ($this->getStats() as $stat)
            <div class="min-w-[220px] shrink-0 rounded-xl shadow p-4 bg-white">
                <div class="text-sm font-medium text-gray-500">
                    {{ $stat['label'] }}
                </div>
                <div class="text-2xl font-bold text-{{ $stat['color'] }}-600">
                    {{ $stat['value'] }}
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>

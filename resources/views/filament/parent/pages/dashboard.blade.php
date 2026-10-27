<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Main Content Widgets --}}
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($this->getWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

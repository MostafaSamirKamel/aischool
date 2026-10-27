<x-filament-panels::page>
    <div>
        @foreach($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>

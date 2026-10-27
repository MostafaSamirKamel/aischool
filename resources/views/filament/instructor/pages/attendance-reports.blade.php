<div>
    <x-filament-panels::page>
        <form wire:submit="$refresh" class="mb-6">
            {{ $this->form }}
        </form>

        {{ $this->table }}
    </x-filament-panels::page>
</div>

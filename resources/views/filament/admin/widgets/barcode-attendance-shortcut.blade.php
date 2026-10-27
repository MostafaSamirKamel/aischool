<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            تسجيل الحضور بالباركود
        </x-slot>
        <x-slot name="description">
            اختصار سريع لتسجيل حضور الطلاب في جلسة اليوم.
        </x-slot>

        <x-filament::button
            :href="$this->getUrl()"
            tag="a"
            color="success"
            icon="heroicon-o-qr-code"
        >
            افتح شاشة الباركود
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>

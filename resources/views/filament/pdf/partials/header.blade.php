@php
    $brandName = \App\Helpers\SettingsHelper::getSiteTitle();
    $logoSetting = \App\Helpers\SettingsHelper::getSiteLogo();
    $logoSrc = null;

    if ($logoSetting) {
        if (\Illuminate\Support\Str::startsWith($logoSetting, ['http://', 'https://', 'data:image'])) {
            $logoSrc = $logoSetting;
        } elseif (file_exists(public_path('storage/' . ltrim($logoSetting, '/')))) {
            $logoSrc = 'file://' . public_path('storage/' . ltrim($logoSetting, '/'));
        } elseif (file_exists(public_path(ltrim($logoSetting, '/')))) {
            $logoSrc = 'file://' . public_path(ltrim($logoSetting, '/'));
        } elseif (file_exists(storage_path('app/public/' . ltrim($logoSetting, '/')))) {
            $logoSrc = 'file://' . storage_path('app/public/' . ltrim($logoSetting, '/'));
        }
    }
@endphp

<div class="pdf-header">
    <div class="pdf-header-main">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="logo" class="pdf-logo">
        @endif
        <div class="pdf-brand-block">
            <div class="pdf-brand">{{ $brandName }}</div>
            <div class="pdf-title">{{ $title ?? 'تقرير' }}</div>
            @if(!empty($subtitle))
                <div class="pdf-subtitle">{{ $subtitle }}</div>
            @endif
        </div>
    </div>
    <div class="pdf-meta">
        <span>تاريخ التصدير: {{ now()->format('Y-m-d H:i') }}</span>
        @if(!empty($period))
            <span class="pdf-period">{{ $period }}</span>
        @endif
    </div>
</div>

<?php

namespace App\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleSwitcherPlugin implements Plugin
{
    use EvaluatesClosures;

    protected array $locales = [
        'ar' => 'العربية',
        'en' => 'English',
    ];

    protected string $defaultLocale = 'ar';

    public function getId(): string
    {
        return 'locale-switcher';
    }

    public function register(Panel $panel): void
    {
        // تعيين اللغة الافتراضية
        $locale = Session::get('locale', $this->defaultLocale);
        App::setLocale($locale);
        
        $panel->renderHook(
            'panels::user-menu.before',
            fn (): string => view('filament.components.locale-switcher', [
                'locales' => $this->locales,
                'currentLocale' => App::getLocale(),
            ])->render()
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function locales(array $locales): static
    {
        $this->locales = $locales;
        return $this;
    }

    public function defaultLocale(string $locale): static
    {
        $this->defaultLocale = $locale;
        return $this;
    }
}

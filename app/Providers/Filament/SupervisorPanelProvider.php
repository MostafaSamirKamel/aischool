<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path('supervisor')
            ->maxContentWidth(Width::Full)
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->profile()
            ->darkMode(true)
            ->font('Tajawal')
            ->brandName('👨‍🏫 ' . __('panels.supervisor_panel', [], 'ar') ?: 'لوحة المشرف')
            ->topNavigation()
            ->topbarLivewireComponent(\App\Filament\Components\EmployeeTopNavbar::class)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => '<script>(function(){function apply(){var t=localStorage.getItem("theme");if(!t){t="light";localStorage.setItem("theme",t);}if(t==="dark"){document.documentElement.classList.add("dark");}else{document.documentElement.classList.remove("dark");}}apply();document.addEventListener("livewire:navigated",apply);document.addEventListener("DOMContentLoaded",apply);})();</script>' . '<link rel="stylesheet" href="' . asset('css/custom-theme.css') . '?v=' . (file_exists(public_path('css/custom-theme.css')) ? filemtime(public_path('css/custom-theme.css')) : time()) . '">' . "\n" . '<link rel="stylesheet" href="' . asset('css/employee-navbar.css') . '?v=' . (file_exists(public_path('css/employee-navbar.css')) ? filemtime(public_path('css/employee-navbar.css')) : time()) . '">',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => '<button type="button" onclick="const t = localStorage.getItem(\'theme\') === \'dark\' ? \'light\' : \'dark\'; localStorage.setItem(\'theme\', t); if (t === \'dark\') { document.documentElement.classList.add(\'dark\'); } else { document.documentElement.classList.remove(\'dark\'); } window.dispatchEvent(new CustomEvent(\'theme-changed\', {detail: t}));" class="ai-theme-toggle-btn fi-icon-btn flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition mr-2" title="تبديل الوضع الليلي / النهاري" aria-label="تبديل الوضع الليلي / النهاري"><svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg><svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg></button>',
            )
            ->colors([
                'primary' => Color::Blue,
            ])
                        ->renderHook(
                \Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@include(\'filament.components.screen-guide-modal\')'),
            )
            ->discoverResources(in: app_path('Filament/Supervisor/Resources'), for: 'App\Filament\Supervisor\Resources')
            ->discoverPages(in: app_path('Filament/Supervisor/Pages'), for: 'App\Filament\Supervisor\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Supervisor/Widgets'), for: 'App\Filament\Supervisor\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RedirectIfWrongPanel::class . ':supervisor',
            ])

            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckUserStatus::class,
            ])
            ->plugins([
                // FilamentShieldPlugin::make(),
                // FilamentBackgroundsPlugin::make(),

                // FilamentEditProfilePlugin::make()
                //     ->shouldRegisterNavigation(false),
                StickyTableHeaderPlugin::make(),
            ])
            ->authGuard('web')
            ->font('Tajawal');
    }

    public function boot(): void
    {
        // طھظپط¹ظٹظ„ ط§ظ„ظ€ custom theme ط§ظ„ط¬ظ…ظٹظ„
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '<link rel="stylesheet" href="' . asset('css/filament-admin-theme.css') . '">' . "\n" . '<link rel="stylesheet" href="' . asset('css/student-theme-scoped.css') . '">',
        );
    }
}



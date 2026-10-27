<?php

namespace App\Providers\Filament;

use App\Helpers\SettingsHelper;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Admin\Widgets\TenantSwitcher;
use App\Filament\Admin\Widgets\UserInfoWidget;
// use Jacobtims\FilamentLogger\FilamentLoggerPlugin;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Admin\Widgets\PatientsChartWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Admin\Widgets\RecentActivitiesWidget;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Admin\Widgets\ReservationsStatusChartWidget;
// use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;
use Filament\Support\Enums\Width;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->is_super_admin || is_null($user->tenant_id));

        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->maxContentWidth(Width::Full)
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->loginRouteSlug('login')
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->emailVerification()
            ->profile()
            ->registration()
            
            ->brandName(fn() => $this->getBrandName())
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
            ->darkMode(true)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Tajawal')
                        ->renderHook(
                \Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@include(\'filament.components.screen-guide-modal\')'),
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets(array_filter([
                \App\Filament\Admin\Widgets\StatsOverview::class,
                \App\Filament\Admin\Widgets\BarcodeAttendanceShortcut::class,
                \App\Filament\Admin\Widgets\RevenueChart::class,
                \App\Filament\Admin\Widgets\StudentGrowthChart::class,
                \App\Filament\Admin\Widgets\EnrollmentStatusChart::class,
                \App\Filament\Admin\Widgets\PaymentMethodsChart::class,
                \App\Filament\Admin\Widgets\TopCoursesChart::class,
                \App\Filament\Admin\Widgets\VaultDailyMovement::class,
                $isSuperAdmin ? \App\Filament\Admin\Widgets\TenantSwitcher::class : null,
                $isSuperAdmin ? \App\Filament\Admin\Widgets\UserInfoWidget::class : null,
            ]))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RedirectIfWrongPanel::class . ':admin',
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckUserStatus::class,
            ])

            ->plugins([
                // \App\Filament\Plugins\LocaleSwitcherPlugin::make()
                //     ->locales([
                //         'ar' => 'ط§ظ„ط¹ط±ط¨ظٹط©',
                //         'en' => 'English',
                //         'fr' => 'Franأ§ais',
                //     ])
                //     ->defaultLocale('ar'),
                FilamentShieldPlugin::make()
                    ->scopeToTenant(true)
                    ->tenantRelationshipName('organization')
                    ->tenantOwnershipRelationshipName('owner')
                    ->localizePermissionLabels(),

                // FilamentSpatieLaravelBackupPlugin::make(),
                // FilamentAuthenticationLogPlugin::make(),
                // FilamentEditProfilePlugin::make()
                //     ->shouldRegisterNavigation(false),

                // Lockscreen::make()
                //     ->usingCustomTableColumns('email', 'password')
                //     ->enableRateLimit()
                //     ->setUrl('lockscreen')
                //     ->enableIdleTimeout()
                //     ->disableDisplayName()
                //     ->enablePlugin(),

                // FilamentLogViewer::make()
                //     ->navigationGroup(__('filament.navigation_groups.system')),
                StickyTableHeaderPlugin::make(),
                // FilamentLoggerPlugin::make(),
            ])
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index');
    }

    /**
     * ط¬ظ„ط¨ ط§ط³ظ… ط§ظ„ظ€ brand ط­ط³ط¨ ط§ظ„ظ€ tenant ط§ظ„ط­ط§ظ„ظٹ
     */
    protected function getBrandName(): string
    {
        return SettingsHelper::getSiteTitle();
    }

    public function boot(): void
    {
        // طھظپط¹ظٹظ„ ط§ظ„ظ€ custom theme ط§ظ„ط¨ط³ظٹط·
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '<link rel="stylesheet" href="' . asset('css/filament-admin-theme.css') . '">' . "\n" . '<link rel="stylesheet" href="' . asset('css/student-theme-scoped.css') . '">'
        );
    }
}



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
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->is_super_admin || is_null($user->tenant_id));

        // ط§ط³طھط®ط¯ط§ظ… Lazy evaluation ظ„ط¬ظ„ط¨ ط§ظ„ط¨ظٹط§ظ†ط§طھ ط¹ظ†ط¯ ط§ظ„ط­ط§ط¬ط©
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->loginRouteSlug('login')
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->emailVerification()
            ->profile()
            ->registration()
            
            ->brandName(fn() => $this->getBrandName())
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => '<link rel="stylesheet" href="' . asset('css/custom-theme.css') . '?v=' . (file_exists(public_path('css/custom-theme.css')) ? filemtime(public_path('css/custom-theme.css')) : time()) . '">',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<script>
                    const initSmartSidebar = () => {
                        if (!window.Alpine || !window.Alpine.store("sidebar")) return;
                        const sidebarStore = window.Alpine.store("sidebar");

                        if (!sidebarStore._smartHoverApplied) {
                            sidebarStore._smartHoverApplied = true;

                            // 1. Accordion Mode (Only ONE group open at a time)
                            const getAllGroupLabels = () => {
                                return Array.from(document.querySelectorAll("[data-group-label]"))
                                    .map(el => el.getAttribute("data-group-label"))
                                    .filter(Boolean);
                            };

                            sidebarStore.toggleCollapsedGroup = function(group) {
                                const isCurrentlyCollapsed = this.collapsedGroups ? this.collapsedGroups.includes(group) : false;
                                const allGroups = getAllGroupLabels();

                                if (isCurrentlyCollapsed) {
                                    this.collapsedGroups = allGroups.filter(g => g !== group);
                                } else {
                                    if (!this.collapsedGroups) this.collapsedGroups = [];
                                    if (!this.collapsedGroups.includes(group)) {
                                        this.collapsedGroups = this.collapsedGroups.concat(group);
                                    }
                                }
                            };

                            // 2. Smart Hover & Pin System
                            let isPinned = localStorage.getItem("sidebar_pinned") === "true";

                            if (window.innerWidth >= 1024) {
                                if (isPinned) {
                                    sidebarStore.open();
                                } else {
                                    sidebarStore.close();
                                }
                            }

                            const attachSidebarEvents = () => {
                                const sidebarEl = document.getElementById("fi-main-sidebar") || document.querySelector(".fi-sidebar");
                                if (sidebarEl && !sidebarEl._smartEventsAttached) {
                                    sidebarEl._smartEventsAttached = true;
                                    let closeTimer = null;

                                    sidebarEl.addEventListener("mouseenter", () => {
                                        if (window.innerWidth >= 1024 && !isPinned) {
                                            clearTimeout(closeTimer);
                                            sidebarStore.open();
                                        }
                                    });

                                    sidebarEl.addEventListener("mouseleave", () => {
                                        if (window.innerWidth >= 1024 && !isPinned) {
                                            closeTimer = setTimeout(() => {
                                                if (!isPinned) {
                                                    sidebarStore.close();
                                                }
                                            }, 200);
                                        }
                                    });
                                }

                                const toggleBtns = document.querySelectorAll(".fi-topbar-open-collapse-sidebar-btn, .fi-topbar-close-collapse-sidebar-btn, .fi-sidebar-collapse-btn");
                                toggleBtns.forEach(btn => {
                                    if (!btn._pinHookAttached) {
                                        btn._pinHookAttached = true;
                                        btn.addEventListener("click", () => {
                                            isPinned = !isPinned;
                                            localStorage.setItem("sidebar_pinned", isPinned ? "true" : "false");
                                            if (isPinned) {
                                                sidebarStore.open();
                                            } else {
                                                sidebarStore.close();
                                            }
                                        });
                                    }
                                });
                            };

                            attachSidebarEvents();
                            document.addEventListener("livewire:navigated", attachSidebarEvents);
                        }
                    };

                    document.addEventListener("alpine:init", initSmartSidebar);
                    document.addEventListener("livewire:navigated", initSmartSidebar);
                    setTimeout(initSmartSidebar, 150);
                    setTimeout(initSmartSidebar, 500);
                </script>',
            )
            ->colors([
                'primary' => Color::Blue,
            ])
            ->darkMode(false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            // ->font('Cairo')
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
                // TEMPORARILY DISABLED
                // \App\Http\Middleware\CheckUserStatus::class,
                // ط§ظ„ظ€ middleware ظٹطھط­ظƒظ… ظپظٹ ط§ظ„ظˆطµظˆظ„ ط­ط³ط¨ tenant_id طھظ„ظ‚ط§ط¦ظٹط§ظ‹
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



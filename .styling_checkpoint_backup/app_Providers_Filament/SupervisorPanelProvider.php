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
use Filament\View\PanelsRenderHook;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path('supervisor')
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->profile()
            ->brandName('ظ„ظˆط­ط© ط§ظ„ظ…ط´ط±ظپ')
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
                'primary' => Color::Indigo,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                // FilamentShieldPlugin::make(),
                // FilamentBackgroundsPlugin::make(),

                // FilamentEditProfilePlugin::make()
                //     ->shouldRegisterNavigation(false),
                StickyTableHeaderPlugin::make(),
            ])
            ->authGuard('web')
            ->font('Cairo');
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



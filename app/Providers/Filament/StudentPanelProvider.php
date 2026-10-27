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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\Student;
use App\Models\Setting;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => '<script>(function(){function apply(){var t=localStorage.getItem("theme");if(!t){t="light";localStorage.setItem("theme",t);}if(t==="dark"){document.documentElement.classList.add("dark");}else{document.documentElement.classList.remove("dark");}}apply();document.addEventListener("livewire:navigated",apply);document.addEventListener("DOMContentLoaded",apply);})();</script>' . '<link rel="stylesheet" href="' . asset('css/custom-theme.css') . '?v=' . (file_exists(public_path('css/custom-theme.css')) ? filemtime(public_path('css/custom-theme.css')) : time()) . '">',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => '<button type="button" onclick="const t = localStorage.getItem(\'theme\') === \'dark\' ? \'light\' : \'dark\'; localStorage.setItem(\'theme\', t); if (t === \'dark\') { document.documentElement.classList.add(\'dark\'); } else { document.documentElement.classList.remove(\'dark\'); } window.dispatchEvent(new CustomEvent(\'theme-changed\', {detail: t}));" class="ai-theme-toggle-btn fi-icon-btn flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition mr-2" title="تبديل الوضع الليلي / النهاري" aria-label="تبديل الوضع الليلي / النهاري"><svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg><svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg></button>',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<script>
                    const initSmartSidebar = () => {
                        if (!window.Alpine || !window.Alpine.store("sidebar")) return;
                        const sidebarStore = window.Alpine.store("sidebar");

                        if (!sidebarStore._smartHoverApplied) {
                            sidebarStore._smartHoverApplied = true;

                            // 1. Independent Groups (Allow multiple groups open simultaneously)
                            // Native Filament independent toggle preserved

                            // 2. Sidebar open and expanded by default
                            let isPinned = localStorage.getItem("sidebar_pinned") !== "false";

                            if (window.innerWidth >= 1024) {
                                if (isPinned) {
                                    sidebarStore.open();
                                } else {
                                    sidebarStore.open();
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
            ->path('student')
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            
            ->colors([
                'primary' => Color::Blue,
            ])
            ->darkMode(true)
            ->font('Tajawal')
            ->spa()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
                        ->renderHook(
                \Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@include(\'filament.components.screen-guide-modal\')'),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@include(\'filament.student.components.mobile-navigation\')'),
            )
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\\Filament\\Student\\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\\Filament\\Student\\Pages')
            ->pages([
                \App\Filament\Student\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\\Filament\\Student\\Widgets')
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
                \App\Http\Middleware\RedirectIfWrongPanel::class . ':student',
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckUserStatus::class,
                \App\Http\Middleware\EnsureUserIsStudent::class,
                \App\Http\Middleware\CheckOverdueInstallments::class,
            ])
            ->authGuard('web')
            ->plugins([
                // FilamentShieldPlugin::make()
                //     ->scopeToTenant(true)
                //     ->tenantRelationshipName('organization')
                //     ->tenantOwnershipRelationshipName('owner'),
                // FilamentBackgroundsPlugin::make(),
                // 
                // FilamentEditProfilePlugin::make()
                //     ->shouldRegisterNavigation(false),
                StickyTableHeaderPlugin::make(),
            ]);
    }

    protected function getPlatformName(): string
    {
        $defaultName = '🎓 منصتي التعليمية';

        try {
            if (!Auth::check()) {
                return $defaultName;
            }

            $user = Auth::user();
            $student = Student::withoutGlobalScopes()->where('user_id', $user->id)->first();

            if ($student && $student->tenant_id) {
                $setting = Setting::withoutGlobalScopes()->where('tenant_id', $student->tenant_id)->first();
                if ($setting && $setting->company_name) {
                    return '🎓 ' . $setting->company_name;
                }
            }
        } catch (\Exception $e) {
            // Return default name if any error occurs
            return $defaultName;
        }

        return $defaultName;
    }

    public function boot(): void
    {
        // تفعيل الـ custom theme الجميل
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '<link rel="stylesheet" href="' . asset('css/filament-admin-theme.css') . '">',
        );
    }
}


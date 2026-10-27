<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register custom authentication provider to support login with email, phone, or username
        \Illuminate\Support\Facades\Auth::provider('custom_eloquent', function ($app, array $config) {
            return new \App\Providers\CustomUserProvider($app->make('hash'), $config['model']);
        });

        // Provide robust exception renderer fallback for debug mode
        $this->app->singleton(\Illuminate\Contracts\Foundation\ExceptionRenderer::class, function () {
            return new class implements \Illuminate\Contracts\Foundation\ExceptionRenderer {
                public function render($throwable)
                {
                    $debug = (bool) config('app.debug', false);
                    $renderer = new \Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer($debug);
                    return $renderer->render($throwable)->getAsString();
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant super_admin all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return ($user->is_super_admin || $user->hasRole('super_admin')) ? true : null;
        });

        // Independent Vault Permissions
        \Illuminate\Support\Facades\Gate::define('view_vault_balances', function ($user) {
            return $user->hasPermissionTo('view_vault_balances') || $user->hasPermissionTo('ViewBalances:Vault');
        });

        \Illuminate\Support\Facades\Gate::define('view_vault_transactions', function ($user) {
            return $user->hasPermissionTo('view_vault_transactions') || $user->hasPermissionTo('ViewTransactions:Vault');
        });

        // Register Observers
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\AttendanceSession::observe(\App\Observers\AttendanceSessionObserver::class);
        \App\Models\AttendanceRecord::observe(\App\Observers\AttendanceRecordObserver::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'fr'])
                ->visible(outsidePanels: true)

            ; // also accepts a closure
        });

        // Global Modal Stacking Context: Ensures Select dropdowns inside modals use fixed viewport positioning
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::BODY_END,
            fn(): string => '<script>
                (function() {
                    function applyFixedContext() {
                        document.querySelectorAll(".fi-modal-window, .fi-modal-content, [role=\"dialog\"]").forEach(function(el) {
                            if (!el.classList.contains("fi-fixed-positioning-context")) {
                                el.classList.add("fi-fixed-positioning-context");
                            }
                        });
                    }
                    window.addEventListener("open-modal", function() { setTimeout(applyFixedContext, 30); });
                    document.addEventListener("livewire:navigated", applyFixedContext);
                    document.addEventListener("DOMContentLoaded", applyFixedContext);
                    if (typeof MutationObserver !== "undefined") {
                        try {
                            var observer = new MutationObserver(function(mutations) {
                                for (var i = 0; i < mutations.length; i++) {
                                    if (mutations[i].addedNodes.length) {
                                        applyFixedContext();
                                        break;
                                    }
                                }
                            });
                            observer.observe(document.documentElement, { childList: true, subtree: true });
                        } catch(e) {}
                    }
                })();
            </script>'
        );
    }
}

@php
    $isRtl = true;
    $panelId = $navData['panel_id'] ?? 'admin';
    $dashboardItems = $navData['dashboard_items'] ?? [];
    $categories = $navData['categories'] ?? [];
    $homeUrl = filament()->getHomeUrl() ?? url('/' . $panelId);
    $tenantName = $tenant?->name ?? 'الفرع الرئيسي';
    $userName = $user?->name ?? 'الموظف';
    $userEmail = $user?->email ?? '';
    $userRoles = $user?->getRoleNames()->toArray() ?? [];
    $roleLabel = !empty($userRoles) ? ($userRoles[0] === 'super_admin' ? 'الإدارة العامة' : ($userRoles[0] === 'instructor' ? 'معلم / مدرب' : ($userRoles[0] === 'supervisor' ? 'مشرف تربوي' : ($userRoles[0] === 'accountant' ? 'الشؤون المالية' : ($userRoles[0] === 'secretary' ? 'السكرتارية' : $userRoles[0]))))) : 'موظف';
    $userInitials = mb_substr($userName, 0, 1, 'UTF-8');
@endphp

<div 
    x-data="{ 
        activeCategory: null, 
        mobileMenuOpen: false, 
        userMenuOpen: false,
        toggleCategory(key) {
            this.activeCategory = (this.activeCategory === key) ? null : key;
        },
        closeAll() {
            this.activeCategory = null;
            this.userMenuOpen = false;
        }
    }"
    @keydown.escape.window="closeAll(); mobileMenuOpen = false;"
    @click.outside="closeAll()"
    class="ai-employee-top-navbar-wrapper"
    dir="rtl"
>
    <!-- TOP NAVBAR ROW -->
    <header class="ai-employee-top-navbar">
        <div class="ai-navbar-inner">
            
            <!-- RIGHT: Brand Logo & Branch Info -->
            <div class="ai-navbar-brand-section">
                <!-- Mobile Menu Hamburger Button -->
                <button 
                    type="button" 
                    @click="mobileMenuOpen = !mobileMenuOpen" 
                    class="ai-navbar-mobile-toggle lg:hidden"
                    aria-label="القائمة الرئيسية"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ $homeUrl }}" class="ai-brand-link" title="الرئيسية">
                    <div class="ai-brand-badge">
                        <svg viewBox="0 0 192 192" class="ai-brand-svg" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="192" height="192" rx="44" fill="#2563EB"/>
                            <path d="M36 129V64h17l42 48V64h19v65H96L55 83v46z" fill="white"/>
                            <path d="M129 64h27v18h-27zm0 31h27v34h-27z" fill="#16A34A"/>
                        </svg>
                    </div>
                    <div class="ai-brand-text">
                        <span class="ai-brand-title">AI SCHOOL</span>
                        <span class="ai-branch-badge">{{ $tenantName }}</span>
                    </div>
                </a>
            </div>

            <!-- CENTER: Desktop Mega Navigation Links -->
            <nav class="ai-navbar-links-section hidden lg:flex" aria-label="التنقل الرئيسي">
                <!-- 1. Direct Dashboard Link -->
                @php
                    $isHomeActive = request()->is($panelId) || request()->is($panelId . '/') || request()->is($panelId . '/dashboard');
                @endphp
                <a 
                    href="{{ $homeUrl }}" 
                    class="ai-nav-tab {{ $isHomeActive ? 'is-active' : '' }}"
                    title="الرئيسية"
                >
                    <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>الرئيسية</span>
                    @if ($isHomeActive)
                        <span class="ai-nav-glow"></span>
                    @endif
                </a>

                <!-- 2. Mega Menu Category Buttons -->
                @foreach ($categories as $catKey => $cat)
                    <div class="ai-category-dropdown-wrapper">
                        <button 
                            type="button" 
                            @click="toggleCategory('{{ $catKey }}')"
                            :class="{ 'is-open': activeCategory === '{{ $catKey }}', 'is-active': {{ $cat['is_active'] ? 'true' : 'false' }} }"
                            class="ai-nav-tab ai-nav-dropdown-btn {{ $cat['is_active'] ? 'is-active' : '' }}"
                            aria-haspopup="true"
                            :aria-expanded="activeCategory === '{{ $catKey }}'"
                        >
                            @if ($catKey === 'students')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            @elseif ($catKey === 'academic' || $catKey === 'teaching')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            @elseif ($catKey === 'finance')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif ($catKey === 'reports' || $catKey === 'supervision_reports')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @elseif ($catKey === 'administration')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @elseif ($catKey === 'groups' || $catKey === 'supervision')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @elseif ($catKey === 'quality')
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            @else
                                <svg class="ai-nav-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            @endif

                            <span>{{ $cat['label'] }}</span>

                            <svg class="ai-arrow-icon" :class="{ 'rotate-180': activeCategory === '{{ $catKey }}' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>

                            @if ($cat['is_active'])
                                <span class="ai-nav-glow"></span>
                            @endif
                        </button>

                        <!-- MEGA DROPDOWN PANEL -->
                        <div 
                            x-show="activeCategory === '{{ $catKey }}'" 
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                            class="ai-mega-dropdown"
                            style="display: none;"
                        >
                            <div class="ai-mega-dropdown-header">
                                <div class="ai-mega-title-wrap">
                                    <h3 class="ai-mega-title">{{ $cat['label'] }}</h3>
                                    <span class="ai-mega-count">{{ $cat['total_items'] }} صفحة متاحة</span>
                                </div>
                                <button type="button" @click="activeCategory = null" class="ai-mega-close-btn" title="إغلاق">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="ai-mega-grid ai-cols-{{ min(count($cat['columns']), 4) }}">
                                @foreach ($cat['columns'] as $col)
                                    <div class="ai-mega-column">
                                        <div class="ai-col-header">
                                            <span class="ai-col-indicator"></span>
                                            <div>
                                                <h4 class="ai-col-title">{{ $col['group_label'] }}</h4>
                                                @if (!empty($col['sub_heading']) && $col['sub_heading'] !== $col['group_label'])
                                                    <p class="ai-col-caption">{{ $col['sub_heading'] }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <ul class="ai-col-list">
                                            @foreach ($col['items'] as $item)
                                                <li>
                                                    <a 
                                                        href="{{ $item['url'] }}" 
                                                        class="ai-mega-link {{ $item['is_active'] ? 'is-active' : '' }}"
                                                        @if ($item['target']) target="{{ $item['target'] }}" @endif
                                                        @click="activeCategory = null"
                                                    >
                                                        <span class="ai-link-icon-wrap">
                                                            @if (!empty($item['icon']))
                                                                <x-filament::icon :icon="$item['icon']" class="w-4 h-4 ai-item-svg" />
                                                            @else
                                                                <svg class="w-4 h-4 ai-item-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                            @endif
                                                        </span>
                                                        <span class="ai-link-label">{{ $item['label'] }}</span>
                                                        @if (!empty($item['badge']))
                                                            <span class="ai-link-badge">{{ $item['badge'] }}</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </nav>

            <!-- LEFT: Search, Notifications, Theme, User Profile -->
            <div class="ai-navbar-actions-section">
                <!-- 1. Global Search (if enabled) -->
                @if (filament()->isGlobalSearchEnabled() && filament()->getGlobalSearchPosition() === \Filament\Enums\GlobalSearchPosition::Topbar)
                    <div class="ai-global-search-container hidden md:block">
                        @livewire(Filament\Livewire\GlobalSearch::class)
                    </div>
                @endif

                <!-- 2. Database Notifications -->
                @if (filament()->auth()->check() && filament()->hasDatabaseNotifications() && filament()->getDatabaseNotificationsPosition() === \Filament\Enums\DatabaseNotificationsPosition::Topbar)
                    <div class="ai-notifications-wrap">
                        @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                        ])
                    </div>
                @endif

                <!-- 3. Theme Toggle Button -->
                <button 
                    type="button" 
                    onclick="const t = localStorage.getItem('theme') === 'dark' ? 'light' : 'dark'; localStorage.setItem('theme', t); if (t === 'dark') { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); } window.dispatchEvent(new CustomEvent('theme-changed', {detail: t}));" 
                    class="ai-theme-toggle-btn fi-icon-btn flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition" 
                    title="تبديل الوضع الليلي / النهاري" 
                    aria-label="تبديل الوضع الليلي / النهاري"
                >
                    <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <!-- 4. User Profile Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button 
                        type="button" 
                        @click="userMenuOpen = !userMenuOpen" 
                        class="ai-user-profile-trigger flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        aria-label="قائمة المستخدم"
                    >
                        <div class="ai-user-avatar">
                            <span>{{ $userInitials }}</span>
                        </div>
                        <div class="ai-user-meta hidden xl:block text-start">
                            <span class="ai-user-name block text-xs font-bold text-slate-800 dark:text-slate-200 truncate max-w-[120px]">{{ $userName }}</span>
                            <span class="ai-user-role block text-[10px] text-slate-500 dark:text-slate-400">{{ $roleLabel }}</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 hidden xl:block transition" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- User Menu Dropdown Panel -->
                    <div 
                        x-show="userMenuOpen" 
                        x-cloak
                        @click.outside="userMenuOpen = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="ai-user-dropdown-panel"
                    >
                        <div class="ai-user-dropdown-header">
                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $userName }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $userEmail }}</p>
                            <span class="ai-role-pill mt-1.5 inline-block">{{ $roleLabel }}</span>
                        </div>
                        <div class="ai-user-dropdown-body">
                            @if (filament()->hasProfile())
                                <a href="{{ filament()->getProfileUrl() }}" class="ai-user-menu-item">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>الملف الشخصي</span>
                                </a>
                            @endif

                            <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="w-full">
                                @csrf
                                <button type="submit" class="ai-user-menu-item text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 w-full text-start">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </header>

    <!-- MOBILE & TABLET NAVIGATION DRAWER -->
    <div 
        x-show="mobileMenuOpen" 
        x-cloak 
        class="ai-mobile-menu-overlay lg:hidden"
    >
        <!-- Backdrop -->
        <div 
            class="ai-mobile-backdrop"
            @click="mobileMenuOpen = false"
            x-show="mobileMenuOpen"
            x-transition:enter="transition-opacity ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        <!-- Slide-out Drawer -->
        <aside 
            class="ai-mobile-drawer"
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="ai-mobile-header">
                <div class="ai-mobile-brand">
                    <span class="ai-brand-title">AI SCHOOL</span>
                    <span class="ai-branch-badge">{{ $tenantName }}</span>
                </div>
                <button type="button" @click="mobileMenuOpen = false" class="ai-mobile-close" title="إغلاق">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="ai-mobile-body">
                <!-- Direct Dashboard Link -->
                <a 
                    href="{{ $homeUrl }}" 
                    class="ai-mobile-tab-link {{ $isHomeActive ? 'is-active' : '' }}"
                    @click="mobileMenuOpen = false"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>لوحة التحكم الرئيسية</span>
                </a>

                <!-- Accordion Categories -->
                @foreach ($categories as $catKey => $cat)
                    <div x-data="{ openCat: {{ $cat['is_active'] ? 'true' : 'false' }} }" class="ai-mobile-cat-group">
                        <button 
                            type="button" 
                            @click="openCat = !openCat" 
                            class="ai-mobile-cat-btn"
                            :class="{ 'is-active': openCat }"
                        >
                            <span>{{ $cat['label'] }}</span>
                            <span class="flex items-center gap-1.5">
                                <span class="ai-badge-sm">{{ $cat['total_items'] }}</span>
                                <svg class="w-4 h-4 transition" :class="{ 'rotate-180': openCat }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </button>

                        <div x-show="openCat" x-cloak class="ai-mobile-subgroup-list">
                            @foreach ($cat['columns'] as $col)
                                <div class="ai-mobile-subgroup">
                                    <h5 class="ai-mobile-subgroup-title">{{ $col['group_label'] }}</h5>
                                    <ul>
                                        @foreach ($col['items'] as $item)
                                            <li>
                                                <a 
                                                    href="{{ $item['url'] }}" 
                                                    class="ai-mobile-link {{ $item['is_active'] ? 'is-active' : '' }}"
                                                    @click="mobileMenuOpen = false"
                                                >
                                                    <span class="ai-link-label">{{ $item['label'] }}</span>
                                                    @if (!empty($item['badge']))
                                                        <span class="ai-link-badge">{{ $item['badge'] }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="ai-mobile-footer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $userName }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $roleLabel }}</p>
                    </div>
                    <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                        @csrf
                        <button type="submit" class="ai-mobile-logout-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>خروج</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>

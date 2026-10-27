# 📘 دليل منظومة الشريط الجانبي الذكي في Filament v3 / v4
## (Smart Collapsible Sidebar with Live Hover, Exclusive Accordion, & Pinning System)

هذا الدليل هو **مرجع تقني وهندسي جاهز للتطبيق الفوري (Plug-and-Play)** لنقل وتطبيق منظومة الشريط الجانبي الذكي التي تم ابتكارها وتطويرها على أي مشروع أو نظام Laravel Filament جديد.

---

## 🎯 الأهداف والميزات التقنية للمنظومة

1. **الوضع المصغر التلقائي (Mini Icon Sidebar by default)**:
   - يبدأ الشريط الجانبي في وضع الأيقونات المدمجة لإعطاء مساحة عمل قصوى 100% للشاشات والجداول.
2. **التوسيع المباشر الذكي بمجرد مرور الماوس (`Smart Live Hover`)**:
   - بمجرد تمرير الماوس فوق الشريط الجانبي، يتمدد تلقائياً ويفتح كشريط كامل وتظهر **عناوين المجموعات الرئيسية** (Group Headers).
   - لا تتبعثر الشاشات في قائمة طويلة مسطحة، بل تظهر المجموعات منظمة ومغلقة.
3. **نمط الأكورديون الحصري (`Exclusive Accordion Mode`)**:
   - عند النقر على أي مجموعة لفتحها، يقوم النظام **تلقائياً وفوراً بإغلاق أي مجموعة أخرى كانت مفتوحة**، لتبقى مجموعة واحدة فقط مفتوحة في كل مرة لمنع التمرير الطويل.
4. **الانكماش التلقائي عند مغادرة الماوس (`Auto-Collapse on Leave`)**:
   - بمجرد خروج مؤشر الماوس من الشريط الجانبي، ينكمش تلقائياً وبسلاسة مع مهلة أمان ذكية (Debounce 200ms).
5. **زر التثبيت الدائم (`Pin / Unpin Toggle Button`)**:
   - زر مخصص في الشريط العلوي (أو الشريط الجانبي) لتثبيت الشريط الجانبي في وضع الفتح الدائم (`Pinned Open`) أو فك تثبيته ليعمل بنمط الـ Hover، مع حفظ الحالة في المتصفح (`localStorage`).
6. **عزل وتعتيم الموبايل الكامل (`Mobile Off-Canvas Drawer Polish`)**:
   - في الشاشات الصغيرة والموبايل، يفتح الشريط كـ Drawer معتم بضبابية كاملة (`Backdrop Blur`) وإخفاء عناصر الشريط العلوي لمنع أي تداخل بصري.

---

## 🛠️ خطوات التطبيق على أي مشروع Filament جديد (4 خطوات فقط)

---

### 1️⃣ الخطوة الأولى: إعدادات `AdminPanelProvider.php`

في ملف مزود لوحة التحكم (`app/Providers/Filament/AdminPanelProvider.php`):

#### أ) تفعيل ميزة الطي للشاشات العريضة وإضافة ملف الـ CSS والـ Script الذكي:
```php
namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            
            // 1. تفعيل الشريط الجانبي القابل للطي
            ->sidebarCollapsibleOnDesktop()
            
            // 2. تحميل ملف التنسيقات المخصص
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => '<link rel="stylesheet" href="' . asset('css/custom-theme.css') . '?v=' . (file_exists(public_path('css/custom-theme.css')) ? filemtime(public_path('css/custom-theme.css')) : time()) . '">',
            )
            
            // 3. كود محرك الـ Alpine الذكي (Hover + Exclusive Accordion + Pinning)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<script>
                    const initSmartSidebar = () => {
                        if (!window.Alpine || !window.Alpine.store("sidebar")) return;
                        const sidebarStore = window.Alpine.store("sidebar");

                        if (!sidebarStore._smartHoverApplied) {
                            sidebarStore._smartHoverApplied = true;

                            // 1. نمط الأكورديون الحصري (فتح قائمة واحدة فقط وإغلاق الباقي آلياً)
                            const getAllGroupLabels = () => {
                                return Array.from(document.querySelectorAll("[data-group-label]"))
                                    .map(el => el.getAttribute("data-group-label"))
                                    .filter(Boolean);
                            };

                            sidebarStore.toggleCollapsedGroup = function(group) {
                                const isCurrentlyCollapsed = this.collapsedGroups ? this.collapsedGroups.includes(group) : false;
                                const allGroups = getAllGroupLabels();

                                if (isCurrentlyCollapsed) {
                                    // فتح هذه المجموعة فقط، وإغلاق كافة المجموعات الأخرى
                                    this.collapsedGroups = allGroups.filter(g => g !== group);
                                } else {
                                    // إغلاق هذه المجموعة أيضاً
                                    if (!this.collapsedGroups) this.collapsedGroups = [];
                                    if (!this.collapsedGroups.includes(group)) {
                                        this.collapsedGroups = this.collapsedGroups.concat(group);
                                    }
                                }
                            };

                            // 2. نظام التثبيت والـ Hover الذكي
                            let isPinned = localStorage.getItem("sidebar_pinned") === "true";

                            // بدء التشغيل مغلقاً للشاشات الكبيرة إذا لم يكن مثبتاً
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

                                    // فتح الشريط الجانبي عند مرور الماوس
                                    sidebarEl.addEventListener("mouseenter", () => {
                                        if (window.innerWidth >= 1024 && !isPinned) {
                                            clearTimeout(closeTimer);
                                            sidebarStore.open();
                                        }
                                    });

                                    // إغلاق الشريط الجانبي عند خروج الماوس مع مهلة 200ms
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

                                // ربط أزرار التبديل لتحديث حالة التثبيت (Pin/Unpin)
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
```

#### ب) تعريف المجموعات وجعلها مطوية افتراضياً (`collapsible()->collapsed()`):
```php
            // 4. تعريف المجموعات وجعلها مطوية وقابلة للطي
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('📞 المبيعات ومتابعة العملاء')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('💧 إدارة العملاء والفلاتر')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('🛠️ الميدان والتكييف')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('💳 الأقساط والخزينة')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('التقارير')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('🤖 الذكاء الاصطناعي والتحليلات')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('📦 إدارة المخازن والمشتريات')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('👥 الموارد البشرية')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('📈 تقارير المحاسبة والمالية')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('🤝 إدارة الشركاء')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('🔐 إدارة الصلاحيات والوصول')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('⚙️ الإعدادات العامة والنسخ الاحتياطي')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('⚙️ الإعدادات العامة')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
```

---

### 2️⃣ الخطوة الثانية: تنسيقات CSS للشريط الجانبي (`public/css/custom-theme.css`)

أضف هذه القواعد إلى ملف الـ CSS المخصص للنظام:

```css
/* ==========================================================================
   1. متغيرات وتنسيقات الشريط الجانبي المطور
   ========================================================================== */
:root {
    --sidebar-width: 19rem; /* مساحة كافية للعناوين العربية الطويلة دون اقتطاع */
}

.fi-sidebar {
    background: #ffffff !important;
    border-inline-end: 1px solid #e2e8f0 !important;
    box-shadow: 2px 0 12px rgba(15, 23, 42, 0.02) !important;
    transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s ease !important;
}

.dark .fi-sidebar {
    background: #0f172a !important;
    border-inline-end: 1px solid #1e293b !important;
}

/* تنسيق أزرار عناوين المجموعات (Accordion Headers) */
.fi-sidebar-group-button {
    border-radius: 10px !important;
    padding: 0.5rem 0.75rem !important;
    margin-inline: 0.35rem !important;
    transition: all 0.2s ease !important;
}

.fi-sidebar-group-button:hover {
    background-color: #f8fafc !important;
}

.dark .fi-sidebar-group-button:hover {
    background-color: #1e293b !important;
}

.fi-sidebar-group-label {
    font-weight: 700 !important;
    font-size: 0.825rem !important;
    color: #475569 !important;
    letter-spacing: 0.02em;
}

.dark .fi-sidebar-group-label {
    color: #94a3b8 !important;
}

/* تنسيق عناصر وروابط القوائم الفرعية */
.fi-sidebar-item > a,
.fi-sidebar-item > button {
    border-radius: 12px !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    margin-inline: 0.5rem !important;
}

.fi-sidebar-item > a:hover,
.fi-sidebar-item > button:hover {
    background-color: #f0f9ff !important;
    color: #0284c7 !important;
    transform: translateX(-2px);
}

.dark .fi-sidebar-item > a:hover,
.dark .fi-sidebar-item > button:hover {
    background-color: #1e293b !important;
    color: #38bdf8 !important;
}

/* العنصر النشط (Active Item) */
.fi-sidebar-item-active > a,
.fi-sidebar-item-active > button {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.28) !important;
}

.fi-sidebar-item-active > a svg,
.fi-sidebar-item-active > button svg,
.fi-sidebar-item-active .fi-sidebar-item-label {
    color: #ffffff !important;
}

/* أزرار التثبيت والتبديل (Pin/Collapse Toggle Buttons) */
.fi-topbar-open-collapse-sidebar-btn,
.fi-topbar-close-collapse-sidebar-btn {
    border-radius: 10px !important;
    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    transition: all 0.2s ease !important;
}

.fi-topbar-open-collapse-sidebar-btn:hover,
.fi-topbar-close-collapse-sidebar-btn:hover {
    background-color: #f0f9ff !important;
    border-color: #0284c7 !important;
    color: #0284c7 !important;
    transform: scale(1.05);
}

.dark .fi-topbar-open-collapse-sidebar-btn,
.dark .fi-topbar-close-collapse-sidebar-btn {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}

/* ==========================================================================
   2. تحسينات الموبايل والشاشات الصغيرة (Mobile Drawer Polish)
   ========================================================================== */
@media (max-width: 1023px) {
    /* أولوية الشريط العلوي في الموبايل */
    .fi-topbar-ctn,
    .fi-topbar {
        z-index: 30 !important;
    }

    /* رفع الشريط الجانبي في الموبايل ليعلو كل شيء */
    .fi-sidebar {
        z-index: 10000 !important;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.5) !important;
        width: 19rem !important;
        max-width: 88vw !important;
        background-color: #ffffff !important;
    }

    .dark .fi-sidebar {
        background-color: #0f172a !important;
    }

    /* طبقة التعتيم والضبابية الكاملة للشاشة */
    .fi-sidebar-close-overlay {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9990 !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(15, 23, 42, 0.7) !important;
    }

    .fi-sidebar-nav {
        padding-bottom: 4rem !important;
        padding-top: 1rem !important;
    }

    .fi-sidebar-header {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 0.75rem !important;
    }

    .dark .fi-sidebar-header {
        border-bottom: 1px solid #1e293b !important;
    }
}
```

---

### 3️⃣ الخطوة الثالثة: إخفاء عناصر الشريط العلوي عند فتح الموبايل

في ملف الـ Topbar (`resources/views/vendor/filament-panels/livewire/topbar.blade.php`):
أضف خاصية الـ Opacity التلقائية على عنصر `<nav class="fi-topbar">`:

```blade
<nav
    aria-label="{{ __('filament-panels::layout.topbar.label') }}"
    class="fi-topbar"
    x-data="{}"
    x-bind:class="$store.sidebar?.isOpen ? 'max-lg:opacity-0 max-lg:pointer-events-none transition-opacity duration-200' : 'transition-opacity duration-200'"
>
```

---

### 4️⃣ الخطوة الرابعة: مسح الكاش وتحديث البناء

شغل الأوامر التالية في سطر الأوامر:
```bash
php artisan view:clear
php artisan config:clear
php artisan optimize:clear
php artisan optimize
```

---

## 🏆 ملخص النتيجة الفورية:
1. يفتح النظام دائماً بشريط جانبي مصغر نظيف جداً ومريح للعين.
2. بمجرد مرور الماوس يتمدد الشريط بسلاسة وتظهر عناوين المجموعات (`المبيعات`، `العملاء`، `المخازن`، إلخ) مقفولة بأناقة.
3. عند النقر على أي مجموعة تفتح عناصرها وتُقفل أي مجموعة سابقة آلياً.
4. بمجرد إبعاد الماوس يرجع مصغراً تلقائياً.
5. يمكن تثبيته بضغطة زر ليظل مفتوحاً إذا فضل المستخدم ذلك!

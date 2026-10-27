<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div dir="rtl" class="classroom-availability-container space-y-6">
        <style>
            .ca-glass-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease-in-out;
            }
            .ca-glass-card:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
            }
            .ca-pill-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.5rem 0.95rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 700;
                transition: all 0.15s ease;
                cursor: pointer;
                border: 1px solid transparent;
            }
            .ca-pill-btn.active {
                background: #364FAC;
                color: #ffffff;
                border-color: #2b3e8a;
                box-shadow: 0 2px 4px rgba(54, 79, 172, 0.3);
            }
            .ca-pill-btn:not(.active) {
                background: #f1f5f9;
                color: #475569;
                border-color: #e2e8f0;
            }
            .ca-pill-btn:not(.active):hover {
                background: #e2e8f0;
                color: #1e293b;
            }
            .ca-timeline-bar {
                height: 14px;
                border-radius: 9999px;
                overflow: hidden;
                background: #f1f5f9;
                display: flex;
                width: 100%;
                border: 1px solid #cbd5e1;
            }
            .ca-slot-avail {
                background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                transition: opacity 0.2s;
            }
            .ca-slot-avail:hover {
                opacity: 0.85;
            }
            .ca-slot-occ {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                transition: opacity 0.2s;
            }
            .ca-slot-occ:hover {
                opacity: 0.85;
            }
            .ca-slot-unavail {
                background: #94a3b8;
            }
            .ca-badge-avail {
                background: #dcfce7;
                color: #15803d;
                border: 1px solid #bbf7d0;
            }
            .ca-badge-occ {
                background: #ffedd5;
                color: #c2410c;
                border: 1px solid #fed7aa;
            }
            .ca-badge-unavail {
                background: #f1f5f9;
                color: #64748b;
                border: 1px solid #cbd5e1;
            }
            .ca-badge-conflict {
                background: #fee2e2;
                color: #b91c1c;
                border: 1px solid #fca5a5;
            }
            .ca-legend-dot {
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                display: inline-block;
            }
            .ca-table-cell-day {
                min-width: 130px;
                vertical-align: top;
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
        </style>

        
        <div class="ca-glass-card p-5 space-y-4">
            
            <div class="flex flex-wrap items-center justify-between gap-4">
                
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="changeDate(1)" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                        title="اليوم التالي"
                    >
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </button>

                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 shadow-sm">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-calendar-days'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-indigo-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <input 
                            type="date" 
                            wire:model.live="selectedDate" 
                            class="bg-transparent border-none p-0 text-sm font-bold text-gray-800 focus:ring-0 cursor-pointer"
                        />
                    </div>

                    <button 
                        type="button" 
                        wire:click="changeDate(-1)" 
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                        title="اليوم السابق"
                    >
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-chevron-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </button>

                    <button 
                        type="button" 
                        wire:click="changeDate(0)" 
                        wire:click="$set('selectedDate', '<?php echo e(now()->format('Y-m-d')); ?>')"
                        class="text-xs font-bold px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition"
                    >
                        اليوم الحالي
                    </button>
                </div>

                
                <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-inner">
                    <button 
                        type="button" 
                        wire:click="setViewMode('daily')" 
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition <?php echo e($viewMode === 'daily' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'); ?>"
                    >
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <span>العرض اليومي المفصل</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="setViewMode('weekly')" 
                        class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition <?php echo e($viewMode === 'weekly' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'); ?>"
                    >
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-table-cells'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <span>الجدول الأسبوعي الشامل</span>
                    </button>
                </div>
            </div>

            
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-500 ml-2">أيام الأسبوع:</span>
                <?php
                    $currentDayOfWeek = strtolower(\Carbon\Carbon::parse($selectedDate)->format('l'));
                    $days = [
                        'saturday' => 'السبت',
                        'sunday' => 'الأحد',
                        'monday' => 'الاثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                    ];
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button 
                        type="button" 
                        wire:click="setDayOfWeek('<?php echo e($key); ?>')" 
                        class="ca-pill-btn <?php echo e($currentDayOfWeek === $key ? 'active' : ''); ?>"
                    >
                        <?php echo e($name); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-3 border-t border-gray-100">
                
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">بحث عن قاعة</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="searchQuery" 
                            placeholder="ابحث باسم القاعة أو موقعها..." 
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 pr-8 pl-3"
                        />
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-magnifying-glass'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-gray-400 absolute right-2.5 top-2.5 pointer-events-none']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </div>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تحديد قاعة معينة</label>
                    <select 
                        wire:model.live="selectedClassroomId" 
                        class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                    >
                        <option value="">جميع القاعات المتاحة</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classroomsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تصفية حسب حالة التوفر</label>
                    <select 
                        wire:model.live="filterStatus" 
                        class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                    >
                        <option value="all">كل الحالات</option>
                        <option value="with_available">قاعات بها أوقات متاحة</option>
                        <option value="fully_available">قاعات شاغرة بالكامل (100% متاحة)</option>
                        <option value="has_bookings">قاعات بها مجموعات اليوم</option>
                    </select>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_super_admin && count($this->tenantsList) > 0): ?>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">الفرع / المدرسة</label>
                        <select 
                            wire:model.live="selectedTenantId" 
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                        >
                            <option value="">جميع الفروع</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->tenantsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tId => $tName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tId); ?>"><?php echo e($tName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="flex items-end">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($searchQuery || $selectedClassroomId || $filterStatus !== 'all'): ?>
                            <button 
                                type="button" 
                                wire:click="resetFilters" 
                                class="text-xs font-bold text-red-600 hover:text-red-700 p-2 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 w-full text-center"
                            >
                                ✕ إعادة ضبط التصفية
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="flex flex-wrap items-center justify-between gap-3 px-2 text-xs font-medium text-gray-600">
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-bold text-gray-700">دليل الألوان:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-green-500"></span>
                    <strong class="text-green-700 font-bold">متاح للحجز (شاغر)</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-orange-500"></span>
                    <strong class="text-orange-700 font-bold">مشغول بمجموعة دراسية</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-gray-400"></span>
                    <strong class="text-gray-600 font-bold">صيانة / غير متاح</strong>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="ca-legend-dot bg-red-500"></span>
                    <strong class="text-red-700 font-bold">تنبيه تعارض مواعيد</strong>
                </span>
            </div>

            <div class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded-full text-xs font-bold">
                ⏰ ساعات العمل التشغيلية للقاعات: 09:00 ص – 09:00 م (12 ساعة يومياً)
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'daily'): ?>
            <?php
                $classroomsData = $this->dailyAvailability;
                $totalClassrooms = count($classroomsData);
                $fullyAvailableCount = count(array_filter($classroomsData, fn($c) => $c['is_active'] && empty($c['occupied_slots'])));
                $occupiedCount = count(array_filter($classroomsData, fn($c) => !empty($c['occupied_slots'])));
                $totalGroupsToday = array_sum(array_map(fn($c) => count($c['occupied_slots']), $classroomsData));
            ?>

            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="ca-glass-card p-4 border-l-4 border-l-indigo-600 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">إجمالي القاعات المعروضة</div>
                        <div class="text-2xl font-black text-gray-900 mt-0.5"><?php echo e($totalClassrooms); ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        🏛️
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-green-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">قاعات شاغرة طوال اليوم</div>
                        <div class="text-2xl font-black text-green-600 mt-0.5"><?php echo e($fullyAvailableCount); ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center font-bold">
                        🟢
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-orange-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">قاعات بها مجموعات اليوم</div>
                        <div class="text-2xl font-black text-orange-600 mt-0.5"><?php echo e($occupiedCount); ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                        🟠
                    </div>
                </div>

                <div class="ca-glass-card p-4 border-l-4 border-l-blue-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-bold">إجمالي الحصص المجدولة اليوم</div>
                        <div class="text-2xl font-black text-blue-600 mt-0.5"><?php echo e($totalGroupsToday); ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        📚
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $classroomsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="ca-glass-card p-5 flex flex-col justify-between space-y-4">
                        
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-gray-900"><?php echo e($item['classroom_name']); ?></h3>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_active']): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                نشطة
                                            </span>
                                        <?php elseif($item['status'] === 'maintenance'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                تحت الصيانة
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                                غير نشطة
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['stats']['has_conflicts'] ?? false): ?>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300 animate-pulse">
                                                ⚠️ تعارض مواعيد
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['location'])): ?>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-map-pin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3.5 h-3.5 text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                            <span><?php echo e($item['location']); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="text-left bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700">
                                    <span>👥 السعة القصوى:</span>
                                    <span class="text-indigo-700 font-black text-sm mr-1"><?php echo e($item['capacity']); ?> طالب</span>
                                </div>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_active']): ?>
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-gray-500 mb-1">
                                        <span>09:00 ص</span>
                                        <span class="text-gray-400">مخطط إشغال اليوم الزمني</span>
                                        <span>09:00 م</span>
                                    </div>
                                    <div class="ca-timeline-bar" title="مخطط توفر القاعة على مدار اليوم">
                                        <?php
                                            $totalWorkMins = 12 * 60; // 720 minutes
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $blockMins = \Carbon\Carbon::parse($block['end_time'])->diffInMinutes(\Carbon\Carbon::parse($block['start_time']));
                                                $pct = round(($blockMins / $totalWorkMins) * 100, 1);
                                                $cls = match($block['status']) {
                                                    'available' => 'ca-slot-avail',
                                                    'occupied' => 'ca-slot-occ',
                                                    default => 'ca-slot-unavail',
                                                };
                                            ?>
                                            <div 
                                                class="<?php echo e($cls); ?>" 
                                                style="width: <?php echo e($pct); ?>%;" 
                                                title="<?php echo e($block['start_time']); ?> - <?php echo e($block['end_time']); ?> (<?php echo e($block['label']); ?>)"
                                            ></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-1 font-medium">
                                        <span>مشغول: <strong class="text-orange-600 font-bold"><?php echo e($item['stats']['occupied_hours']); ?> ساعة</strong> (<?php echo e($item['stats']['occupied_percentage']); ?>%)</span>
                                        <span>متاح: <strong class="text-green-600 font-bold"><?php echo e($item['stats']['available_hours']); ?> ساعة</strong></span>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <div class="space-y-2.5 pt-3 border-t border-gray-100 flex-1">
                            <h4 class="text-xs font-black text-gray-700 uppercase tracking-wider mb-2">الفترات الزمنية المتاحة والمشغولة:</h4>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$item['is_active']): ?>
                                <div class="ca-badge-unavail p-3 rounded-xl text-center text-xs font-bold">
                                    <?php echo e($item['status_label']); ?> — لا يمكن حجز أو جدولة مجموعات في هذه القاعة حالياً.
                                </div>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['status'] === 'available'): ?>
                                            
                                            <div class="ca-badge-avail p-3 rounded-xl flex items-center justify-between text-xs transition hover:bg-green-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>
                                                    <strong class="font-black text-green-900 text-sm">متاح للحجز: <?php echo e($slot['start_time']); ?> – <?php echo e($slot['end_time']); ?></strong>
                                                    <span class="text-green-700">(<?php echo e($slot['duration_formatted']); ?>)</span>
                                                </div>
                                                <div class="text-[11px] text-green-800 font-bold bg-green-200/60 px-2 py-0.5 rounded-md">
                                                    جاهزة لاستقبال مجموعة جديدة
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            
                                            <div class="<?php echo e(($slot['has_conflict'] ?? false) ? 'ca-badge-conflict' : 'ca-badge-occ'); ?> p-3 rounded-xl space-y-1.5 transition">
                                                <div class="flex items-center justify-between text-xs">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>
                                                        <strong class="font-black text-orange-950 text-sm"><?php echo e($slot['start_time']); ?> – <?php echo e($slot['end_time']); ?></strong>
                                                        <span class="text-orange-800">(<?php echo e($slot['duration_formatted']); ?>)</span>
                                                    </div>

                                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-white/70 text-orange-900 border border-orange-200 shadow-2xs">
                                                        <?php echo e($slot['source'] === 'session_schedule' ? 'حصّة مؤكدة' : 'جدول أسبوعي'); ?>

                                                    </span>
                                                </div>

                                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-xs border-t border-orange-200/50">
                                                    <div>
                                                        <span class="font-black text-gray-900 text-sm"><?php echo e($slot['label']); ?></span>
                                                        <span class="text-gray-500 mr-1">(<?php echo e($slot['course']); ?>)</span>
                                                    </div>

                                                    <div class="flex items-center gap-3 text-xs font-medium text-gray-600">
                                                        <span>👨‍🏫 <?php echo e($slot['instructor']); ?></span>
                                                        <span class="font-bold text-gray-800 bg-white/90 px-2 py-0.5 rounded border border-gray-200">
                                                            الطلاب: <?php echo e($slot['students_count']); ?> / <?php echo e($slot['capacity']); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['available_seats'] > 0): ?>
                                                                <small class="text-green-600 font-bold">(<?php echo e($slot['available_seats']); ?> مقعد شاغر)</small>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($slot['has_conflict'] ?? false): ?>
                                                    <div class="text-[11px] font-bold text-red-700 bg-red-100 p-1.5 rounded mt-1">
                                                        ⚠️ تنبيه: يوجد تداخل زمني مع مجموعة أخرى في نفس القاعة بنفس التوقيت!
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_active']): ?>
                            <div class="pt-3 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-between">
                                <span>💡 سعة القاعة تتيح إضافة مجموعة جديدة حتى <strong><?php echo e($item['capacity']); ?> طالب</strong> في الأوقات الخضراء.</span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-2 ca-glass-card p-12 text-center text-gray-500 space-y-3">
                        <div class="text-4xl">🏛️</div>
                        <h3 class="text-base font-bold text-gray-800">لا توجد قاعات تطابق معايير التصفية</h3>
                        <p class="text-xs text-gray-500">يرجى تعديل معايير البحث أو اختيار فرع/قاعة مختلفة من الشريط العلوي.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        
        <?php else: ?>
            <?php
                $weeklyData = $this->weeklyAvailability;
            ?>

            <div class="ca-glass-card overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-calendar-days'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-indigo-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                        <h3 class="text-sm font-black text-gray-900">
                            الجدول الأسبوعي الشامل للقاعات: من <?php echo e(\Carbon\Carbon::parse($weeklyData['week_start'])->format('Y/m/d')); ?> إلى <?php echo e(\Carbon\Carbon::parse($weeklyData['week_end'])->format('Y/m/d')); ?>

                        </h3>
                    </div>
                    <div class="text-xs text-gray-500 font-bold">
                        اضغط على أي يوم للانتقال مباشرة لعرض تفاصيل اليوم الكاملة
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 text-xs font-black border-b border-gray-200">
                                <th class="p-3 w-44 sticky right-0 bg-gray-100 z-10 border-l border-gray-200">القاعة</th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $weeklyData['days_header']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hDay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="p-3 text-center border-l border-gray-200 min-w-[140px] cursor-pointer hover:bg-gray-200 transition" wire:click="selectDate('<?php echo e($hDay['date']); ?>')">
                                        <div class="text-indigo-800"><?php echo e($hDay['day_name_ar']); ?></div>
                                        <div class="text-[11px] font-normal text-gray-500"><?php echo e($hDay['formatted']); ?></div>
                                    </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $weeklyData['classrooms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wClassroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/70 transition">
                                    
                                    <td class="p-3 font-bold text-gray-900 sticky right-0 bg-white border-l border-gray-200 shadow-2xs">
                                        <div class="text-sm font-black"><?php echo e($wClassroom['classroom_name']); ?></div>
                                        <div class="text-[11px] text-gray-500 font-normal">سعة: <?php echo e($wClassroom['capacity']); ?> طالب</div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$wClassroom['is_active']): ?>
                                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] bg-gray-200 text-gray-700">غير نشطة</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $weeklyData['days_header']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hDay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $dayData = $wClassroom['days'][$hDay['day_of_week']] ?? null;
                                        ?>
                                        <td class="ca-table-cell-day border-l border-gray-100">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$wClassroom['is_active']): ?>
                                                <div class="p-1.5 rounded bg-gray-100 text-gray-400 text-center text-[10px] font-bold">
                                                    مغلقة
                                                </div>
                                            <?php elseif(empty($dayData['occupied_slots'])): ?>
                                                <div 
                                                    wire:click="selectDate('<?php echo e($hDay['date']); ?>')" 
                                                    class="p-2 rounded-lg bg-green-50 border border-green-200 text-green-700 text-center font-bold cursor-pointer hover:bg-green-100 transition"
                                                    title="القاعة شاغرة طوال هذا اليوم - اضغط للتفاصيل"
                                                >
                                                    <span class="text-green-600 block text-xs">🟢 شاغرة بالكامل</span>
                                                    <span class="text-[10px] text-green-800 font-normal">09:00 ص – 09:00 م</span>
                                                </div>
                                            <?php else: ?>
                                                <div 
                                                    wire:click="selectDate('<?php echo e($hDay['date']); ?>')"
                                                    class="space-y-1 cursor-pointer"
                                                    title="اضغط لعرض تفاصيل هذا اليوم"
                                                >
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayData['occupied_slots']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $occ): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="p-1.5 rounded bg-orange-100 border border-orange-200 text-orange-950 font-bold hover:bg-orange-200 transition">
                                                            <div class="text-[11px] font-black text-orange-900"><?php echo e($occ['start_time']); ?> – <?php echo e($occ['end_time']); ?></div>
                                                            <div class="truncate text-[10px] font-medium text-gray-800"><?php echo e($occ['group_name']); ?></div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <div class="text-[10px] text-green-700 font-bold text-center pt-0.5">
                                                        ✓ متاح <?php echo e($dayData['stats']['available_hours']); ?> ساعة
                                                    </div>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-500 font-bold">
                                        لا توجد بيانات قاعات متاحة
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/resources/classrooms/pages/classroom-availability.blade.php ENDPATH**/ ?>
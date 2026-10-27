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
    <div class="space-y-6" dir="rtl">

        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-presentation-chart-line'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 inline']); ?>
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
                    </span>
                    مركز التقارير والاستخبارات التشغيلية والمالية
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    منصة الرقابة المركزية المعتمدة لمدارس الذكاء الاصطناعي — بيانات موثقة ومطابقة محاسبياً وأكاديمياً
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                
                <button type="button" wire:click="$set('showSaveReportModal', true)" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-bookmark'); ?>
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
                    حفظ الفلترة الحالية
                </button>

                
                <div class="inline-flex rounded-xl shadow-sm">
                    <button type="button" wire:click="exportReport('xlsx')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-r-xl border border-emerald-200 dark:border-emerald-800 transition">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-table-cells'); ?>
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
                        Excel
                    </button>
                    <button type="button" wire:click="exportReport('pdf')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border-t border-b border-rose-200 dark:border-rose-800 transition">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-document-text'); ?>
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
                        PDF
                    </button>
                    <button type="button" wire:click="exportReport('csv')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 rounded-l-xl border border-sky-200 dark:border-sky-800 transition">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-down-tray'); ?>
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
                        CSV
                    </button>
                </div>

                
                <button type="button" wire:click="loadCurrentReport" class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="تحديث البيانات">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-path'); ?>
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
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-funnel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-primary-500']); ?>
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
                    محددات الفلترة الزمنية والتشغيلية الموحدة
                </span>
                <button type="button" wire:click="resetFilters" class="text-xs text-gray-500 hover:text-rose-500 transition">
                    إعادة ضبط الفلاتر
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الفترة الزمنية</label>
                    <select wire:model.live="periodPreset" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="today">اليوم</option>
                        <option value="yesterday">أمس</option>
                        <option value="this_week">هذا الأسبوع</option>
                        <option value="last_week">الأسبوع الماضي</option>
                        <option value="this_month">هذا الشهر</option>
                        <option value="last_month">الشهر الماضي</option>
                        <option value="this_quarter">هذا الربع</option>
                        <option value="this_year">هذا العام</option>
                        <option value="custom">فترة مخصصة</option>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">من تاريخ</label>
                    <input type="date" wire:model="dateFrom" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">إلى تاريخ</label>
                    <input type="date" wire:model="dateTo" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ الأرصدة (As-Of-Date)</label>
                    <input type="date" wire:model="asOfDate" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الفرع</label>
                    <select wire:model="selectedBranchId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_super_admin || auth()->user()->hasRole('super_admin') || is_null(auth()->user()->tenant_id)): ?>
                            <option value="">كافة الفروع المتاحة</option>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableBranches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الكورس / المادة</label>
                    <select wire:model="selectedCourseId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة الكورسات</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>"><?php echo e($course->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">المجموعة الدراسية</label>
                    <select wire:model="selectedGroupId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة المجموعات</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($grp->id); ?>"><?php echo e($grp->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الخزينة</label>
                    <select wire:model="selectedVaultId" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500">
                        <option value="">كافة الخزائن</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableVaults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vault): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($vault->id); ?>"><?php echo e($vault->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" wire:click="applyFilters" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-black text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow transition">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-magnifying-glass'); ?>
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
                    تطبيق الفلترة والبحث
                </button>
            </div>
        </div>

        
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-1 space-x-reverse overflow-x-auto pb-2 scrollbar-none" aria-label="Tabs">
                <?php
                    $tabs = [
                        'overview' => ['name' => 'النظرة التنفيذية', 'icon' => 'heroicon-o-home'],
                        'growth' => ['name' => 'نمو الطلاب', 'icon' => 'heroicon-o-user-plus'],
                        'enrollments' => ['name' => 'الاشتراكات والتجديد', 'icon' => 'heroicon-o-document-check'],
                        'collections' => ['name' => 'التحصيلات النقدية', 'icon' => 'heroicon-o-banknotes'],
                        'installments' => ['name' => 'الأقساط وإعمار الديون', 'icon' => 'heroicon-o-clock'],
                        'vaults' => ['name' => 'الخزائن وحركة النقد', 'icon' => 'heroicon-o-wallet'],
                        'pnl' => ['name' => 'الإيرادات وقائمة الدخل', 'icon' => 'heroicon-o-calculator'],
                        'salaries' => ['name' => 'مرتبات المهندسين', 'icon' => 'heroicon-o-identification'],
                        'cancellations' => ['name' => 'الإلغاءات والتسويات', 'icon' => 'heroicon-o-arrow-uturn-left'],
                        'academic' => ['name' => 'التحليل الأكاديمي', 'icon' => 'heroicon-o-academic-cap'],
                        'supervision' => ['name' => 'الإشراف التربوي', 'icon' => 'heroicon-o-eye'],
                        'employees' => ['name' => 'عمليات الموظفين', 'icon' => 'heroicon-o-briefcase'],
                        'branches' => ['name' => 'مقارنة الفروع', 'icon' => 'heroicon-o-building-office-2'],
                        'alerts' => ['name' => 'التنبيهات الإدارية', 'icon' => 'heroicon-o-bell-alert'],
                        'saved' => ['name' => 'التقارير المحفوظة', 'icon' => 'heroicon-o-bookmark'],
                        'settings' => ['name' => 'إعدادات التقارير', 'icon' => 'heroicon-o-cog-6-tooth'],
                    ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" wire:click="setSection('<?php echo e($key); ?>')" class="whitespace-nowrap px-3.5 py-2 text-xs font-bold rounded-xl flex items-center gap-1.5 transition <?php echo e($activeSection === $key ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'); ?>">
                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $tab['icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 inline']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
                        <?php echo e($tab['name']); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>
        </div>

        

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'overview'): ?>
            <div class="space-y-6">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <div wire:click="openDrillDown('new_students')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-primary-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>الطلاب الجدد المسجلين</span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-user-plus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-primary-500 group-hover:scale-110 transition']); ?>
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
                        <div class="text-3xl font-black text-gray-900 dark:text-white">
                            <?php echo e($reportData['new_students_count'] ?? 0); ?>

                        </div>
                        <div class="mt-2 text-[10px] text-gray-400 flex items-center gap-1">
                            <span>طالب فريد لأول مرة بالفترة</span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3 h-3 text-primary-500 inline']); ?>
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

                    
                    <div wire:click="openDrillDown('enrollments_renewals')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-emerald-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>الاشتراكات والتجديدات (إجمالي موحد)</span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-document-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-emerald-500 group-hover:scale-110 transition']); ?>
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
                        <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                            <?php echo e($reportData['total_enrollments_count'] ?? 0); ?>

                        </div>
                        <div class="mt-2 text-[10px] text-emerald-600/80 font-medium">
                            قيمة تعاقدية: <?php echo e(number_format($reportData['contracted_enrollment_value'] ?? 0, 2)); ?> ج.م
                        </div>
                    </div>

                    
                    <div wire:click="openDrillDown('actual_collections')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-sky-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>التحصيلات النقدية الفعلية</span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-banknotes'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-sky-500 group-hover:scale-110 transition']); ?>
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
                        <div class="text-3xl font-black text-sky-600 dark:text-sky-400">
                            <?php echo e(number_format($reportData['actual_collections'] ?? 0, 2)); ?> <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <div class="mt-2 text-[10px] text-sky-600/80 font-medium">
                            نقدية محصلة ومودعة بالخزائن
                        </div>
                    </div>

                    
                    <div wire:click="openDrillDown('overdue_debt')" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-rose-500 transition group">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span>إجمالي المديونيات المستحقة</span>
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-rose-500 group-hover:scale-110 transition']); ?>
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
                        <div class="text-3xl font-black text-rose-600 dark:text-rose-400">
                            <?php echo e(number_format($reportData['total_outstanding_debt'] ?? 0, 2)); ?> <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <div class="mt-2 text-[10px] text-rose-600/80 font-medium flex items-center justify-between">
                            <span>متأخرات: <?php echo e(number_format($reportData['overdue_debt'] ?? 0, 2)); ?> ج.م</span>
                            <span class="text-amber-500">مستحق اليوم: <?php echo e(number_format($reportData['currently_due_debt'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                    </div>
                </div>

                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-wallet'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-primary-500']); ?>
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
                            حركة السيولة والتدفق النقدي الفعلي بالخزائن
                        </h2>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-800">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">المقبوضات النقدية</span>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                    <?php echo e(number_format($reportData['actual_cash_inflows'] ?? 0, 2)); ?>

                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-800">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">المدفوعات والمصروفات</span>
                                <span class="text-base font-black text-rose-600 dark:text-rose-400">
                                    <?php echo e(number_format($reportData['actual_cash_disbursements'] ?? 0, 2)); ?>

                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">صافي الحركة النقدية</span>
                                <span class="text-base font-black <?php echo e(($reportData['net_cash_movement'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>">
                                    <?php echo e(number_format($reportData['net_cash_movement'] ?? 0, 2)); ?>

                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-200">
                            <strong>قاعدة محاسبية معتمدة:</strong> التحويلات الداخلية بين الخزائن لا تولد إيراداً أو مصروفاً للمدرسة وتُعامل كحركة مقاصة نقدية فقط.
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-academic-cap'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 text-primary-500']); ?>
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
                            مؤشرات الانضباط والامتثال الأكاديمي
                        </h2>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-1">الجلسات المكتملة</span>
                                <span class="text-base font-black text-gray-800 dark:text-gray-200">
                                    <?php echo e($reportData['completed_sessions_count'] ?? 0); ?>

                                </span>
                            </div>
                            <div wire:click="openDrillDown('unrecorded_sessions')" class="cursor-pointer p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 hover:border-amber-500 transition">
                                <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">جلسات بدون رصد حضور</span>
                                <span class="text-base font-black text-amber-600 dark:text-amber-400">
                                    <?php echo e($reportData['unrecorded_attendance_sessions_count'] ?? 0); ?>

                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-950/30 border border-primary-200 dark:border-primary-800">
                                <span class="text-[11px] text-primary-700 dark:text-primary-300 block mb-1">نسبة انضباط التسجيل</span>
                                <span class="text-base font-black text-primary-600 dark:text-primary-400">
                                    <?php echo e($reportData['attendance_compliance_rate'] ?? 0); ?>%
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200">
                            <strong>قاعدة أكاديمية صارمة:</strong> عدم رصد الحضور يُعد تأخيراً إدارياً ولا يُحسب إطلاقاً كغياب للطلاب.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'growth'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير تسجيل الطلاب الجدد</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">يُحسب الطالب الجديد ككيان فريد مرة واحدة في المدرسة مهما تعددت كورساته</p>
                    </div>
                    <div class="text-sm font-bold bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-xl">
                        إجمالي الطلاب الجدد: <?php echo e($reportData['total_unique_students'] ?? 0); ?>

                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">#</th>
                                <th class="p-3">كود الطالب</th>
                                <th class="p-3">اسم الطالب</th>
                                <th class="p-3">رقم الهاتف</th>
                                <th class="p-3">الفرع</th>
                                <th class="p-3">تاريخ ووقت التسجيل</th>
                                <th class="p-3">عدد الاشتراكات</th>
                                <th class="p-3">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono text-gray-400"><?php echo e($st['id']); ?></td>
                                    <td class="p-3 font-mono font-bold"><?php echo e($st['student_code']); ?></td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($st['name']); ?></td>
                                    <td class="p-3 font-mono text-gray-500"><?php echo e($st['phone']); ?></td>
                                    <td class="p-3"><?php echo e($st['branch_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($st['registration_date']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e($st['enrollments_count']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($st['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo e($st['status']); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد تسجيلات جديدة مطابقة لمعايير الفلترة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'enrollments'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-100 dark:border-gray-700 pb-3 gap-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الاشتراكات والتجديدات الموحد</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">الاشتراكات الجديدة والتجديدات مجتمعة في إجمالي رئيسي واحد مع تفصيل العمليات</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 px-3 py-1.5 rounded-xl">
                            إجمالي الحركات: <?php echo e($reportData['total_enrollment_and_renewal_events'] ?? 0); ?>

                        </span>
                        <span class="text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300 px-3 py-1.5 rounded-xl">
                            التعاقد: <?php echo e(number_format($reportData['total_contracted_value'] ?? 0, 2)); ?> ج.م
                        </span>
                        <span class="text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300 px-3 py-1.5 rounded-xl">
                            المسدد: <?php echo e(number_format($reportData['total_paid_amount'] ?? 0, 2)); ?> ج.م
                        </span>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">الكورسات والمجموعات</th>
                                <th class="p-3">نوع العملية</th>
                                <th class="p-3">التاريخ</th>
                                <th class="p-3">الصافي التعاقدي</th>
                                <th class="p-3">المدفوع</th>
                                <th class="p-3">المتبقي</th>
                                <th class="p-3">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold"><?php echo e($enr['enrollment_code']); ?></td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($enr['student_name']); ?></td>
                                    <td class="p-3 text-gray-600 dark:text-gray-300"><?php echo e($enr['courses']); ?> (<?php echo e($enr['groups']); ?>)</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($enr['operation_type'] === 'تجديد اشتراك' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'); ?>">
                                            <?php echo e($enr['operation_type']); ?>

                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500"><?php echo e($enr['date']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e(number_format($enr['net_amount'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600 font-bold"><?php echo e(number_format($enr['paid_amount'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold"><?php echo e(number_format($enr['remaining_amount'], 2)); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($enr['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : ($enr['status'] === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700')); ?>">
                                            <?php echo e($enr['status']); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد اشتراكات أو تجديدات مسجلة خلال الفترة المحددة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'collections'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير التحصيلات النقدية الفعلية</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">سندات القبض النقدية الفعلية المودعة بالخزائن — لا يتم تكرار السند بتوزيعه على الأقساط</p>
                    </div>
                    <div class="text-sm font-bold bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 px-3 py-1.5 rounded-xl">
                        إجمالي التحصيلات: <?php echo e(number_format($reportData['total_collections'] ?? 0, 2)); ?> ج.م (<?php echo e($reportData['collections_count'] ?? 0); ?> سند)
                    </div>
                </div>

                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportData['by_method'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <span class="text-[11px] text-gray-500 block mb-1"><?php echo e($m['method_label']); ?></span>
                            <span class="text-base font-black text-gray-900 dark:text-white"><?php echo e(number_format($m['total'], 2)); ?> ج.م</span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">(<?php echo e($m['count']); ?> حركة)</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">رقم السند</th>
                                <th class="p-3">تاريخ الدفع</th>
                                <th class="p-3">اسم الطالب</th>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">المبلغ</th>
                                <th class="p-3">طريقة الدفع</th>
                                <th class="p-3">الخزينة المودع بها</th>
                                <th class="p-3">الموظف المستلم</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold text-sky-600"><?php echo e($pay['payment_code']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($pay['payment_date']); ?></td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($pay['student_name']); ?></td>
                                    <td class="p-3 font-mono"><?php echo e($pay['enrollment_code']); ?></td>
                                    <td class="p-3 font-black text-emerald-600"><?php echo e(number_format($pay['amount'], 2)); ?></td>
                                    <td class="p-3"><?php echo e($pay['payment_method']); ?></td>
                                    <td class="p-3"><?php echo e($pay['vault_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($pay['received_by']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد سندات تحصيل مسجلة خلال هذه الفترة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'installments'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الأقساط وإعمار الديون والمديونيات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">فصل تام بين المتأخرات، والمستحق في تاريخ المرجع، والأقساط المستقبلية القادمة</p>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                        <span class="text-xs font-bold text-rose-700 dark:text-rose-300 block mb-1">المديونيات المتأخرة (Overdue)</span>
                        <div class="text-2xl font-black text-rose-600 dark:text-rose-400">
                            <?php echo e(number_format($reportData['overdue_amount'] ?? 0, 2)); ?> <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-rose-500 mt-1 block">عدد الأقساط: <?php echo e($reportData['overdue_count'] ?? 0); ?></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300 block mb-1">المستحق في تاريخ اليوم (Currently Due)</span>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400">
                            <?php echo e(number_format($reportData['currently_due_amount'] ?? 0, 2)); ?> <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-amber-500 mt-1 block">عدد الأقساط: <?php echo e($reportData['currently_due_count'] ?? 0); ?></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-300 block mb-1">الأقساط المستقبلية (Future Installments)</span>
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                            <?php echo e(number_format($reportData['future_amount'] ?? 0, 2)); ?> <span class="text-xs font-normal">ج.م</span>
                        </div>
                        <span class="text-[10px] text-blue-500 mt-1 block">عدد الأقساط: <?php echo e($reportData['future_count'] ?? 0); ?></span>
                    </div>
                </div>

                
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 space-y-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">أوعية إعمار الديون المتأخرة (Aging Buckets)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">1 إلى 7 أيام</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(number_format($reportData['aging_buckets']['1_7_days'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">8 إلى 30 يوماً</span>
                            <span class="text-sm font-bold text-amber-600"><?php echo e(number_format($reportData['aging_buckets']['8_30_days'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">31 إلى 60 يوماً</span>
                            <span class="text-sm font-bold text-orange-600"><?php echo e(number_format($reportData['aging_buckets']['31_60_days'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="text-[10px] text-gray-500 block">أكثر من 60 يوماً</span>
                            <span class="text-sm font-bold text-rose-600"><?php echo e(number_format($reportData['aging_buckets']['over_60_days'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">القسط</th>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">كود الاشتراك</th>
                                <th class="p-3">تاريخ الاستحقاق</th>
                                <th class="p-3">أصل المبلغ</th>
                                <th class="p-3">المسدد</th>
                                <th class="p-3">المتبقي</th>
                                <th class="p-3">حالة الاستحقاق</th>
                                <th class="p-3">حالة السداد</th>
                                <th class="p-3">أيام التأخير</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold">قسط #<?php echo e($ins['installment_number']); ?></td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($ins['student_name']); ?></td>
                                    <td class="p-3 font-mono"><?php echo e($ins['enrollment_code']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($ins['due_date']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e(number_format($ins['original_amount'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600 font-bold"><?php echo e(number_format($ins['paid_amount'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold"><?php echo e(number_format($ins['remaining_amount'], 2)); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ins['due_status'] === 'متأخر' ? 'bg-rose-100 text-rose-700' : ($ins['due_status'] === 'مستحق اليوم' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')); ?>">
                                            <?php echo e($ins['due_status']); ?>

                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ins['payment_status'] === 'مسدد جزئياً' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo e($ins['payment_status']); ?>

                                        </span>
                                    </td>
                                    <td class="p-3 font-mono <?php echo e($ins['days_overdue'] > 0 ? 'text-rose-600 font-bold' : 'text-gray-400'); ?>">
                                        <?php echo e($ins['days_overdue'] > 0 ? $ins['days_overdue'] . ' يوم' : '—'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد أقساط مطابقة للمعايير المحددة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'vaults'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير ومطابقة حركة الخزائن</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">المعادلة المعتمدة: الرصيد الافتتاحي + التدفقات الداخلة - التدفقات الخارجة = الرصيد الختامي</p>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الخزينة</th>
                                <th class="p-3">الرصيد الافتتاحي</th>
                                <th class="p-3">التحصيلات النقدية</th>
                                <th class="p-3">تحويلات واردة</th>
                                <th class="p-3">المصروفات</th>
                                <th class="p-3">المرتبات المنصرفة</th>
                                <th class="p-3">استردادات مسددة</th>
                                <th class="p-3">تحويلات صادرة</th>
                                <th class="p-3">الرصيد الختامي</th>
                                <th class="p-3">الرصيد الحالي بالنظام</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['vaults'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($v['vault_name']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e(number_format($v['opening_balance'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600 font-bold">+<?php echo e(number_format($v['cash_receipts'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600">+<?php echo e(number_format($v['transfers_in'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold">-<?php echo e(number_format($v['expenses'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold">-<?php echo e(number_format($v['salaries'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold">-<?php echo e(number_format($v['refunds'], 2)); ?></td>
                                    <td class="p-3 text-rose-600">-<?php echo e(number_format($v['transfers_out'], 2)); ?></td>
                                    <td class="p-3 font-black text-sky-600 text-sm"><?php echo e(number_format($v['closing_balance'], 2)); ?></td>
                                    <td class="p-3 font-mono font-bold"><?php echo e(number_format($v['current_system_balance'], 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد خزائن نشطة مسجلة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'pnl'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">قائمة الدخل التشغيلي والتحليل المقارن</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">الفصل الصارم بين التدفق النقدي الفعلي والالتزامات التعاقدية التشغيلية</p>
                </div>

                
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                    <strong>إشعار سياسة الإفصاح المالي:</strong> <?php echo e($reportData['operating_statement_view']['accounting_policy_notice'] ?? ''); ?>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white border-b pb-2">الأساس النقدي التشغيلي (Cash Flow)</h3>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>التحصيلات النقدية المودعة</span>
                            <span class="font-bold text-emerald-600">+<?php echo e(number_format($reportData['cash_flow_view']['cash_collections'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المصروفات التشغيلية المدفوعة</span>
                            <span class="font-bold text-rose-600">-<?php echo e(number_format($reportData['cash_flow_view']['cash_expenses_paid'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المرتبات المنصرفة نقداً</span>
                            <span class="font-bold text-rose-600">-<?php echo e(number_format($reportData['cash_flow_view']['salaries_paid'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs pt-2 font-black">
                            <span>صافي التدفق النقدي التشغيلي</span>
                            <span class="text-sm <?php echo e(($reportData['cash_flow_view']['net_cash_flow'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">
                                <?php echo e(number_format($reportData['cash_flow_view']['net_cash_flow'] ?? 0, 2)); ?> ج.م
                            </span>
                        </div>
                    </div>

                    
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white border-b pb-2">المركز التحليلي التعاقدي (Contractual / Operating)</h3>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>صافي القيمة التعاقدية للاشتراكات</span>
                            <span class="font-bold text-emerald-600">+<?php echo e(number_format($reportData['operating_statement_view']['contracted_enrollments_value'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>إيرادات إضافية مسجلة</span>
                            <span class="font-bold text-emerald-600">+<?php echo e(number_format($reportData['operating_statement_view']['direct_revenues_recorded'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>استحقاقات أتعاب المهندسين المكتسبة</span>
                            <span class="font-bold text-rose-600">-<?php echo e(number_format($reportData['operating_statement_view']['instructor_compensation_earned'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span>المصروفات التشغيلية المسجلة</span>
                            <span class="font-bold text-rose-600">-<?php echo e(number_format($reportData['operating_statement_view']['operating_expenses_recorded'] ?? 0, 2)); ?> ج.م</span>
                        </div>
                        <div class="flex justify-between text-xs pt-2 font-black">
                            <span>النتيجة التشغيلية التحليلية</span>
                            <span class="text-sm <?php echo e(($reportData['operating_statement_view']['operating_result_analytic'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">
                                <?php echo e(number_format($reportData['operating_statement_view']['operating_result_analytic'] ?? 0, 2)); ?> ج.م
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'salaries'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير مستحقات ومرتبات المهندسين</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">تقييمات الإشراف التربوي (1-5) مستقلة تماماً ولا تؤثر تلقائياً على حسابات المرتبات</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-xl">
                            المستحق المكتسب: <?php echo e(number_format($reportData['total_earned'] ?? 0, 2)); ?> ج.م
                        </span>
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 px-3 py-1.5 rounded-xl">
                            المنصرف فعلياً: <?php echo e(number_format($reportData['total_disbursed'] ?? 0, 2)); ?> ج.م
                        </span>
                        <span class="text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 px-3 py-1.5 rounded-xl">
                            المتبقي المستحق: <?php echo e(number_format($reportData['outstanding_salary'] ?? 0, 2)); ?> ج.م
                        </span>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">المهندس / المحاضر</th>
                                <th class="p-3">الفترة المحسوبة</th>
                                <th class="p-3">الجلسات المنجزة</th>
                                <th class="p-3">سعر الجلسة</th>
                                <th class="p-3">المكافآت</th>
                                <th class="p-3">الخصومات</th>
                                <th class="p-3">إجمالي المستحق</th>
                                <th class="p-3">الحالة</th>
                                <th class="p-3">تاريخ الصرف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($sal['instructor_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($sal['period_label']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e($sal['completed_sessions']); ?></td>
                                    <td class="p-3"><?php echo e(number_format($sal['rate_per_session'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600">+<?php echo e(number_format($sal['bonus_amount'], 2)); ?></td>
                                    <td class="p-3 text-rose-600">-<?php echo e(number_format($sal['deduction_amount'], 2)); ?></td>
                                    <td class="p-3 font-black"><?php echo e(number_format($sal['total_amount'], 2)); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($sal['status'] === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'); ?>">
                                            <?php echo e($sal['status']); ?>

                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500"><?php echo e($sal['payment_date']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد سجلات مرتبات معتمدة خلال الفترة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'cancellations'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقرير الإلغاءات والتسويات المالية والاسترداد</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">الفصل الصارم: 1. إلغاء الاشتراك 2. قرار التسوية المالية 3. صرف الاسترداد الفعلي من الخزينة</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] text-gray-500 block mb-1">عدد الإلغاءات</span>
                        <span class="text-xl font-black text-gray-800 dark:text-gray-200"><?php echo e($reportData['cancellations_count'] ?? 0); ?></span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">الديون المسقطة بالتسوية</span>
                        <span class="text-xl font-black text-amber-600 dark:text-amber-400"><?php echo e(number_format($reportData['waived_debt_total'] ?? 0, 2)); ?> ج.م</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-[11px] text-blue-700 dark:text-blue-300 block mb-1">التزام الاسترداد المعتمد</span>
                        <span class="text-xl font-black text-blue-600 dark:text-blue-400"><?php echo e(number_format($reportData['total_refund_liability'] ?? 0, 2)); ?> ج.م</span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block mb-1">الاسترداد المنصرف فعلياً</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400"><?php echo e(number_format($reportData['actual_refunds_disbursed'] ?? 0, 2)); ?> ج.م</span>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الطالب</th>
                                <th class="p-3">تاريخ الإلغاء</th>
                                <th class="p-3">الموظف المنفذ</th>
                                <th class="p-3">سبب الإلغاء</th>
                                <th class="p-3">نوع التسوية</th>
                                <th class="p-3">المبلغ المسقط</th>
                                <th class="p-3">المحتجز بالمدرسة</th>
                                <th class="p-3">المستحق استرداده</th>
                                <th class="p-3">المنصرف من الخزنة</th>
                                <th class="p-3">حالة الاسترداد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['records'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $can): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($can['student_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($can['cancelled_at']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($can['cancelled_by']); ?></td>
                                    <td class="p-3 text-gray-700 dark:text-gray-300"><?php echo e($can['cancellation_reason']); ?></td>
                                    <td class="p-3 font-bold text-primary-600"><?php echo e($can['settlement_type']); ?></td>
                                    <td class="p-3 text-amber-600"><?php echo e(number_format($can['waived_amount'], 2)); ?></td>
                                    <td class="p-3 text-gray-700 font-bold"><?php echo e(number_format($can['retained_amount'], 2)); ?></td>
                                    <td class="p-3 text-blue-600 font-bold"><?php echo e(number_format($can['refundable_amount'], 2)); ?></td>
                                    <td class="p-3 text-emerald-600 font-black"><?php echo e(number_format($can['refunded_amount'], 2)); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($can['refund_status'] === 'refunded' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo e($can['refund_status']); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-400">لا توجد اشتراكات ملغاة خلال هذه الفترة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'academic'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">التحليل الأكاديمي والامتحانات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">لكل مادة مؤشراتها المستقلة دون دمج عشوائي — مسودات الدرجات مستثناة من التقييمات الرسمية</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-xs text-gray-500 block mb-1">جلسات الحضور المكتملة</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e($reportData['sessions']['completed'] ?? 0); ?></span>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-xs text-amber-700 dark:text-amber-300 block mb-1">جلسات لم يُرصد حضورها</span>
                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400"><?php echo e($reportData['attendance']['unrecorded_sessions_count'] ?? 0); ?></span>
                        <span class="text-[10px] text-amber-500 block mt-1">تأخر رصد إداري وليس غياباً</span>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <span class="text-xs text-blue-700 dark:text-blue-300 block mb-1">تسليمات الواجبات المنشورة</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400"><?php echo e($reportData['homework']['graded_published'] ?? 0); ?></span>
                        <span class="text-[10px] text-blue-500 block mt-1">ينتظر التصحيح: <?php echo e($reportData['homework']['pending_grading'] ?? 0); ?></span>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                        <span class="text-xs text-rose-700 dark:text-rose-300 block mb-1">نتائج الامتحانات المنخفضة (&lt; 50%)</span>
                        <span class="text-2xl font-black text-rose-600 dark:text-rose-400"><?php echo e($reportData['exams']['low_scores_count'] ?? 0); ?></span>
                        <span class="text-[10px] text-rose-500 block mt-1">نسبة النجاح العامة: <?php echo e($reportData['exams']['pass_rate'] ?? 0); ?>%</span>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'supervision'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">تقارير الإشراف التربوي المعتمدة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">حالات الملاحظات الإشرافية المعتمدة: (جديدة، بانتظار مراجعة المشرف، مكتملة) — التقارير الرسمية لا تُنشر إلا بتوقيع المشرف</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] text-gray-500 block mb-1">الملاحظات الجديدة</span>
                        <span class="text-xl font-black text-gray-800 dark:text-gray-200"><?php echo e($reportData['observations']['new'] ?? 0); ?></span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                        <span class="text-[11px] text-amber-700 dark:text-amber-300 block mb-1">بانتظار مراجعة المشرف</span>
                        <span class="text-xl font-black text-amber-600 dark:text-amber-400"><?php echo e($reportData['observations']['waiting_review'] ?? 0); ?></span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block mb-1">ملاحظات مكتملة ومعتمدة</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400"><?php echo e($reportData['observations']['completed'] ?? 0); ?></span>
                    </div>
                    <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800">
                        <span class="text-[11px] text-purple-700 dark:text-purple-300 block mb-1">التقارير الإشرافية المنشورة</span>
                        <span class="text-xl font-black text-purple-600 dark:text-purple-400"><?php echo e($reportData['supervision_reports']['published'] ?? 0); ?></span>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">كود الملاحظة</th>
                                <th class="p-3">عنوان التوجيه</th>
                                <th class="p-3">المهندس المعني</th>
                                <th class="p-3">المشرف التربوي</th>
                                <th class="p-3">المجموعة</th>
                                <th class="p-3">الأولوية</th>
                                <th class="p-3">تاريخ الإنشاء</th>
                                <th class="p-3">الحالة الرسمية</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['recent_observations'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $obs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-mono font-bold text-primary-600"><?php echo e($obs['observation_code']); ?></td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white"><?php echo e($obs['title']); ?></td>
                                    <td class="p-3"><?php echo e($obs['instructor_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo e($obs['supervisor_name']); ?></td>
                                    <td class="p-3"><?php echo e($obs['group_name']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($obs['priority'] === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo e($obs['priority']); ?>

                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500"><?php echo e($obs['created_at']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($obs['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($obs['status'] === 'waiting_supervisor_review' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')); ?>">
                                            <?php echo e($obs['status_label']); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">لا توجد ملاحظات إشرافية مسجلة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'employees'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">العمليات التشغيلية لموظفي المدرسة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">مؤشرات تشغيلية متخصصة لكل طبيعة عمل دون فرض تقييم عشوائي موحد</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white">مسؤولو التسجيل والقبول</h3>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['registration_staff'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-xs">
                                    <span class="font-bold text-gray-900 dark:text-white"><?php echo e($rs['name']); ?></span>
                                    <div class="flex gap-3 text-gray-500">
                                        <span>تسجيل طلاب: <strong><?php echo e($rs['students_registered']); ?></strong></span>
                                        <span>اشتراكات: <strong><?php echo e($rs['enrollments_recorded']); ?></strong></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-xs text-gray-400">لا يوجد موظفو تسجيل مسجلون.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 space-y-3">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white">المحاسبون وأمناء الخزائن</h3>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['accountants'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-xs">
                                    <span class="font-bold text-gray-900 dark:text-white"><?php echo e($acc['name']); ?></span>
                                    <div class="flex gap-3">
                                        <span class="text-gray-500">سندات قبض: <strong><?php echo e($acc['payments_recorded_count']); ?></strong></span>
                                        <span class="text-emerald-600 font-bold"><?php echo e(number_format($acc['collections_amount'], 2)); ?> ج.م</span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-xs text-gray-400">لا يوجد محاسبون مسجلون.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'branches'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">المقارنة المعيارية بين فروع المدرسة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">مقارنة المؤشرات الرئيسية بالفروع تحت نفس التعريف الزمني والمعايير الموحدة</p>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3">الفرع</th>
                                <th class="p-3">طلاب جدد</th>
                                <th class="p-3">الاشتراكات والتجديدات</th>
                                <th class="p-3">القيمة التعاقدية</th>
                                <th class="p-3">التحصيلات الفعلية</th>
                                <th class="p-3">إجمالي المديونيات</th>
                                <th class="p-3">الطلاب النشطون</th>
                                <th class="p-3">المجموعات النشطة</th>
                                <th class="p-3">جلسات غير مسجلة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['branches'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $br): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white text-sm"><?php echo e($br['branch_name']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e($br['new_students']); ?></td>
                                    <td class="p-3 font-bold text-emerald-600"><?php echo e($br['enrollments_count']); ?></td>
                                    <td class="p-3 font-bold"><?php echo e(number_format($br['contracted_value'], 2)); ?></td>
                                    <td class="p-3 font-black text-sky-600"><?php echo e(number_format($br['actual_collections'], 2)); ?></td>
                                    <td class="p-3 text-rose-600 font-bold"><?php echo e(number_format($br['total_debt'], 2)); ?></td>
                                    <td class="p-3"><?php echo e($br['active_students']); ?></td>
                                    <td class="p-3"><?php echo e($br['active_groups']); ?></td>
                                    <td class="p-3 font-mono text-amber-600 font-bold"><?php echo e($br['unrecorded_attendance_sessions']); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400">لا توجد بيانات مقارنة متاحة.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'alerts'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">لوحة التنبيهات الإدارية والاستثناءات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">تنبيهات مشتقة من بيانات حقيقية — للاطلاع والرقابة فقط ولا تنفذ إجراءات تلقائية على الحسابات</p>
                </div>

                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['alerts'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $al): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 rounded-xl border flex items-start justify-between gap-4 <?php echo e($al['severity'] === 'danger' ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800' : ($al['severity'] === 'warning' ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800' : 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800')); ?>">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black <?php echo e($al['severity'] === 'danger' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : ($al['severity'] === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300')); ?>">
                                        <?php echo e($al['type']); ?>

                                    </span>
                                    <h3 class="text-xs font-bold text-gray-900 dark:text-white"><?php echo e($al['title']); ?></h3>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300"><?php echo e($al['description']); ?></p>
                            </div>
                            <div class="text-left text-[11px] text-gray-400 whitespace-nowrap">
                                <div><?php echo e($al['branch_name']); ?></div>
                                <div><?php echo e($al['date']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center text-emerald-600 font-bold bg-emerald-50 dark:bg-emerald-950/20 rounded-xl">
                            لا توجد استثناءات أو تنبيهات إدارية نشطة حالياً. جميع المؤشرات ضمن الحدود المعتمدة.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'saved'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">التقارير المحفوظة والمجدولة</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">إدارة التكوينات المفضلة للمستخدم وجدولة الإعداد المسبق للمسودات</p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-300">التقارير المحفوظة</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData['saved'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-gray-900 dark:text-white"><?php echo e($sr['name']); ?></div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">القسم: <?php echo e($sr['category']); ?></div>
                                </div>
                                <button type="button" wire:click="setSection('<?php echo e($sr['category']); ?>')" class="px-3 py-1 text-xs font-bold bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition">
                                    فتح التقرير
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-xs text-gray-400 col-span-2">لا توجد تقارير محفوظة حالياً. يمكنك حفظ أي تقرير بالضغط على زر "حفظ الفلترة الحالية".</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSection === 'settings'): ?>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h2 class="text-base font-black text-gray-900 dark:text-white">إعدادات مركز التقارير الموحد</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">إعداد الحدود المعيارية للتنبيهات وتخصيص ترويسة ملفات الـ PDF</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">الحد الأدنى لتنبيه المديونية المتأخرة (ج.م)</label>
                        <input type="number" readonly value="<?php echo e($reportData['settings']['alert_threshold_debt'] ?? 1000); ?>" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">نسبة التنبيه للدرجات المنخفضة (%)</label>
                        <input type="number" readonly value="<?php echo e($reportData['settings']['alert_threshold_low_score_percentage'] ?? 50); ?>" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">ساعات مهلة تصحيح الواجبات</label>
                        <input type="number" readonly value="<?php echo e($reportData['settings']['alert_homework_overdue_hours'] ?? 48); ?>" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" />
                    </div>
                </div>

                <div class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200">
                    <strong>ملاحظة الصلاحيات:</strong> تعديل الحدود المعيارية لسياسات التنبيهات مقتصر على الإدارة العامة ومفوضي الفروع المعتمدين.
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDrillDownModal): ?>
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-5xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-magnifying-glass-plus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 text-primary-500 inline']); ?>
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
                            <?php echo e($drillDownTitle); ?>

                        </h3>
                        <button type="button" wire:click="closeDrillDownModal" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
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
                    </div>

                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-bold sticky top-0">
                                <tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $drillDownHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $head): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="p-3 border-b"><?php echo e($head); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $drillDownRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="p-3"><?php echo e($cell); ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e(count($drillDownHeaders)); ?>" class="p-6 text-center text-gray-400">لا توجد سجلات تفصيلية مسجلة لهذا المؤشر.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 dark:border-gray-700 pt-3">
                        <button type="button" wire:click="closeDrillDownModal" class="px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            إغلاق النافذة
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSaveReportModal): ?>
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">حفظ تكوين التقرير الحالي</h3>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم التقرير المحفوظ</label>
                        <input type="text" wire:model="savedReportName" placeholder="مثال: تقرير اشتراكات فرع القاهرة لشهر أكتوبر" class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary-500" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showSaveReportModal', false)" class="px-3.5 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            إلغاء
                        </button>
                        <button type="button" wire:click="saveReportConfiguration" class="px-4 py-2 text-xs font-black text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition">
                            تأكيد الحفظ
                        </button>
                    </div>
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
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/pages/reporting-center-page.blade.php ENDPATH**/ ?>
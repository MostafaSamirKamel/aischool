<div class="ai-tenant-widget rounded-2xl overflow-hidden mb-5 border border-blue-100 dark:border-slate-700 shadow-sm bg-gradient-to-br from-blue-50/70 to-purple-50/70 dark:from-slate-800 dark:to-slate-800/95" style="direction: rtl;">

    <!-- الهيدر -->
    <div class="px-6 py-2.5 flex items-center justify-between border-b border-blue-200/50 dark:border-slate-700 bg-gradient-to-r from-blue-100/80 to-purple-100/80 dark:from-slate-900/80 dark:to-slate-800">
        <div class="text-sm font-semibold text-blue-900 dark:text-slate-100 tracking-wide">
            نظام إدارة المدارس
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-400">
            لوحة التحكم السريعة
        </div>
    </div>

    <!-- المحتوى -->
    <div style="padding: 20px 24px; text-align: right;">
        <div style="
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            align-items: stretch;
        ">

            <!-- الفرع -->
            <div style="flex: 1 1 260px; min-width: 260px;">

                <div style="margin-bottom: 8px;">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-800 dark:text-sky-400">
                        📋 الفرع الحالي
                    </span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_super_admin || is_null(auth()->user()->tenant_id)): ?>

                    <div style="position: relative;">
                        <select
                            wire:change="switchTenant($event.target.value)"
                            class="w-full bg-white dark:bg-slate-900 border-2 border-blue-400 dark:border-slate-600 rounded-xl py-2.5 pe-3 ps-10 text-sm font-medium text-slate-800 dark:text-slate-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            style="
                                appearance: none;
                                background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2338bdf8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e&quot;);
                                background-repeat: no-repeat;
                                background-position: left 10px center;
                                background-size: 16px 12px;
                            "
                        >
                            <option value="">اختر فرع</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tenant->id); ?>" <?php echo e(session('tenant_id') == $tenant->id ? 'selected' : ''); ?>>
                                    🏢 <?php echo e($tenant->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <div style="margin-top: 6px; display: flex; align-items: center; gap: 6px; font-size: 0.8rem;" class="text-blue-800 dark:text-sky-400">
                        <span class="bi bi-info-circle"></span>
                        <span>اختر الفرع المراد العمل به من القائمة.</span>
                    </div>

                <?php else: ?>

                    <div class="rounded-xl p-3.5 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-slate-900 dark:to-slate-900 border border-blue-200/60 dark:border-slate-700">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                            <span class="text-xs text-blue-900 dark:text-sky-400">الفرع الحالي</span>
                        </div>
                        <div class="text-base font-bold text-blue-900 dark:text-slate-100">
                            <?php echo e(auth()->user()->tenant->name ?? 'بدون فرع'); ?>

                        </div>
                    </div>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>

            <!-- معلومات المستخدم -->
            <div style="flex: 1 1 260px; min-width: 260px; display: flex; flex-direction: column; gap: 14px; align-items: flex-end;">

                <!-- البادج -->
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_super_admin): ?>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-100/80 dark:bg-slate-700/80 border border-blue-300/80 dark:border-slate-600 text-blue-800 dark:text-sky-300 text-sm font-semibold">
                            <span>مسؤول النظام</span>
                        </div>
                    <?php else: ?>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 dark:bg-slate-700/80 border border-blue-200 dark:border-slate-600 text-blue-800 dark:text-sky-300 text-sm font-semibold">
                            <span>مستخدم النظام</span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- كارت المستخدم -->
                <div class="w-full rounded-xl p-3.5 bg-gradient-to-br from-purple-50/80 to-green-50/80 dark:from-slate-900 dark:to-slate-900 border border-blue-200/50 dark:border-slate-700">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="text-xs text-blue-800 dark:text-sky-400 font-medium">المستخدم الحالي</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">مسجل دخول الآن</span>
                    </div>

                    <div class="text-base font-bold text-blue-900 dark:text-slate-100 mb-1">
                        <?php echo e(auth()->user()->name); ?>

                    </div>

                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="text-xs text-slate-600 dark:text-slate-400 break-all">
                            <?php echo e(auth()->user()->email); ?>

                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- خط سفلي زخرفي -->
    <div style="
        height: 3px;
        background: linear-gradient(90deg, transparent, rgba(56,189,248,0.4), rgba(156,39,176,0.4), transparent);
    "></div>
</div>
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/widgets/tenant-switcher.blade.php ENDPATH**/ ?>
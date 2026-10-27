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
    <style>
        @media print {
            .no-print, .fi-sidebar, .fi-topbar, .fi-header, .fi-breadcrumbs {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
        
        .installment-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .dark .installment-card {
            background: #1E293B !important;
            border-color: #334155 !important;
            box-shadow: none !important;
        }
        
        .installment-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .installment-table th {
            background: linear-gradient(135deg, #364FAC 0%, #293C85 100%);
            color: white;
            padding: 12px;
            text-align: right;
            font-weight: 600;
        }
        .dark .installment-table th {
            background: #25344A !important;
            color: #CBD5E1 !important;
            border-bottom: 1px solid #334155 !important;
        }
        
        .installment-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .dark .installment-table td {
            background: #172235 !important;
            color: #F8FAFC !important;
            border-bottom: 1px solid #334155 !important;
        }
        
        .installment-table tbody tr:hover {
            background: #f9fafb;
        }
        .dark .installment-table tbody tr:hover td {
            background: #1E2D44 !important;
        }
    </style>

    <div class="space-y-6" dir="rtl">
        <!-- Toolbar -->
        <div class="no-print flex gap-3">
            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <span>🖨️</span>
                <span>طباعة</span>
            </button>
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-5">
            📆 تقرير أقساط الأسبوع
        </h2>

        <!-- بطاقات الإحصائيات -->
        <?php
            $stats = $this->getInstallmentStats();
        ?>
        
        <div class="no-print grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- إجمالي الأقساط -->
            <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">📊 إجمالي الأقساط</div>
                <div class="text-3xl font-extrabold text-blue-700 dark:text-sky-400"><?php echo e($stats['total']['count']); ?></div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-2"><?php echo e(number_format($stats['total']['amount'], 2)); ?> ج.م</div>
            </div>
            
            <!-- الأقساط المتأخرة -->
            <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">⚠️ متأخرة</div>
                <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400"><?php echo e($stats['overdue']['count']); ?></div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-2"><?php echo e(number_format($stats['overdue']['amount'], 2)); ?> ج.م</div>
            </div>
            
            <!-- أقساط اليوم -->
            <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">📅 اليوم</div>
                <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400"><?php echo e($stats['today']['count']); ?></div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-2"><?php echo e(number_format($stats['today']['amount'], 2)); ?> ج.م</div>
            </div>
            
            <!-- الأقساط القادمة -->
            <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">🔜 قادمة</div>
                <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400"><?php echo e($stats['upcoming']['count']); ?></div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-2"><?php echo e(number_format($stats['upcoming']['amount'], 2)); ?> ج.م</div>
            </div>
        </div>

        <!-- قائمة الفلترة -->
        <div class="no-print mb-5 text-right flex items-center gap-3">
            <label class="font-bold text-sm text-slate-700 dark:text-slate-300">تصفية حسب:</label>
            <select wire:model.live="installmentFilter" 
                    class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-sm cursor-pointer transition focus:ring-2 focus:ring-blue-500">
                <option value="all">📊 الكل (<?php echo e($stats['total']['count']); ?>)</option>
                <option value="overdue">⚠️ المتأخرة (<?php echo e($stats['overdue']['count']); ?>)</option>
                <option value="today">📅 اليوم (<?php echo e($stats['today']['count']); ?>)</option>
                <option value="upcoming">🔜 القادمة (<?php echo e($stats['upcoming']['count']); ?>)</option>
            </select>
        </div>

        <!-- جدول البيانات حسب الأيام -->
        <?php
            $weekTotal = 0;
            $weekPaid = 0;
            $weekRemaining = 0;
            $weekCount = 0;
        ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($installmentsByDay) > 0): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $installmentsByDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $installments): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $dayInstallments = collect($installments);
                    $dayTotal = $dayInstallments->sum('amount');
                    $dayPaid = $dayInstallments->sum('paid_amount');
                    $dayRemaining = $dayTotal - $dayPaid;
                    
                    $weekTotal += $dayTotal;
                    $weekPaid += $dayPaid;
                    $weekRemaining += $dayRemaining;
                    $weekCount += count($installments);
                ?>
                
                <div class="installment-card">
                    <h3 class="text-lg font-bold text-blue-700 dark:text-sky-400 mb-4">
                        <?php echo e($day); ?>

                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400 me-2">
                            (<?php echo e(count($installments)); ?> قسط - <?php echo e(number_format($dayRemaining, 2)); ?> ج.م متبقي)
                        </span>
                    </h3>
                    
                    <table class="installment-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>رقم الهاتف</th>
                                <th>قيمة القسط</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $installment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td class="font-semibold text-slate-800 dark:text-slate-100"><?php echo e($installment->enrollment?->student?->user?->name ?? '-'); ?></td>
                                    <td class="text-slate-600 dark:text-slate-300"><?php echo e($installment->enrollment?->student?->user?->phone ?? '-'); ?></td>
                                    <td class="text-blue-700 dark:text-sky-400 font-bold"><?php echo e(number_format($installment->amount, 2)); ?> ج.م</td>
                                    <td class="text-emerald-600 dark:text-emerald-400 font-semibold"><?php echo e(number_format($installment->paid_amount ?? 0, 2)); ?> ج.م</td>
                                    <td class="text-amber-600 dark:text-amber-400 font-semibold"><?php echo e(number_format($installment->amount - ($installment->paid_amount ?? 0), 2)); ?> ج.م</td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($installment->status === 'paid'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border dark:border-emerald-800">
                                                ✅ مسدد
                                            </span>
                                        <?php elseif($installment->status === 'partial'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 dark:border dark:border-amber-800">
                                                ⚠️ جزئي
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300 dark:border dark:border-red-800">
                                                ❌ غير مسدد
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- الإجماليات -->
            <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 mt-5">
                <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-3">📊 إجمالي الأسبوع:</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm text-slate-700 dark:text-slate-300">
                    <div><strong>العدد:</strong> <?php echo e($weekCount); ?> قسط</div>
                    <div><strong>الإجمالي:</strong> <?php echo e(number_format($weekTotal, 2)); ?> ج.م</div>
                    <div><strong>المدفوع:</strong> <?php echo e(number_format($weekPaid, 2)); ?> ج.م</div>
                    <div><strong>المتبقي:</strong> <?php echo e(number_format($weekRemaining, 2)); ?> ج.م</div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-10 rounded-xl text-center bg-emerald-50 dark:bg-slate-800/80 border border-emerald-100 dark:border-slate-700">
                <span class="text-5xl">✅</span>
                <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-400 mt-4">
                    لا توجد أقساط مستحقة هذا الأسبوع
                </h3>
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
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/pages/installments-due-week.blade.php ENDPATH**/ ?>
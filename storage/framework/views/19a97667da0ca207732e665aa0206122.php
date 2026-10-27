<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $viewData = $this->getViewData();
        $date = $viewData['date'];
        $dailyData = collect($viewData['dailyData']);
        $vaults = $viewData['vaults'];
        $tenants = $viewData['tenants'] ?? [];

        $totals = [
            'opening' => (float) $dailyData->sum('opening_balance'),
            'revenues' => (float) $dailyData->sum('revenues'),
            'payments' => (float) $dailyData->sum('student_payments'),
            'income' => (float) $dailyData->sum('total_income'),
            'expenses' => (float) $dailyData->sum('expenses'),
            'salaries' => (float) $dailyData->sum('salary_payments'),
            'total_expenses' => (float) $dailyData->sum('total_expenses'),
            'net' => (float) $dailyData->sum('net_movement'),
            'closing' => (float) $dailyData->sum('closing_balance'),
        ];
    ?>

    <style>
        .vault-report-shell {
            display: grid;
            gap: 1.25rem;
        }

        .bootstrap-panel {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .bootstrap-panel__header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .bootstrap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr));
            gap: 1rem;
        }

        .bootstrap-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 0.9rem;
            padding: 1rem 1.1rem;
            box-shadow: 0 0.35rem 0.85rem rgba(15, 23, 42, 0.06);
        }

        .bootstrap-card__label {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
            font-weight: 600;
        }

        .bootstrap-card__value {
            color: #111827;
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .bootstrap-card__hint {
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        .bootstrap-card--success {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .bootstrap-card--danger {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .bootstrap-card--warning {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .bootstrap-card--primary {
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .bootstrap-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: end;
        }

        .bootstrap-field {
            display: grid;
            gap: 0.35rem;
            min-width: 180px;
            flex: 1 1 180px;
        }

        .bootstrap-field label {
            color: #495057;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .bootstrap-field input,
        .bootstrap-field select {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 0.75rem;
            padding: 0.7rem 0.9rem;
            background: #fff;
            color: #212529;
        }

        .bootstrap-table-wrap {
            overflow-x: auto;
        }

        .bootstrap-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 980px;
        }

        .bootstrap-table th {
            background: #f8f9fa;
            color: #495057;
            font-size: 0.84rem;
            font-weight: 800;
            white-space: nowrap;
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }

        .bootstrap-table td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
            white-space: nowrap;
        }

        .bootstrap-table tbody tr:nth-child(even) {
            background: #fcfcfd;
        }

        .bootstrap-table tbody tr:hover {
            background: #f8fafc;
        }

        .bootstrap-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .bootstrap-badge--cash { background: #dcfce7; color: #166534; }
        .bootstrap-badge--wallet { background: #dbeafe; color: #1d4ed8; }
        .bootstrap-badge--bank { background: #ede9fe; color: #6d28d9; }

        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }
        .text-muted { color: #6c757d; }

        .bootstrap-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: #6c757d;
        }
    </style>

    <div class="vault-report-shell">
        <div class="bootstrap-panel">
            <div class="bootstrap-panel__header">
                <div style="display:flex;justify-content:space-between;align-items:start;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:1.4rem;font-weight:800;color:#111827;">تقرير الحركة اليومية للخزائن</div>
                        <div class="text-muted" style="margin-top:0.25rem;"><?php echo e($date->locale('ar')->translatedFormat('l، j F Y')); ?></div>
                    </div>

                    <div class="bootstrap-toolbar">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($tenants)): ?>
                            <div class="bootstrap-field">
                                <label>الفرع</label>
                                <select wire:model.live="selectedTenantId">
                                    <option value="">كل الفروع</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tenant->id); ?>"><?php echo e($tenant->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="bootstrap-field">
                            <label>التاريخ</label>
                            <input type="date" wire:model.live="selectedDate" />
                        </div>

                        <div class="bootstrap-field">
                            <label>الخزينة</label>
                            <select wire:model.live="selectedVaultId">
                                <option value="">كل الخزائن</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vaults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vault): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vault->id); ?>"><?php echo e($vault->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding:1.25rem 1.5rem;">
                <div class="bootstrap-grid">
                    <div class="bootstrap-card bootstrap-card--primary">
                        <div class="bootstrap-card__label">رصيد بداية اليوم</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['opening'], 2)); ?></div>
                        <div class="bootstrap-card__hint">ج.م</div>
                    </div>

                    <div class="bootstrap-card bootstrap-card--success">
                        <div class="bootstrap-card__label">إجمالي الإيرادات</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['revenues'], 2)); ?></div>
                        <div class="bootstrap-card__hint">إيرادات مباشرة</div>
                    </div>

                    <div class="bootstrap-card bootstrap-card--success">
                        <div class="bootstrap-card__label">مدفوعات الطلاب</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['payments'], 2)); ?></div>
                        <div class="bootstrap-card__hint">تحصيلات اليوم</div>
                    </div>

                    <div class="bootstrap-card bootstrap-card--warning">
                        <div class="bootstrap-card__label">مرتبات مدفوعة</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['salaries'], 2)); ?></div>
                        <div class="bootstrap-card__hint">خصم مباشر من الخزنة</div>
                    </div>

                    <div class="bootstrap-card bootstrap-card--danger">
                        <div class="bootstrap-card__label">إجمالي المصروفات</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['total_expenses'], 2)); ?></div>
                        <div class="bootstrap-card__hint">مصروفات + مرتبات</div>
                    </div>

                    <div class="bootstrap-card <?php echo e($totals['closing'] >= 0 ? 'bootstrap-card--primary' : 'bootstrap-card--danger'); ?>">
                        <div class="bootstrap-card__label">رصيد نهاية اليوم</div>
                        <div class="bootstrap-card__value"><?php echo e(number_format($totals['closing'], 2)); ?></div>
                        <div class="bootstrap-card__hint">صافي الحركة: <?php echo e(number_format($totals['net'], 2)); ?> ج.م</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bootstrap-panel">
            <div class="bootstrap-panel__header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                <div style="font-size:1.05rem;font-weight:800;color:#111827;">تفصيل الحركة حسب الخزينة</div>
                <div class="text-muted" style="font-size:0.88rem;">يشمل المرتبات المدفوعة كجزء من المصروفات اليومية</div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dailyData->isEmpty()): ?>
                <div class="bootstrap-empty">لا توجد خزائن نشطة أو لا توجد بيانات لهذا التاريخ.</div>
            <?php else: ?>
                <div class="bootstrap-table-wrap">
                    <table class="bootstrap-table">
                        <thead>
                            <tr>
                                <th>الخزينة</th>
                                <th>رصيد أول اليوم</th>
                                <th>الإيرادات</th>
                                <th>مدفوعات الطلاب</th>
                                <th>المرتبات</th>
                                <th>المصروفات الأخرى</th>
                                <th>إجمالي المصروفات</th>
                                <th>صافي الحركة</th>
                                <th>رصيد نهاية اليوم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dailyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $vaultTypeClass = match($data['vault']->type) {
                                        'cash' => 'bootstrap-badge--cash',
                                        'vodafone' => 'bootstrap-badge--wallet',
                                        default => 'bootstrap-badge--bank',
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <div style="display:grid;gap:0.35rem;">
                                            <div style="font-weight:800;color:#111827;"><?php echo e($data['vault']->name); ?></div>
                                            <div>
                                                <span class="bootstrap-badge <?php echo e($vaultTypeClass); ?>">
                                                    <span><?php echo e($data['vault']->type === 'cash' ? 'نقدي' : ($data['vault']->type === 'vodafone' ? 'فودافون كاش' : 'إنستاباي/أخرى')); ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-primary" style="font-weight:700;"><?php echo e(number_format($data['opening_balance'], 2)); ?> ج.م</td>
                                    <td class="text-success" style="font-weight:700;"><?php echo e(number_format($data['revenues'], 2)); ?> ج.م</td>
                                    <td class="text-success" style="font-weight:700;"><?php echo e(number_format($data['student_payments'], 2)); ?> ج.م</td>
                                    <td class="text-danger" style="font-weight:700;"><?php echo e(number_format($data['salary_payments'], 2)); ?> ج.م</td>
                                    <td class="text-danger" style="font-weight:700;"><?php echo e(number_format($data['expenses'], 2)); ?> ج.م</td>
                                    <td class="text-danger" style="font-weight:800;"><?php echo e(number_format($data['total_expenses'], 2)); ?> ج.م</td>
                                    <td class="<?php echo e($data['net_movement'] >= 0 ? 'text-success' : 'text-danger'); ?>" style="font-weight:800;"><?php echo e(number_format($data['net_movement'], 2)); ?> ج.م</td>
                                    <td class="<?php echo e($data['closing_balance'] >= 0 ? 'text-primary' : 'text-danger'); ?>" style="font-weight:800;"><?php echo e(number_format($data['closing_balance'], 2)); ?> ج.م</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/widgets/vault-daily-movement.blade.php ENDPATH**/ ?>
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
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @media print {
            .fi-sidebar, .fi-topbar, .fi-header, .fi-breadcrumbs, .fi-topbar-sticky, .fi-footer {
                display: none !important;
            }
            .fi-main, .fi-main-ctn, .fi-body {
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
    </style>

    <div style="animation: slideDown 0.5s ease-out;">
        <!-- Export & Print Buttons -->
        <div class="no-print" style="display: flex; gap: 12px; margin-bottom: 24px; animation: slideDown 0.5s ease-out 0.1s backwards; flex-wrap: wrap;">
            <a href="<?php echo e(route('admin.reports.all-installments.export-excel')); ?>" 
               style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.3s; cursor: pointer;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(34, 197, 94, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(34, 197, 94, 0.3)'">
                <span style="font-size: 20px;">📊</span> تصدير Excel
            </a>

            <a href="<?php echo e(route('admin.reports.all-installments.export-pdf')); ?>" 
               style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.3s; cursor: pointer;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(239, 68, 68, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.3)'">
                <span style="font-size: 20px;">📄</span> تصدير PDF
            </a>

            <a href="javascript:window.print();" 
               style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.3s; cursor: pointer;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(59, 130, 246, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)'">
                <span style="font-size: 20px;">🖨️</span> طباعة
            </a>
        </div>

        <!-- Filters Section -->
        <div class="no-print" style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); padding: 32px; border-radius: 20px; box-shadow: 0 12px 32px rgba(99, 102, 241, 0.15); margin-bottom: 32px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50%; right: -10%; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; filter: blur(40px);"></div>
            <div style="position: relative; z-index: 10; display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div style="background: white; border-radius: 16px; padding: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                    <span style="font-size: 36px;">🔍</span>
                </div>
                <div>
                    <h3 style="color: white; font-size: 24px; font-weight: 900; margin: 0;">الفلاتر والبحث</h3>
                    <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin: 4px 0 0 0;">ابحث وصفّي الأقساط حسب احتياجاتك بسهولة</p>
                </div>
            </div>

            <form wire:submit.prevent="applyFilters" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; position: relative; z-index: 10;">
                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">الدورة</label>
                    <select wire:model.live="course_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                        <option value="">جميع الدورات</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getCourses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>"><?php echo e($course->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">المجموعة</label>
                    <select wire:model.live="group_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                        <option value="">جميع المجموعات</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getGroups(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">الطالب</label>
                    <select wire:model.live="student_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                        <option value="">جميع الطلاب</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getStudents(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>"><?php echo e($student->user?->name); ?> (<?php echo e($student->student_code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">من تاريخ</label>
                    <input type="date" wire:model.live="date_from" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">إلى تاريخ</label>
                    <input type="date" wire:model.live="date_to" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">نوع العرض</label>
                    <select wire:model.live="display_type" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                        <option value="all">جميع الأقساط</option>
                        <option value="overdue">الأقساط المتأخرة فقط</option>
                        <option value="upcoming">الأقساط القريبة (7 أيام)</option>
                        <option value="partial">السداد الجزئي فقط</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">فلتر الوقت</label>
                    <select wire:model.live="time_filter" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                        <option value="all">جميع الأقساط</option>
                        <option value="past">الأقساط السابقة (قبل اليوم)</option>
                        <option value="today">أقساط اليوم فقط</option>
                        <option value="future">الأقساط المستقبلية (بعد اليوم)</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Time-based Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; animation: fadeIn 0.8s ease-out 0.15s backwards;">
            
            <!-- Past Installments Card -->
            <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 24px; border-radius: 16px; box-shadow: 0 8px 24px rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">الأقساط السابقة</p>
                        <p style="color: white; font-size: 32px; font-weight: 900; margin: 0;"><?php echo e($pastStats['count'] ?? 0); ?></p>
                        <p style="color: rgba(255,255,255,0.75); font-size: 11px; margin: 4px 0 0 0;">قبل اليوم</p>
                    </div>
                    <span style="font-size: 48px; opacity: 0.3;">📅</span>
                </div>
                <div style="border-top: 1px solid rgba(255, 255, 255, 0.2); padding-top: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: rgba(255,255,255,0.85); font-size: 11px;">المتبقي:</span>
                        <span style="color: white; font-weight: 700; font-size: 12px;"><?php echo e(number_format($pastStats['total_remaining'] ?? 0, 0)); ?> ج</span>
                    </div>
                </div>
            </div>

            <!-- Today Installments Card -->
            <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 24px; border-radius: 16px; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">أقساط اليوم</p>
                        <p style="color: white; font-size: 32px; font-weight: 900; margin: 0;"><?php echo e($todayStats['count'] ?? 0); ?></p>
                        <p style="color: rgba(255,255,255,0.75); font-size: 11px; margin: 4px 0 0 0;"><?php echo e(now()->format('Y-m-d')); ?></p>
                    </div>
                    <span style="font-size: 48px; opacity: 0.3;">📆</span>
                </div>
                <div style="border-top: 1px solid rgba(255, 255, 255, 0.2); padding-top: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: rgba(255,255,255,0.85); font-size: 11px;">المتبقي:</span>
                        <span style="color: white; font-weight: 700; font-size: 12px;"><?php echo e(number_format($todayStats['total_remaining'] ?? 0, 0)); ?> ج</span>
                    </div>
                </div>
            </div>

            <!-- Future Installments Card -->
            <div style="background: linear-gradient(135deg, #22c55e, #16a34a); padding: 24px; border-radius: 16px; box-shadow: 0 8px 24px rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">الأقساط المستقبلية</p>
                        <p style="color: white; font-size: 32px; font-weight: 900; margin: 0;"><?php echo e($futureStats['count'] ?? 0); ?></p>
                        <p style="color: rgba(255,255,255,0.75); font-size: 11px; margin: 4px 0 0 0;">بعد اليوم</p>
                    </div>
                    <span style="font-size: 48px; opacity: 0.3;">🗓️</span>
                </div>
                <div style="border-top: 1px solid rgba(255, 255, 255, 0.2); padding-top: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: rgba(255,255,255,0.85); font-size: 11px;">المتبقي:</span>
                        <span style="color: white; font-weight: 700; font-size: 12px;"><?php echo e(number_format($futureStats['total_remaining'] ?? 0, 0)); ?> ج</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <?php
            $stats = $this->getStats();
            $totalAmount = $stats['total_amount'] ?? 0;
            $totalPaid = $stats['total_paid'] ?? 0;
            $paidPercentage = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
        ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; animation: fadeIn 0.8s ease-out 0.2s backwards;">
            <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">إجمالي الأقساط</p>
                        <p style="color: white; font-size: 28px; font-weight: 900; margin: 0;"><?php echo e($stats['total_count']); ?></p>
                    </div>
                    <span style="font-size: 32px;">📊</span>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">المبلغ الكلي</p>
                        <p style="color: white; font-size: 20px; font-weight: 900; margin: 0;"><?php echo e(number_format($totalAmount, 0)); ?> ج</p>
                    </div>
                    <span style="font-size: 32px;">💰</span>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #22c55e, #16a34a); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">المدفوع</p>
                        <p style="color: white; font-size: 20px; font-weight: 900; margin: 0;"><?php echo e(number_format($totalPaid, 0)); ?> ج</p>
                    </div>
                    <span style="font-size: 32px;">✅</span>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">المتبقي</p>
                        <p style="color: white; font-size: 20px; font-weight: 900; margin: 0;"><?php echo e(number_format($stats['total_remaining'], 0)); ?> ج</p>
                    </div>
                    <span style="font-size: 32px;">⏳</span>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">المتأخرة</p>
                        <p style="color: white; font-size: 28px; font-weight: 900; margin: 0;"><?php echo e($stats['overdue_count']); ?></p>
                    </div>
                    <span style="font-size: 32px;">🚨</span>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #ec4899, #db2777); padding: 20px; border-radius: 16px; box-shadow: 0 8px 24px rgba(236, 72, 153, 0.15); border: 1px solid rgba(236, 72, 153, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="color: rgba(255,255,255,0.85); font-size: 12px; font-weight: bold; margin: 0 0 8px 0;">السداد الجزئي</p>
                        <p style="color: white; font-size: 28px; font-weight: 900; margin: 0;"><?php echo e($stats['partial_count']); ?></p>
                    </div>
                    <span style="font-size: 32px;">⚠️</span>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 32px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <p style="font-weight: 700; color: #1e293b; margin: 0; font-size: 14px;">نسبة السداد الإجمالية</p>
                <p style="font-weight: 900; font-size: 18px; color: #6366f1; margin: 0;"><?php echo e(round($paidPercentage)); ?>%</p>
            </div>
            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                <div style="background: linear-gradient(90deg, #22c55e, #16a34a); height: 100%; width: <?php echo e($paidPercentage); ?>%; transition: all 0.5s ease; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);"></div>
            </div>
        </div>

        <!-- Installments Table -->
        <?php
            $installments = $this->getInstallments();
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($installments->count() > 0): ?>
            <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; animation: fadeIn 1s ease-out 0.4s backwards;">
                <div style="background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 24px; display: flex; align-items: center; gap: 16px;">
                    <span style="font-size: 32px;">📋</span>
                    <div>
                        <h3 style="color: white; font-size: 20px; font-weight: 900; margin: 0;">تفاصيل الأقساط</h3>
                        <p style="color: rgba(255,255,255,0.85); font-size: 13px; margin: 4px 0 0 0;"><?php echo e($installments->count()); ?> قسط</p>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">#</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">الطالب</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">كود الطالب</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">المبلغ</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">المدفوع</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">المتبقي</th>
                                <th style="padding: 16px; text-align: center; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">الحالة</th>
                                <th style="padding: 16px; text-align: right; font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">تاريخ الاستحقاق</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $installment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: all 0.2s; background: <?php echo e($index % 2 == 0 ? 'white' : '#f8fafc'); ?>; hover: background-color: #f1f5f9;">
                                    <td  style="padding: 16px; color: #64748b; font-weight: 600;"><?php echo e($index + 1); ?></td>
                                    <td style="padding: 16px; color: #1e293b; font-weight: 700;"><?php echo e($installment->enrollment?->student?->user?->name ?? '-'); ?></td>
                                    <td style="padding: 16px; color: #64748b; font-weight: 500;"><?php echo e($installment->enrollment?->student?->student_code ?? '-'); ?></td>
                                    <td style="padding: 16px; color: #6366f1; font-weight: 800;"><?php echo e(number_format($installment->amount, 2)); ?> ج</td>
                                    <td style="padding: 16px; color: #22c55e; font-weight: 700;"><?php echo e(number_format($installment->paid_amount, 2)); ?> ج</td>
                                    <td style="padding: 16px; color: #f59e0b; font-weight: 700;"><?php echo e(number_format($installment->amount - $installment->paid_amount, 2)); ?> ج</td>
                                    <td style="padding: 16px; text-align: center;">
                                        <?php
                                            $status = $installment->payment_status ?? [];
                                            $badgeClass = match($status['color'] ?? 'blue') {
                                                'green' => 'background: rgba(34, 197, 94, 0.1); color: #15803d; border: 1.5px solid #86efac;',
                                                'yellow' => 'background: rgba(234, 179, 8, 0.1); color: #854d0e; border: 1.5px solid #fcd34d;',
                                                'red' => 'background: rgba(239, 68, 68, 0.1); color: #7f1d1d; border: 1.5px solid #fca5a5;',
                                                default => 'background: rgba(59, 130, 246, 0.1); color: #1e40af; border: 1.5px solid #93c5fd;',
                                            };
                                        ?>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; <?php echo e($badgeClass); ?>">
                                            <?php echo e($status['icon'] ?? '⏳'); ?> <?php echo e($status['status'] ?? 'قريب'); ?>

                                        </span>
                                    </td>
                                    <td style="padding: 16px; color: #64748b; font-weight: 500;"><?php echo e($installment->due_date?->format('d/m/Y') ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div style="background: linear-gradient(135deg, #f0fdf4, #e8f8f0); padding: 60px 20px; border-radius: 16px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 2px dashed #bbf7d0; animation: fadeIn 1s ease-out 0.4s backwards;">
                <span style="font-size: 64px; display: block; margin-bottom: 16px;">📭</span>
                <h3 style="color: #0f172a; font-size: 20px; font-weight: 900; margin: 0 0 8px 0;">لا توجد أقساط</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">لا توجد أقساط تطابق الفلاتر المحددة</p>
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
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/pages/reports/all-installments-report.blade.php ENDPATH**/ ?>
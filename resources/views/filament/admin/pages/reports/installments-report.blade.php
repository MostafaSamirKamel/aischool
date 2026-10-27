<x-filament-panels::page>
    <style>
        @keyframes slideDown { 
            from { opacity: 0; transform: translateY(-20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        @keyframes fadeIn { 
            from { opacity: 0; } 
            to { opacity: 1; } 
        }
        @keyframes bounce { 
            0%, 100% { transform: translateY(0); } 
            50% { transform: translateY(-10px); } 
        }
        .animate-slide-down { animation: slideDown 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        .animate-bounce { animation: bounce 1s infinite; }
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

    <div style="background: white; padding: 24px; width: 100%;">
        <div style="display: flex; flex-direction: column; gap: 32px;">

            <!-- Export & Print Buttons -->
            <div class="no-print" style="display: flex; gap: 12px; animation: slideDown 0.5s ease-out;">
                <a href="{{ route('admin.reports.installments.export-excel') }}" 
                   style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.2s; cursor: pointer;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(34, 197, 94, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(34, 197, 94, 0.3)'">
                    <span style="font-size: 20px;">📊</span> تصدير Excel
                </a>

                <a href="{{ route('admin.reports.installments.export-pdf') }}" 
                   style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(220, 38, 38)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.2s; cursor: pointer;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(239, 68, 68, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.3)'">
                    <span style="font-size: 20px;">📄</span> تصدير PDF
                </a>

                <a href="javascript:window.print();" 
                   style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235)); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.2s; cursor: pointer;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(59, 130, 246, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)'">
                    <span style="font-size: 20px;">🖨️</span> طباعة
                </a>
            </div>

            <!-- Filters Section -->
            <div class="no-print" style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); padding: 32px; border-radius: 20px; box-shadow: 0 12px 32px rgba(99, 102, 241, 0.15); position: relative; overflow: hidden;">
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

                <form wire:submit.prevent="applyFilters" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; position: relative; z-index: 10;">
                    <div>
                        <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">الدورة</label>
                        <select wire:model.live="course_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                            <option value="">جميع الدورات</option>
                            @foreach($this->getCourses() as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">المجموعة</label>
                        <select wire:model.live="group_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                            <option value="">جميع المجموعات</option>
                            @foreach($this->getGroups() as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: white; font-weight: bold; margin-bottom: 8px; font-size: 14px;">الطالب</label>
                        <select wire:model.live="student_id" style="width: 100%; padding: 12px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.95); color: #1f2937; font-weight: 500;">
                            <option value="">جميع الطلاب</option>
                            @foreach($this->getStudents() as $student)
                                <option value="{{ $student->id }}">{{ $student->user?->name }} ({{ $student->student_code }})</option>
                            @endforeach
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
            
            <!-- Hero Header -->
            <div style="position: relative; overflow: hidden; border-radius: 24px; background: linear-gradient(135deg, rgb(79, 70, 229), rgb(139, 92, 246), rgb(236, 72, 153)); padding: 32px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: slideDown 0.5s ease-out;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                        <div style="background: rgba(255, 255, 255, 0.2); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.3);">
                            📊
                        </div>
                        <div>
                            <h1 style="font-size: 28px; font-weight: 900; color: white; margin: 0 0 8px 0;">تقرير الأقساط المستحقة والقريبة</h1>
                            <p style="color: #fed7aa; margin: 0; font-size: 14px;">متابعة شاملة لحالة الأقساط الدراسية</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time-based Statistics Cards Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; animation: fadeIn 0.8s ease-out;">
                
                <!-- Past Installments Card -->
                <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(139, 92, 246), rgb(124, 58, 237));"></div>
                    <div style="position: relative; padding: 28px;">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                            <div style="font-size: 36px;">📅</div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط السابقة</h3>
                                <p style="color: #ddd6fe; margin: 0; font-size: 12px;">قبل اليوم</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #ddd6fe; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">العدد</p>
                                <p style="font-size: 36px; font-weight: 900; color: white; margin: 0;">{{ $pastStats['count'] }}</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #ddd6fe; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">المتبقي</p>
                                <p style="font-size: 28px; font-weight: 900; color: white; margin: 0;">{{ number_format($pastStats['total_amount'], 0) }}</p>
                                <p style="color: #ddd6fe; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today Installments Card -->
                <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235));"></div>
                    <div style="position: relative; padding: 28px;">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                            <div style="font-size: 36px;">📆</div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: white; margin: 0 0 4px 0;">أقساط اليوم</h3>
                                <p style="color: #dbeafe; margin: 0; font-size: 12px;">{{ now()->format('Y-m-d') }}</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #dbeafe; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">العدد</p>
                                <p style="font-size: 36px; font-weight: 900; color: white; margin: 0;">{{ $todayStats['count'] }}</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #dbeafe; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">المتبقي</p>
                                <p style="font-size: 28px; font-weight: 900; color: white; margin: 0;">{{ number_format($todayStats['total_amount'], 0) }}</p>
                                <p style="color: #dbeafe; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Future Installments Card -->
                <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(34, 197, 94), rgb(22, 163, 74));"></div>
                    <div style="position: relative; padding: 28px;">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                            <div style="font-size: 36px;">🗓️</div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط المستقبلية</h3>
                                <p style="color: #dcfce7; margin: 0; font-size: 12px;">بعد اليوم</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #dcfce7; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">العدد</p>
                                <p style="font-size: 36px; font-weight: 900; color: white; margin: 0;">{{ $futureStats['count'] }}</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #dcfce7; font-size: 11px; font-weight: bold; margin: 0 0 6px 0; letter-spacing: 1px;">المتبقي</p>
                                <p style="font-size: 28px; font-weight: 900; color: white; margin: 0;">{{ number_format($futureStats['total_amount'], 0) }}</p>
                                <p style="color: #dcfce7; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 24px; animation: fadeIn 0.8s ease-out;">
                
                <!-- Overdue Card -->
                <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(239, 68, 68), rgb(244, 114, 147), rgb(244, 63, 94));"></div>
                    <div style="position: relative; padding: 32px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                            <div style="font-size: 40px;">🔔</div>
                            <div>
                                <h3 style="font-size: 22px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط المتأخرة</h3>
                                <p style="color: #fecaca; margin: 0; font-size: 13px;">تحتاج متابعة فورية</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">العدد</p>
                                <p style="font-size: 42px; font-weight: 900; color: white; margin: 0;">{{ $overdueStats['count'] }}</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المستحق</p>
                                <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format($overdueStats['total_due'], 0) }}</p>
                                <p style="color: #fecaca; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fecaca; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المتبقي</p>
                                <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format($overdueStats['total_amount'], 0) }}</p>
                                <p style="color: #fecaca; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Card -->
                <div style="position: relative; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgb(217, 119, 6), rgb(249, 115, 22), rgb(234, 179, 8));"></div>
                    <div style="position: relative; padding: 32px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                            <div style="font-size: 40px;">⏰</div>
                            <div>
                                <h3 style="font-size: 22px; font-weight: 900; color: white; margin: 0 0 4px 0;">الأقساط القريبة</h3>
                                <p style="color: #fef3c7; margin: 0; font-size: 13px;">خلال 7 أيام</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">العدد</p>
                                <p style="font-size: 42px; font-weight: 900; color: white; margin: 0;">{{ $upcomingStats['count'] }}</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المستحق</p>
                                <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format($upcomingStats['total_due'], 0) }}</p>
                                <p style="color: #fef3c7; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 16px; border: 1px solid rgba(255, 255, 255, 0.2); text-align: center;">
                                <p style="color: #fef3c7; font-size: 11px; font-weight: bold; margin: 0 0 8px 0; letter-spacing: 1px;">المتبقي</p>
                                <p style="font-size: 32px; font-weight: 900; color: white; margin: 0;">{{ number_format($upcomingStats['total_amount'], 0) }}</p>
                                <p style="color: #fef3c7; font-size: 10px; margin: 4px 0 0 0;">جنيه</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success State -->
            @if($overdueInstallments->count() == 0 && $upcomingInstallments->count() == 0)
            <div style="background: linear-gradient(135deg, #dcfce7, #e0f2fe); border-radius: 16px; padding: 40px; text-align: center; border: 2px solid #bbf7d0;">
                <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                <h3 style="font-size: 20px; font-weight: 900; color: #15803d; margin: 0 0 8px 0;">ممتاز!</h3>
                <p style="color: #059669; margin: 0;">جميع الأقساط مدفوعة في موعدها</p>
            </div>
            @endif

        </div>
    </div>
</x-filament-panels::page>

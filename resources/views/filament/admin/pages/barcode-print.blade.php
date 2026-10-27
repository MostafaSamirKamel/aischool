<x-filament::page>
    <style>
        .barcode-page-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 32px 16px;
        }

        .barcode-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .barcode-header-title {
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 24px;
        }

        .barcode-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: end;
        }

        .barcode-control-group {
            flex: 1;
            min-width: 200px;
        }

        .barcode-control-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .barcode-control-group select,
        .barcode-control-group input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .barcode-control-group select:focus,
        .barcode-control-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
            margin-top: 32px;
        }

        .barcode-card {
            background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .barcode-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }

        .barcode-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        }

        .barcode-role {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .barcode-name {
            font-size: 18px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .barcode-wrapper {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            border: 2px solid #e2e8f0;
        }

        .barcode {
            width: 100%;
            max-width: 220px;
            height: 60px;
            margin: 0 auto;
        }

        .barcode-code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #4a5568;
            margin-top: 12px;
            letter-spacing: 1px;
        }

        .barcode-empty {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .barcode-empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .barcode-empty-text {
            font-size: 18px;
            font-weight: 600;
            color: #718096;
        }

        @media print {
            body * {
                visibility: hidden !important;
            }

            .barcode-page-wrapper,
            .barcode-page-wrapper * {
                visibility: visible !important;
            }

            .barcode-page-wrapper {
                position: absolute;
                inset: 0;
                width: 100%;
                margin: 0;
            }

            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-page-header,
            .fi-breadcrumbs {
                display: none !important;
            }

            .barcode-page-wrapper {
                background: #ffffff;
                padding: 0;
            }

            .barcode-header {
                display: none;
            }

            .barcode-grid {
                gap: 12px;
                grid-template-columns: repeat(3, 1fr);
            }

            .barcode-card {
                page-break-inside: avoid;
                box-shadow: none;
                border: 2px solid #000000;
            }

            .barcode-card::before {
                display: none;
            }

            .barcode-card:hover {
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .barcode-page-wrapper {
                padding: 16px 8px;
            }

            .barcode-header {
                padding: 20px;
            }

            .barcode-header-title {
                font-size: 24px;
            }

            .barcode-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
        }
    </style>

    <div class="barcode-page-wrapper">
        <div class="barcode-header print:hidden">
            <h1 class="barcode-header-title">🎫 طباعة بطاقات الباركود</h1>
            
            <div class="barcode-controls">
                <div class="barcode-control-group">
                    <label>النوع</label>
                    <select wire:model.live="type">
                        <option value="students">👨‍🎓 الطلاب</option>
                        <option value="instructors">👨‍🏫 المدرسين</option>
                        <option value="secretaries">👔 السكرتارية</option>
                    </select>
                </div>

                <div class="barcode-control-group">
                    <label>بحث بالاسم أو الكود</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="🔍 اكتب اسم أو كود...">
                </div>

                @if ($type === 'students')
                    <div class="barcode-control-group">
                        <label>طريقة الاختيار</label>
                        <select wire:model.live="studentScope">
                            <option value="count">عدد معين</option>
                            <option value="single">طالب واحد</option>
                            <option value="group">جروب كامل</option>
                        </select>
                    </div>

                    @if ($studentScope === 'single')
                        <div class="barcode-control-group" style="min-width: 280px;">
                            <label>اختيار الطالب</label>
                            <select wire:model.live="selectedStudentId">
                                <option value="">-- اختر الطالب --</option>
                                @foreach ($this->getStudentOptions() as $studentId => $studentLabel)
                                    <option value="{{ $studentId }}">{{ $studentLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($studentScope === 'group')
                        <div class="barcode-control-group" style="min-width: 240px;">
                            <label>اختيار الجروب</label>
                            <select wire:model.live="selectedGroupId">
                                <option value="">-- اختر الجروب --</option>
                                @foreach ($this->getGroupOptions() as $groupId => $groupName)
                                    <option value="{{ $groupId }}">{{ $groupName }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif

                @if ($type !== 'students' || $studentScope === 'count')
                    <div class="barcode-control-group" style="max-width: 180px;">
                        <label>الحد الأقصى</label>
                        <input type="number" min="1" max="500" wire:model.live.debounce.500ms="limit">
                    </div>
                @endif

                <x-filament::button 
                    color="primary" 
                    icon="heroicon-o-printer" 
                    onclick="window.print()"
                    size="xl"
                    class="px-8">
                    طباعة البطاقات
                </x-filament::button>
            </div>
        </div>

        @php
            $items = $this->getItems();
        @endphp

        @if ($items->isEmpty())
            <div class="barcode-empty">
                <div class="barcode-empty-icon">📭</div>
                <div class="barcode-empty-text">لا توجد بيانات للعرض</div>
            </div>
        @else
            <div class="barcode-grid">
                @foreach ($items as $item)
                    @php
                        $name = $item->user?->name ?? 'غير محدد';
                        $code = $type === 'students' ? ($item->student_code ?? '') : ($item->employee_code ?? '');
                        $role = $type === 'students' ? 'طالب' : ($type === 'instructors' ? 'مدرس' : 'سكرتارية');
                    @endphp

                    <div class="barcode-card">
                        <div class="barcode-role">{{ $role }}</div>
                        <div class="barcode-name">{{ $name }}</div>
                        <div class="barcode-wrapper">
                            <svg class="barcode" data-code="{{ $code }}"></svg>
                        </div>
                        <div class="barcode-code">{{ $code }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            renderBarcodes();
        });

        document.addEventListener('DOMContentLoaded', () => {
            renderBarcodes();
        });

        if (window.Livewire) {
            window.Livewire.hook('message.processed', () => {
                renderBarcodes();
            });
        }

        function renderBarcodes() {
            const elements = document.querySelectorAll('svg.barcode');
            elements.forEach((el) => {
                const code = el.getAttribute('data-code') || '';
                if (!code) {
                    return;
                }
                JsBarcode(el, code, {
                    format: 'CODE128',
                    lineColor: '#111827',
                    width: 2,
                    height: 50,
                    displayValue: false,
                    margin: 2,
                });
            });
        }
    </script>
</x-filament::page>

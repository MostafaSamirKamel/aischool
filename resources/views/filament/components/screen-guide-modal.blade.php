@php
    $guide = \App\Services\ScreenGuideService::getGuideForCurrentPage();
@endphp

<div x-data="{ showGuide: false }" class="inline-flex items-center">
    <!-- Screen Guide Action Button in Page Header -->
    <button
        type="button"
        @click="showGuide = true"
        style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.95rem; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; border-radius: 10px; font-size: 0.8rem; font-weight: 700; transition: all 0.2s; font-family: 'Cairo', system-ui, sans-serif; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
        onmouseover="this.style.background='#dcfce7'; this.style.borderColor='#86efac'; this.style.transform='translateY(-1px)';"
        onmouseout="this.style.background='#f0fdf4'; this.style.borderColor='#bbf7d0'; this.style.transform='translateY(0)';"
        title="دليل استخدام ومميزات الشاشة والأفكار الذكية"
    >
        <span style="font-size: 1rem;">💡</span>
        <span>دليل ومميزات الشاشة</span>
    </button>

    <!-- Modal Backdrop & Dialog (Bootstrap-style Card) -->
    <div
        x-show="showGuide"
        x-cloak
        style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); padding: 1rem;"
        @keydown.escape.window="showGuide = false"
    >
        <!-- Modal Dialog Box -->
        <div
            @click.away="showGuide = false"
            style="background: #ffffff; border-radius: 20px; width: min(calc(100vw - 1.5rem), 680px); max-height: calc(100dvh - 2.5rem); overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; font-family: 'Cairo', system-ui, sans-serif; text-align: right;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
        >
            <!-- Modal Header (Bootstrap Card Header style) -->
            <div style="padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-bottom: 1px solid #bbf7d0; border-radius: 20px 20px 0 0; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 12px; background: linear-gradient(135deg, #16a34a, #15803d); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; box-shadow: 0 2px 6px rgba(22,163,74,0.3);">
                        📖
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #0f172a;">{{ $guide['title'] }}</h4>
                        <span style="display: inline-block; margin-top: 0.2rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: #16a34a; color: #fff; font-size: 0.7rem; font-weight: 700;">
                            {{ $guide['badge'] }}
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showGuide = false"
                    style="cursor: pointer; background: transparent; border: none; font-size: 1.5rem; color: #64748b; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 8px; line-height: 1;"
                    onmouseover="this.style.color='#0f172a';"
                    onmouseout="this.style.color='#64748b';"
                >
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                
                <!-- Summary Alert -->
                <div style="background: #f8fafc; border-inline-start: 4px solid #16a34a; padding: 0.85rem 1rem; border-radius: 10px; font-size: 0.875rem; color: #334155; line-height: 1.6;">
                    <strong>📌 الغرض من الشاشة:</strong> {{ $guide['summary'] }}
                </div>

                <!-- Smart Ideas & Features -->
                <div>
                    <h5 style="margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 800; color: #15803d; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🚀</span>
                        <span>الأفكار والميزات الذكية الموجودة بالشاشة:</span>
                    </h5>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach ($guide['smart_ideas'] as $idea)
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.85rem; color: #1e293b; display: flex; align-items: flex-start; gap: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <span style="color: #16a34a; font-weight: 800;">✔</span>
                                <span style="line-height: 1.5;">{{ $idea }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Steps Workflow (Numbered Badges) -->
                <div>
                    <h5 style="margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 0.4rem;">
                        <span>📋</span>
                        <span>خطوات وطريقة الاستخدام السريعة:</span>
                    </h5>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach ($guide['steps'] as $index => $step)
                            <div style="display: flex; align-items: center; gap: 0.75rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #0369a1;">
                                <span style="width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0;">
                                    {{ $index + 1 }}
                                </span>
                                <span style="font-weight: 600;">{{ $step }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pro Tip Alert Box -->
                @if (!empty($guide['tips']))
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.825rem; color: #92400e; display: flex; align-items: center; gap: 0.6rem;">
                        <span style="font-size: 1.1rem;">💡</span>
                        <div><strong>نصيحة احترافية:</strong> {{ $guide['tips'] }}</div>
                    </div>
                @endif

            </div>

            <!-- Modal Footer -->
            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 20px 20px; display: flex; justify-content: flex-end;">
                <button
                    type="button"
                    @click="showGuide = false"
                    style="cursor: pointer; padding: 0.5rem 1.25rem; background: #e2e8f0; color: #334155; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 700; font-family: inherit; transition: all 0.2s;"
                    onmouseover="this.style.background='#cbd5e1';"
                    onmouseout="this.style.background='#e2e8f0';"
                >
                    إغلاق الدليل
                </button>
            </div>

        </div>
    </div>
</div>

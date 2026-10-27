@php
    $events = $this->getEvents();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 تقويم المجموعات
        </x-slot>

        <x-slot name="description">
            جميع أنشطة المجموعات التي تشرف عليها
        </x-slot>

        <div wire:ignore>
            <div id="supervisor-calendar"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/ar.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('supervisor-calendar');
    
    if (!calendarEl) return;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'اليوم',
            month: 'شهر',
            week: 'أسبوع',
            list: 'قائمة'
        },
        events: @js($events),
        height: 'auto',
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            let details = '';
            if (props.type === 'session') {
                details = `
                    <strong>المجموعة:</strong> ${props.group}
                    <strong>المدرس:</strong> ${props.instructor}
                    <strong>الحالة:</strong> ${props.status === 'completed' ? 'مكتملة' : 'قيد الانتظار'}
                `;
            } else if (props.type === 'exam') {
                details = `
                    <strong>المجموعة:</strong> ${props.group}
                    <strong>المدة:</strong> ${props.duration} دقيقة
                    <strong>الدرجة الكاملة:</strong> ${props.total_marks}
                `;
            } else if (props.type === 'assignment') {
                details = `
                    <strong>المجموعة:</strong> ${props.group}
                    <strong>الدرجة الكاملة:</strong> ${props.max_score}
                `;
            }
            
            alert(event.title + '\n\n' + details.replace(/<br>/g, '\n').replace(/<strong>/g, '\n').replace(/<\/strong>/g, ': '));
        },
        eventDidMount: function(info) {
            info.el.style.cursor = 'pointer';
        }
    });
    
    calendar.render();
});
</script>

<style>
#supervisor-calendar {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
}

.fc {
    font-family: inherit;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
}

.fc-button {
    background-color: #364FAC !important;
    border-color: #364FAC !important;
    text-transform: capitalize !important;
    border-radius: 8px !important;
}

.fc-button:hover {
    background-color: #293C85 !important;
    border-color: #293C85 !important;
}

.fc-button-active {
    background-color: #1E2D64 !important;
    border-color: #1E2D64 !important;
}

.fc-day-today {
    background-color: #fef3c7 !important;
}

.fc-event {
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 0.875rem;
}
</style>
@endpush

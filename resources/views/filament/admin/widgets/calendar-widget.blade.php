@php
    $events = $this->getEvents();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 التقويم العام
        </x-slot>

        <x-slot name="description">
            نظرة شاملة على جميع الأنشطة والفعاليات في المدرسة
        </x-slot>

        <div wire:ignore>
            <div id="admin-calendar"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/ar.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('admin-calendar');
    
    if (!calendarEl) return;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'اليوم',
            month: 'شهر',
            week: 'أسبوع',
            day: 'يوم',
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
                    <strong>الفرع:</strong> ${props.branch}
                    <strong>الحالة:</strong> ${props.status === 'completed' ? 'مكتملة' : 'قيد الانتظار'}
                `;
            } else if (props.type === 'exam') {
                details = `
                    <strong>المجموعة:</strong> ${props.group}
                    <strong>الفرع:</strong> ${props.branch}
                    <strong>المدة:</strong> ${props.duration} دقيقة
                    <strong>الدرجة الكاملة:</strong> ${props.total_marks}
                `;
            } else if (props.type === 'assignment') {
                details = `
                    <strong>المجموعة:</strong> ${props.group}
                    <strong>الفرع:</strong> ${props.branch}
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
#admin-calendar {
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
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
    text-transform: capitalize !important;
}

.fc-button:hover {
    background-color: #b91c1c !important;
    border-color: #b91c1c !important;
}

.fc-button-active {
    background-color: #991b1b !important;
    border-color: #991b1b !important;
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

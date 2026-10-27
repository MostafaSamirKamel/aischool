@php
    $events = $this->getEvents();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 التقويم - جدول الأعمال
        </x-slot>

        <div wire:ignore>
            <div id="instructor-calendar"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/ar.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('instructor-calendar');
    
    if (!calendarEl) return;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
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
                    <strong>المجموعة:</strong> ${props.group}<br>
                    <strong>الحالة:</strong> ${props.status === 'completed' ? 'مكتملة' : 'قيد الانتظار'}
                `;
            } else if (props.type === 'exam') {
                details = `
                    <strong>المدة:</strong> ${props.duration} دقيقة<br>
                    <strong>الدرجة الكاملة:</strong> ${props.total_marks}
                `;
            } else if (props.type === 'assignment') {
                details = `
                    <strong>الدرجة الكاملة:</strong> ${props.max_score}
                `;
            }
            
            // Show details in a simple alert (can be replaced with modal)
            alert(event.title + '\n\n' + details.replace(/<br>/g, '\n').replace(/<strong>/g, '').replace(/<\/strong>/g, ''));
        },
        eventDidMount: function(info) {
            info.el.style.cursor = 'pointer';
        }
    });
    
    calendar.render();
});
</script>

<style>
#instructor-calendar {
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
    background-color: #6366f1 !important;
    border-color: #6366f1 !important;
    text-transform: capitalize !important;
}

.fc-button:hover {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
}

.fc-button-active {
    background-color: #4338ca !important;
    border-color: #4338ca !important;
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

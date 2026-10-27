<?php
    $events = $this->getEvents();
?>

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
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('heading', null, []); ?> 
            📅 التقويم العام
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('description', null, []); ?> 
            نظرة شاملة على جميع الأنشطة والفعاليات في المدرسة
         <?php $__env->endSlot(); ?>

        <div wire:ignore>
            <div id="admin-calendar"></div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
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

<?php $__env->startPush('scripts'); ?>
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
        events: <?php echo \Illuminate\Support\Js::from($events)->toHtml() ?>,
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
<?php $__env->stopPush(); ?>
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\resources\views/filament/admin/widgets/calendar-widget.blade.php ENDPATH**/ ?>
/**
 * Shield Permissions Helper
 * يساعد في إدارة وعرض الصلاحيات بشكل صحيح في نظام Shield
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Shield Permissions Helper loaded');
    
    // التأكد من أن الأقسام المطوية تعمل بشكل صحيح
    initializeSections();
    
    // مراقبة التغييرات في DOM لإضافة event listeners للأقسام الجديدة
    observeDOMChanges();
});

/**
 * تهيئة الأقسام وضمان عملها بشكل صحيح
 */
function initializeSections() {
    const sections = document.querySelectorAll('.shield-resource-section');
    
    sections.forEach(section => {
        // التأكد من عرض المحتوى إذا كانت هناك صلاحيات محددة
        const checkboxes = section.querySelectorAll('input[type="checkbox"]:checked');
        
        if (checkboxes.length > 0) {
            // فتح القسم إذا كانت هناك صلاحيات محددة
            expandSection(section);
        }
    });
}

/**
 * فتح قسم معين
 */
function expandSection(section) {
    const header = section.querySelector('[x-bind\\:aria-expanded]');
    if (header && header.getAttribute('aria-expanded') === 'false') {
        const toggleButton = section.querySelector('button[x-on\\:click]');
        if (toggleButton) {
            toggleButton.click();
        }
    }
}

/**
 * مراقبة التغييرات في DOM
 */
function observeDOMChanges() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('shield-resource-section')) {
                        initializeSections();
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/**
 * التأكد من تحديث الصلاحيات بشكل صحيح عند استخدام Livewire
 */
if (window.Livewire) {
    Livewire.hook('message.processed', (message, component) => {
        // إعادة تهيئة الأقسام بعد كل تحديث من Livewire
        setTimeout(() => {
            initializeSections();
        }, 100);
    });
}

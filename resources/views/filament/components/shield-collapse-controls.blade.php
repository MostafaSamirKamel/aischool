<div 
    style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 16px; margin-bottom: 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;"
    class="dark:!bg-gray-800 dark:!border-gray-700"
    x-data="{
        expandAll() {
            document.querySelectorAll('.fi-section').forEach(el => {
                if (el._x_dataStack) {
                    el._x_dataStack.forEach(scope => {
                        if (scope && typeof scope.isCollapsed !== 'undefined') {
                            scope.isCollapsed = false;
                        }
                    });
                }
            });
        },
        collapseAll() {
            document.querySelectorAll('.fi-section').forEach(el => {
                if (el._x_dataStack) {
                    el._x_dataStack.forEach(scope => {
                        if (scope && typeof scope.isCollapsed !== 'undefined') {
                            scope.isCollapsed = true;
                        }
                    });
                }
            });
        },
        expandGroupsOnly() {
            document.querySelectorAll('.fi-shield-group-section, .fi-shield-group-section .fi-section').forEach(el => {
                if (el._x_dataStack) {
                    el._x_dataStack.forEach(scope => {
                        if (scope && typeof scope.isCollapsed !== 'undefined') {
                            scope.isCollapsed = false;
                        }
                    });
                }
            });
            document.querySelectorAll('.fi-shield-resource-section, .fi-shield-resource-section .fi-section').forEach(el => {
                if (el._x_dataStack) {
                    el._x_dataStack.forEach(scope => {
                        if (scope && typeof scope.isCollapsed !== 'undefined') {
                            scope.isCollapsed = true;
                        }
                    });
                }
            });
        }
    }"
>
    <!-- Right side: Title & Icon in one unified line -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background-color: #e0f2fe; border: 1px solid #bae6fd; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;" class="dark:!bg-gray-700 dark:!border-gray-600">
            🎛️
        </div>
        <div>
            <div style="font-weight: 800; font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 8px;" class="dark:!text-white">
                <span>التحكم في عرض وطي مجموعات الصلاحيات</span>
                <span style="font-size: 10px; font-weight: 700; background-color: #0284c7; color: #ffffff; padding: 2px 8px; border-radius: 20px;">
                    عرض سريع
                </span>
            </div>
            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;" class="dark:!text-gray-400">
                يمكنك طي أو توسيع أقسام القوائم وكروت النماذج التابعة لها بضغطة زر واحدة
            </div>
        </div>
    </div>

    <!-- Left side: Buttons aligned horizontally -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
        <!-- Expand All -->
        <button 
            type="button" 
            @click="expandAll()" 
            style="background-color: #0284c7; color: #ffffff; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
            onmouseover="this.style.backgroundColor='#0369a1'" 
            onmouseout="this.style.backgroundColor='#0284c7'"
        >
            <span>🔽 توسيع الكل (فتح الجميع)</span>
        </button>

        <!-- Collapse All -->
        <button 
            type="button" 
            @click="collapseAll()" 
            style="background-color: #ffffff; color: #334155; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; border: 1px solid #cbd5e1; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
            class="dark:!bg-gray-700 dark:!text-gray-200 dark:!border-gray-600"
            onmouseover="this.style.backgroundColor='#f1f5f9'" 
            onmouseout="this.style.backgroundColor='#ffffff'"
        >
            <span>🔼 طي الكل (إغلاق الجميع)</span>
        </button>

        <!-- Expand Groups Only -->
        <button 
            type="button" 
            @click="expandGroupsOnly()" 
            style="background-color: #0d9488; color: #ffffff; font-weight: 700; font-size: 12px; padding: 7px 14px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
            onmouseover="this.style.backgroundColor='#0f766e'" 
            onmouseout="this.style.backgroundColor='#0d9488'"
        >
            <span>📁 فتح الأقسام الرئيسية فقط</span>
        </button>
    </div>
</div>

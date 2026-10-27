# الصلاحيات الدقيقة وعزل المستأجرين والفروع
## AI SCHOOL — PERMISSIONS & MULTI-TENANT ISOLATION REPORT

---

## 1. منظومة الصلاحيات الدقيقة (Granular Permissions Matrix)

تم إرساء حزمة صلاحيات دقيقة ومستقلة لإدارة عمليات المتابعة اليومية:

| كود الصلاحية (Permission) | التسمية والوظيفة | الأدوار الافتراضية الممنوحة |
|---|---|---|
| `view_daily_follow_up_dashboard` | الدخول إلى مركز المتابعة اليومي ورؤية المؤشرات | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `view_student_academic_monitoring` | مطالعة الإنذارات الأكاديمية (الغياب، الواجبات، الامتحانات) | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `view_student_contact_numbers` | رؤية أرقام هواتف الطالب وولي الأمر | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `create_student_followup_task` | الإنشاء اليدوي لحالة متابعة جديدة لطالب | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `record_parent_contact` | تسجيل محاولة تواصل في سجل الاتصالات الزمني | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `reassign_follow_up_case` | إعادة تكليف حالة المتابعة لموظف آخر مع توثيق السبب | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `view_daily_follow_up_reports` | استعراض التقرير الإحصائي والتشغيلي اليومي | `super_admin`, `admin`, `branch_manager`, `supervisor`, `secretary` |
| `view_student_financial_follow_up` | الاطلاع على إنذارات وأقساط الطلاب المتأخرة للقراءة فقط | **مستقلة تماماً:** `super_admin`, `admin` وموظفو المتابعة الممنوحون إياها صراحة |

---

## 2. عزل الفروع والمستأجرين (Multi-Tenant & Branch Isolation)
- المنظومة تعتمد على سمة `BelongsToBranch` في كافة النماذج (`Student`, `Group`, `Assignment`, `Exam`, `StudentFollowUpTask`, `StudentFollowUpContact`, `StudentFollowUpTaskReassignment`).
- كل استعلام في `AcademicFollowUpService` يفرض شرط `tenant_id`:
  - لا يستطيع موظف فرع القاهرة الاطلاع على طلاب أو إنذارات أو سجلات فرع دمنهور أو أي فرع آخر.
  - حتى في حال محاولة تمرير معرّف مجموعة من فرع آخر عبر الواجهة أو Livewire، يتم رفض الاستعلام فوراً.

---

## 3. سرية وخصوصية أرقام الهواتف (Phone Numbers Protection)
- يُفصل تماماً بين رقم هاتف ولي الأمر ورقم هاتف الطالب.
- يتم جلب رقم ولي الأمر الأساسي فقط عبر علاقة `is_primary = true` في جدول `student_parents`.
- في حال عدم امتلاك المستخدم لصلاحية `view_student_contact_numbers`، يتم حجب الأرقام واستبدالها بالقيمة `— (محجوب)`.
- الحفاظ التام على الأصفار البادئة في أرقام الهواتف وعدم اختراع أي أرقام بديلة غير مسجلة.

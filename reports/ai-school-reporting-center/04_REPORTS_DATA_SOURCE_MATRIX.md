# 04 — مصفوفة مصادر البيانات للتقارير (Reports Data Source Matrix)

## ربط تقارير المركز الموحد بجداول وقواعد البيانات الفعلية

الموقع: `reports/ai-school-reporting-center/04_REPORTS_DATA_SOURCE_MATRIX.md`

---

| التقرير | الجداول والنماذج الأساسية | حقول التصفية والعزل | الأبعاد الزمنية المعتمدة | ضوابط الحماية والصلاحيات |
|---|---|---|---|---|
| **نظرة تنفيذية شاملة** | `students`, `enrollments`, `payments`, `installments`, `vaults`, `attendance_sessions` | `tenant_id`, `group_id`, `course_id` | `period_start`, `period_end`, `as_of_date` | مقتصر على الإدارة، مدير الفرع، رؤساء الأقسام المصرح لهم |
| **نمو الطلاب والتسجيل** | `students`, `users`, `tenants` | `students.tenant_id`, `governorate_id` | `students.created_at` | عزل الفروع، التحقق من هوية الطالب الفريد |
| **الاشتراكات والتجديدات** | `enrollments`, `enrollment_courses`, `courses`, `groups` | `enrollments.tenant_id`, `courses.id`, `groups.id` | `enrollment_date` | حجب اشتراكات الفروع الأخرى ما لم يكن super_admin |
| **التحصيلات النقدية** | `payments`, `enrollments`, `vaults`, `users` | `payments.tenant_id`, `vault_id`, `received_by` | `payment_date` | صلاحيات الشؤون المالية والمحاسبين |
| **الأقساط والمديونيات** | `installments`, `enrollments`, `students` | `installments.tenant_id`, `status` | `due_date`, `as_of_date` | تفادي تكرار الحساب مع المقبوضات والمقدمات |
| **الخزائن وحركة النقد** | `vaults`, `payments`, `expenses`, `salary_payments`, `vault_transfers` | `vaults.tenant_id`, `vaults.id` | `payment_date`, `expense_date`, `transfer_date` | صلاحية `view_vault_transactions` وعزل أمناء الخزائن |
| **الإيرادات والمصروفات وقائمة الدخل** | `expenses`, `revenues`, `enrollments`, `instructor_payments` | `tenant_id`, `category_id` | `expense_date`, `revenue_date`, `enrollment_date` | إبراز إشعار السياسة المحاسبية وعدم الخلط مع التدفق النقدي |
| **مرتبات وأتعاب المهندسين** | `instructor_payments`, `salary_payments`, `instructors` | `tenant_id`, `instructor_id` | `period_start`, `payment_date` | الفصل التام بين الاستحقاق والصرف وتقييمات الإشراف |
| **الإلغاء والتسوية والاسترداد** | `enrollment_cancellations`, `enrollments`, `vaults` | `tenant_id`, `settlement_type`, `refund_status` | `cancelled_at`, `refunded_at` | صلاحية تسوية الإلغاءات وإدارة الالتزامات المالية |
| **الأداء الأكاديمي والامتحانات** | `exams`, `exam_attempts`, `assignments`, `assignment_submissions`, `session_schedules` | `tenant_id`, `course_id`, `group_id` | `exam_date`, `due_date`, `published_at` | استبعاد مسودات الدرجات وحصر تنبيه الدرجات المنخفضة في أقل من 50% |
| **الإشراف التربوي والتقييمات** | `supervisory_observations`, `instructor_evaluations`, `supervision_reports` | `tenant_id`, `supervisor_id`, `instructor_id` | `created_at`, `evaluation_period`, `period_key` | توقيع المشرف التربوي شرط ملزم لنشر التقارير الإشرافية |
| **العمليات التشغيلية للموظفين** | `users`, `payments`, `students`, `student_follow_up_tasks`, `student_follow_up_contacts` | `tenant_id`, `user_id`, `role` | `created_at`, `payment_date`, `contacted_at` | مؤشرات تخصصية بحسب طبيعة الوظيفة دون رقم تقييم عام وهمي |
| **مقارنة الفروع** | `tenants`, `students`, `enrollments`, `payments`, `groups` | `tenant_id` | فترات موحدة متطابقة | مقتصر على الإدارة العامة ومسؤولي الإدارة المركزية |
| **التنبيهات الإدارية والاستثناءات** | استعلامات كشف الاستثناءات من الجداول النشطة | `tenant_id` وحواجز الأمان | لحظية وفقاً لقواعد الحدود المعيارية | للعرض والرقابة فقط دون أي تنفيذ مالي أو مساس تلقائي بالسجلات |

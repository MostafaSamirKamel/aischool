# تقرير دقة مؤشرات وتقارير المتابعة اليومية
## AI SCHOOL — DAILY REPORT METRICS & COUNTER ACCURACY (QA-03)
**التاريخ والوقت:** 2026-10-20T23:28:30+03:00  
**المسؤول:** Quality Assurance Lead & Educational Metrics Auditor  
**المشكلة المعالجة:** توضيح وفصل دلالات مؤشر "الحالات الجديدة اليوم" وضبط الحسابات الزمنية

---

## 1. تشخيص الخلاف الإحصائي في مؤشر "الحالات الجديدة اليوم"

أشار التقرير المستقل إلى احتمال وجود لبس إحصائي في استعلام تقرير المتابعة اليومية:
- **السيناريو:**
  تم إنشاء 5 حالات متابعة اليوم من قبل السكرتارية.
  تم التواصل بنجاح مع 3 حالات منها، فتحولت حالتها إلى `تم التواصل`.
  تبقت حالتان اثنتان في حالة `جديدة`.
- **المشكلة السابقة:**
  كان الاستعلام يستعلم:
  `whereBetween('created_at', [$startOfDay, $endOfDay])->whereIn('status', ['new', 'pending'])`
  مما كان يعطي الرقم (2).
  هذا الرقم يعبر بدقة عن: **"الحالات الجديدة التي ما زالت في انتظار التواصل"**، ولكنه قد يلتبس مع: **"إجمالي الحالات المنشأة اليوم"** وهو (5).

---

## 2. المعالجة الهندسية المعتمدة (Architectural Solution)

دون المساس بحالتي المتابعة المعتمدتين (`جديدة` و `تم التواصل`)، تم توفير كلا المؤشرين الإحصائيين بدقة وتسمية واضحة ومستقلة:

### أ. المؤشر الأول: إجمالي الحالات المنشأة اليوم (`created_today_count`)
- **التعريف:** كافة حالات المتابعة التي قام الموظفون بإنشائها خلال تاريخ اليوم، بغض النظر عن حالتها الحالية (سواء بقيت جديدة أو تم التواصل معها لاحقاً خلال اليوم).
- **الاستعلام البرمجي:**
  ```php
  $createdTodayCount = StudentFollowUpTask::where('tenant_id', $branchId)
      ->whereBetween('created_at', [$startOfDay, $endOfDay])
      ->whereIn('group_id', $authorizedGroupIds)
      ->when($employeeId, fn($q) => $q->where('assigned_to', $employeeId))
      ->count();
  ```

### ب. المؤشر الثاني: الحالات الجديدة قيد الانتظار (`new_cases_count`)
- **التعريف:** الحالات المنشأة اليوم التي ما زالت حتى لحظة التقرير في حالة `جديدة` ولم يتم التواصل معها بنجاح بعد.
- **الاستعلام البرمجي:**
  ```php
  $newCasesCount = (clone $createdTodayQuery)
      ->whereIn('status', [StudentFollowUpTask::STATUS_NEW, 'pending'])
      ->count();
  ```

### ج. المؤشر الثالث: الحالات التي تم التواصل معها اليوم (`contacted_cases_count`)
- **التعريف:** الحالات التي تم إنجاز التواصل معها وتوثيقه بنجاح خلال تاريخ اليوم.
- **الاستعلام البرمجي:**
  ```php
  $contactedCasesCount = StudentFollowUpTask::where('tenant_id', $branchId)
      ->whereIn('status', [StudentFollowUpTask::STATUS_CONTACTED, 'completed'])
      ->whereBetween('resolved_at', [$startOfDay, $endOfDay])
      ->whereIn('group_id', $authorizedGroupIds)
      ->when($employeeId, fn($q) => $q->where('assigned_to', $employeeId))
      ->count();
  ```

---

## 3. التوافق الزمني والمنطقة الزمنية (Timezone Alignment)

- يتم استخدام التوقيت المحلي للمدرسة المعتمد في إعدادات التطبيق:
  `config('app.timezone', 'Africa/Cairo')`.
- تحويل تاريخ التقرير باستخدام Carbon مع تعيين بداية ونهاية اليوم الفعلي:
  `$startOfDay = $date->copy()->startOfDay();`
  `$endOfDay = $date->copy()->endOfDay();`
- منع أي انحراف في تواريخ السجلات الناتجة عن فروق توقيت خادم قواعد البيانات (UTC vs Local).

---

## 4. أدلة الاختبار والتحقق (Test Verification)

تم إثبات صحة وتوافق هذه الحسابات في الاختبار المعتمد:
`DailyStudentFollowUpCenterTest::test_20_daily_report_accuracy_for_cases_contact_attempts_and_alerts`
- تم إنشاء حالتين اليوم:
  - الأولى: تم التواصل معها بنجاح -> تحولت إلى `تم التواصل`.
  - الثانية: تم الاتصال بها ولم يرد ولي الأمر -> بقيت `جديدة`.
- نتيجة التقرير المحققة:
  - `new_cases_count`: **1** (الحالة المتبقية قيد الانتظار).
  - `contacted_cases_count`: **1** (الحالة التي تم التواصل معها).
  - `total_contact_attempts`: **2** (كافة المحاولات المسجلة).
  - `successful_contacts`: **1**.
  - `unsuccessful_contacts`: **1**.
- النتيجة: **تطابق حسابي وإحصائي بنسبة 100% دون أي لبس في الأرقام.**

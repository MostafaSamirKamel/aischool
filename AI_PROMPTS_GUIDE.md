# 🚀 خطة الإكمال التفصيلية - AI Vibe Coding Prompts

## 📋 نظرة عامة
هذا الملف يحتوي على **Prompts جاهزة** لمخاطبة الذكاء الاصطناعي (GitHub Copilot, ChatGPT, Claude) لإكمال الميزات الناقصة.

---

## 🎯 المرحلة 1: الأساسيات الضرورية (HIGH PRIORITY)

### ✅ Prompt 1: إنشاء نظام إدارة المدرسين (Instructor Management)

```markdown
أريد إنشاء نظام إدارة المدرسين/المدربين (Instructors) في مشروع Laravel + Filament.

**السياق الحالي:**
- المشروع: AI School Management System
- Framework: Laravel 12 + Filament 4
- Database: MySQL
- Multi-tenant: نعم (TenantScope موجود في app/Models/Scopes/)
- الصلاحيات: Spatie Permission + Filament Shield

**المطلوب:**

1. **إنشاء Model:**
   ```php
   // Path: app/Models/Instructor.php
   
   Fields:
   - id (primary key)
   - user_id (FK to users) - Many-to-One
   - tenant_id (FK to tenants) - Many-to-One
   - department_id (FK to departments) - Many-to-One
   - employee_code (string, unique)
   - specialization (string)
   - bio (text, nullable)
   - qualifications (json, nullable)
   - years_of_experience (integer)
   - status (enum: active, inactive, on_leave)
   - hired_date (date)
   - contract_type (enum: full_time, part_time, contract)
   - salary (decimal, nullable)
   - phone (string, nullable)
   - emergency_contact (string, nullable)
   - timestamps
   - soft deletes
   
   Relationships:
   - belongsTo: User, Tenant, Department
   - hasMany: Group (as instructor)
   - hasManyThrough: Students (through Groups)
   
   Scopes:
   - Apply TenantScope globally
   
   Mutators:
   - qualifications as array cast
   ```

2. **إنشاء Migration:**
   ```php
   // Path: database/migrations/XXXX_create_instructors_table.php
   
   - Create table with all fields
   - Add foreign keys with onDelete cascade
   - Add indexes on: tenant_id, user_id, department_id, employee_code
   - Set charset utf8mb4
   ```

3. **إنشاء Filament Resource:**
   ```php
   // Path: app/Filament/Admin/Resources/Instructors/InstructorResource.php
   
   Form Schema:
   - Section 1: "المعلومات الأساسية"
     * Select: user_id (relationship, searchable)
     * TextInput: employee_code (required, unique)
     * Select: department_id (relationship)
     * TextInput: specialization
     
   - Section 2: "المعلومات المهنية"
     * Textarea: bio
     * Repeater: qualifications (title, institution, year)
     * TextInput: years_of_experience (numeric)
     * DatePicker: hired_date
     
   - Section 3: "بيانات العقد"
     * Select: contract_type (enum options)
     * TextInput: salary (numeric, suffix: ج.م)
     * Select: status (enum options)
     
   - Section 4: "معلومات الاتصال"
     * TextInput: phone
     * TextInput: emergency_contact
   
   Table Columns:
   - TextColumn: employee_code (searchable, sortable)
   - TextColumn: user.name (label: الاسم)
   - TextColumn: department.name (label: القسم)
   - TextColumn: specialization (searchable)
   - TextColumn: years_of_experience (label: سنوات الخبرة)
   - BadgeColumn: status (colors: active=success, inactive=danger, on_leave=warning)
   - TextColumn: hired_date (date format)
   
   Filters:
   - SelectFilter: department_id
   - SelectFilter: status
   - SelectFilter: contract_type
   - Filter: hired_date (date range)
   
   Actions:
   - View
   - Edit
   - Delete (with confirmation)
   
   Bulk Actions:
   - Delete
   - Update status
   
   Navigation:
   - Group: "الموارد البشرية"
   - Icon: heroicon-o-user-group
   - Sort: 20
   ```

4. **إنشاء Factory:**
   ```php
   // Path: database/factories/InstructorFactory.php
   
   - Generate fake data
   - Link to existing users, tenants, departments
   - Random qualifications (2-4 items)
   ```

5. **تحديث Group Model:**
   ```php
   // Path: app/Models/Group.php
   
   Add:
   - instructor_id field to migration
   - belongsTo Instructor relationship
   ```

6. **إنشاء Seeder:**
   ```php
   // Path: database/seeders/InstructorSeeder.php
   
   - Create 10-15 instructors
   - Distribute across tenants
   - Assign to departments
   ```

7. **الترجمات:**
   ```php
   // Path: lang/ar/filament/admin/instructor.php
   
   Create translation file with all labels
   
   // Path: lang/en/filament/admin/instructor.php
   
   English version
   ```

8. **Permissions:**
   ```bash
   بعد الإنشاء، قم بتشغيل:
   php artisan shield:generate --all
   ```

**التأكد من:**
- ✅ Multi-tenancy: TenantScope applied
- ✅ All relationships working
- ✅ Form validation rules
- ✅ Arabic translations
- ✅ Proper permissions
- ✅ Seeders create test data

**ابدأ بإنشاء Model و Migration أولاً، ثم انتظر تأكيدي قبل المتابعة.**
```

---

### ✅ Prompt 2: إنشاء نظام الحضور والغياب (Attendance System)

```markdown
أريد إنشاء نظام الحضور والغياب (Attendance) في مشروع Laravel + Filament.

**السياق:**
- المشروع: AI School Management System
- Multi-tenant: نعم
- Models موجودة: Student, Group, User, Tenant

**المطلوب:**

1. **إنشاء Model:**
   ```php
   // Path: app/Models/Attendance.php
   
   Fields:
   - id
   - tenant_id (FK)
   - student_id (FK)
   - group_id (FK)
   - date (date)
   - status (enum: present, absent, late, excused)
   - check_in_time (time, nullable)
   - check_out_time (time, nullable)
   - notes (text, nullable)
   - recorded_by (FK to users)
   - timestamps
   
   Relationships:
   - belongsTo: Tenant, Student, Group, User (recorded_by)
   
   Scopes:
   - TenantScope
   
   Unique Index:
   - (student_id, group_id, date) - لا يمكن تسجيل حضور مرتين في نفس اليوم
   ```

2. **إنشاء Migration:**
   - Create table
   - Foreign keys
   - Unique constraint
   - Indexes

3. **إنشاء Filament Resource:**
   ```php
   Form Schema:
   - Section: "تسجيل الحضور"
     * Select: group_id (preload students)
     * DatePicker: date (default: today)
     * Repeater: students_attendance
       - Hidden: student_id
       - Placeholder: student_name (read-only)
       - Select: status (present/absent/late/excused)
       - TimePicker: check_in_time
       - TimePicker: check_out_time
       - Textarea: notes
   
   Table Columns:
   - TextColumn: date (sortable)
   - TextColumn: student.user.name
   - TextColumn: group.name
   - BadgeColumn: status (colored)
   - TextColumn: check_in_time
   - TextColumn: recorded_by.name
   
   Filters:
   - SelectFilter: group_id
   - SelectFilter: status
   - Filter: date (date range)
   
   Actions:
   - Edit
   - View
   - Delete
   
   Custom Actions:
   - BulkMarkPresent (لتسجيل حضور الكل)
   - BulkMarkAbsent
   
   Navigation:
   - Group: "الحضور والغياب"
   - Icon: heroicon-o-calendar-days
   ```

4. **إنشاء Widget:**
   ```php
   // Path: app/Filament/Admin/Widgets/AttendanceStats.php
   
   Stats:
   - حضور اليوم
   - غياب اليوم
   - متأخرون اليوم
   - نسبة الحضور (%)
   
   Charts:
   - Weekly attendance trend
   - Attendance by group
   ```

5. **إنشاء Custom Page:**
   ```php
   // Path: app/Filament/Admin/Pages/DailyAttendance.php
   
   - Quick attendance marking page
   - Select group
   - Show all students in a table
   - Mark attendance with checkboxes
   - Save all at once
   ```

6. **الترجمات:**
   - lang/ar/filament/admin/attendance.php
   - lang/en/filament/admin/attendance.php

7. **Factory & Seeder:**
   - Create test attendance records
   - Distribute across days and groups

**ابدأ بإنشاء Model و Migration.**
```

---

### ✅ Prompt 3: إنشاء نظام الدرجات والامتحانات (Grading System)

```markdown
أريد إنشاء نظام الدرجات والامتحانات (Exams & Grades) في Laravel + Filament.

**السياق:**
- المشروع: AI School Management System
- Multi-tenant: نعم
- Models موجودة: Student, Course, Group, Instructor

**المطلوب:**

1. **إنشاء Model: Exam**
   ```php
   // Path: app/Models/Exam.php
   
   Fields:
   - id
   - tenant_id (FK)
   - course_id (FK)
   - group_id (FK)
   - instructor_id (FK)
   - title (string)
   - description (text, nullable)
   - type (enum: quiz, midterm, final, assignment, project)
   - exam_date (datetime)
   - duration (integer, minutes)
   - total_marks (decimal)
   - passing_marks (decimal)
   - status (enum: scheduled, in_progress, completed, cancelled)
   - instructions (text, nullable)
   - timestamps
   - soft deletes
   
   Relationships:
   - belongsTo: Tenant, Course, Group, Instructor
   - hasMany: Grade
   
   Scopes:
   - TenantScope
   ```

2. **إنشاء Model: Grade**
   ```php
   // Path: app/Models/Grade.php
   
   Fields:
   - id
   - tenant_id (FK)
   - exam_id (FK)
   - student_id (FK)
   - enrollment_id (FK)
   - marks_obtained (decimal)
   - grade (enum: A+, A, B+, B, C+, C, D, F)
   - percentage (decimal, calculated)
   - remarks (text, nullable)
   - graded_by (FK to users)
   - graded_at (datetime)
   - timestamps
   
   Relationships:
   - belongsTo: Tenant, Exam, Student, Enrollment, User (graded_by)
   
   Scopes:
   - TenantScope
   
   Unique Index:
   - (exam_id, student_id)
   
   Mutators:
   - Calculate percentage automatically
   - Auto-assign grade based on percentage
   ```

3. **إنشاء ExamResource:**
   ```php
   Form:
   - Section: "معلومات الامتحان"
     * TextInput: title
     * Select: course_id
     * Select: group_id (depends on course)
     * Select: instructor_id
     * Select: type
     
   - Section: "التفاصيل"
     * Textarea: description
     * DateTimePicker: exam_date
     * TextInput: duration (suffix: دقيقة)
     * TextInput: total_marks
     * TextInput: passing_marks
     * Select: status
     
   - Section: "التعليمات"
     * RichEditor: instructions
   
   Table:
   - TextColumn: title
   - TextColumn: course.name
   - TextColumn: group.name
   - BadgeColumn: type
   - TextColumn: exam_date
   - TextColumn: total_marks
   - BadgeColumn: status
   - TextColumn: grades_count (count)
   
   Actions:
   - View grades
   - Enter grades (custom action)
   - Publish results
   ```

4. **إنشاء GradeResource:**
   ```php
   Form:
   - Section: "معلومات الدرجة"
     * Select: exam_id (with details)
     * Select: student_id (from exam's group)
     * TextInput: marks_obtained (numeric, max: exam.total_marks)
     * Select: grade (auto-calculated, can override)
     * Textarea: remarks
   
   Table:
   - TextColumn: exam.title
   - TextColumn: student.user.name
   - TextColumn: marks_obtained
   - TextColumn: exam.total_marks (label: من)
   - TextColumn: percentage (suffix: %)
   - BadgeColumn: grade (colored: A+=success, F=danger)
   - TextColumn: graded_by.name
   - TextColumn: graded_at
   
   Filters:
   - SelectFilter: exam_id
   - SelectFilter: grade
   - SelectFilter: course (through exam)
   
   Bulk Actions:
   - Publish grades
   - Send notifications
   ```

5. **إنشاء Custom Pages:**
   ```php
   // a) GradeEntryPage
   - Select exam
   - Show all students in group
   - Enter marks in a table format
   - Save all at once
   - Auto-calculate grades
   
   // b) StudentTranscriptPage
   - Show all grades for a student
   - Calculate GPA
   - Show course-wise breakdown
   - Generate PDF
   ```

6. **إنشاء Widgets:**
   ```php
   // a) ExamStats
   - Upcoming exams
   - Completed exams
   - Average class performance
   
   // b) GradeDistribution
   - Chart showing grade distribution (A, B, C, etc.)
   - Pass/Fail ratio
   ```

7. **إنشاء Helper Methods:**
   ```php
   // In Grade Model
   - calculateGPA()
   - getGradePoints()
   - isPass()
   
   // In Student Model
   - getOverallGPA()
   - getTranscript()
   ```

8. **Notifications:**
   ```php
   // Create notifications
   - GradePublished (to students)
   - ExamScheduled (to students)
   - LowPerformanceAlert (to parents)
   ```

9. **الترجمات:**
   - lang/ar/filament/admin/exam.php
   - lang/ar/filament/admin/grade.php
   - lang/en/filament/admin/exam.php
   - lang/en/filament/admin/grade.php

10. **Factory & Seeder:**
    - Create 20+ sample exams
    - Create grades for enrolled students
    - Realistic grade distribution

**ابدأ بإنشاء Model و Migration للـ Exam أولاً.**
```

---

## 🎯 المرحلة 2: التحسينات (MEDIUM PRIORITY)

### ✅ Prompt 4: إنشاء نظام الجداول الدراسية (Schedule/Timetable)

```markdown
أريد إنشاء نظام الجداول الدراسية (Schedule/Timetable) في Laravel + Filament.

**المطلوب:**

1. **Model: Schedule**
   ```php
   Fields:
   - id
   - tenant_id
   - group_id
   - course_id
   - instructor_id
   - classroom_id
   - day_of_week (1-7, 1=Monday)
   - start_time (time)
   - end_time (time)
   - session_type (enum: lecture, lab, tutorial, practical)
   - recurrence (enum: weekly, biweekly, custom)
   - effective_from (date)
   - effective_to (date, nullable)
   - status (enum: active, cancelled, rescheduled)
   - timestamps
   
   Relationships:
   - belongsTo: Tenant, Group, Course, Instructor, Classroom
   
   Validation:
   - No overlapping schedules for same instructor
   - No overlapping schedules for same classroom
   - end_time must be after start_time
   ```

2. **Resource Features:**
   - Calendar view for weekly schedule
   - Conflict detection
   - Drag & drop scheduling
   - Print-friendly timetable
   - Export to PDF

3. **Custom Pages:**
   - TeacherSchedulePage
   - StudentSchedulePage
   - ClassroomUsagePage

4. **Widgets:**
   - TodayScheduleWidget
   - ClassroomUtilizationWidget

**ابدأ بالتنفيذ.**
```

---

### ✅ Prompt 5: تحسين نظام التقارير (Enhanced Reporting)

```markdown
أريد تحسين نظام التقارير الموجود بإضافة:

1. **PDF Export:**
   - استخدام Laravel DomPDF أو Snappy
   - قوالب احترافية
   - Header & Footer
   - Watermark

2. **Excel Export:**
   - استخدام Maatwebsite/Laravel-Excel
   - Multiple sheets
   - Formulas & calculations
   - Formatting

3. **Advanced Filters:**
   - Date range picker
   - Multi-select filters
   - Search across relationships
   - Saved filter presets

4. **Scheduled Reports:**
   - Daily/Weekly/Monthly reports
   - Email delivery
   - Laravel Scheduler integration

5. **Report Dashboard:**
   - Quick access to common reports
   - Recently generated reports
   - Favorite reports

**ابدأ بـ PDF Export للتقارير الموجودة.**
```

---

### ✅ Prompt 6: نظام الرسائل والإعلانات (Communication System)

```markdown
أريد إنشاء نظام رسائل داخلي وإعلانات في Laravel + Filament.

**المطلوب:**

1. **Model: Message**
   ```php
   Fields:
   - id
   - tenant_id
   - sender_id (FK to users)
   - recipient_id (FK to users)
   - subject
   - body (text)
   - is_read (boolean)
   - read_at (datetime, nullable)
   - parent_id (FK for threading, nullable)
   - timestamps
   ```

2. **Model: Announcement**
   ```php
   Fields:
   - id
   - tenant_id
   - title
   - content (rich text)
   - author_id (FK to users)
   - target_audience (enum: all, students, teachers, parents)
   - priority (enum: low, normal, high, urgent)
   - published_at (datetime)
   - expires_at (datetime, nullable)
   - is_pinned (boolean)
   - timestamps
   ```

3. **Features:**
   - Inbox/Sent pages
   - Compose message
   - Reply & Forward
   - Mark as read/unread
   - Delete messages
   - Search messages
   - Notifications integration

4. **Widgets:**
   - UnreadMessagesWidget
   - RecentAnnouncementsWidget

**ابدأ بإنشاء Models و Migrations.**
```

---

## 🎯 المرحلة 3: الإضافات الاختيارية (LOW PRIORITY)

### ⭕ Prompt 7: نظام المستندات (Document Management)

```markdown
أريد إضافة نظام إدارة المستندات في Laravel + Filament.

**المطلوب:**
- Upload files
- Organize in folders/categories
- Link to students/courses/exams
- Version control
- Access permissions
- File preview
- Search & filter

**ابدأ عند الحاجة.**
```

---

### ⭕ Prompt 8: نظام المكتبة (Library Management)

```markdown
أريد إضافة نظام مكتبة بسيط.

**Models:**
- Book (title, author, isbn, quantity, etc.)
- BookIssue (book_id, student_id, issue_date, return_date, status)

**Features:**
- Book catalog
- Issue books to students
- Track returns
- Calculate fines for late returns
- Search & filters

**ابدأ عند الحاجة.**
```

---

### ⭕ Prompt 9: API Development

```markdown
أريد إضافة RESTful API للنظام.

**Features:**
- Laravel Sanctum authentication
- API Resources for all models
- Rate limiting
- API documentation (Swagger/OpenAPI)
- Mobile-friendly endpoints

**Endpoints:**
- Auth: login, logout, register
- Students: CRUD operations
- Attendance: mark, view
- Grades: view
- Schedules: view
- Announcements: list

**ابدأ عند الحاجة.**
```

---

## 📊 جدول الأولويات

| #  | الميزة | الأولوية | الوقت المتوقع | الحالة |
|----|--------|---------|---------------|--------|
| 1  | Instructor Management | 🔴 HIGH | 6-8 ساعات | ❌ Pending |
| 2  | Attendance System | 🔴 HIGH | 8-10 ساعات | ❌ Pending |
| 3  | Grading System | 🔴 HIGH | 10-12 ساعة | ❌ Pending |
| 4  | Schedule Management | 🟡 MEDIUM | 6-8 ساعات | ❌ Pending |
| 5  | Enhanced Reports | 🟡 MEDIUM | 4-6 ساعات | ❌ Pending |
| 6  | Communication | 🟡 MEDIUM | 6-8 ساعات | ❌ Pending |
| 7  | Document Management | 🟢 LOW | 4-6 ساعات | ⭕ Optional |
| 8  | Library System | 🟢 LOW | 4-6 ساعات | ⭕ Optional |
| 9  | API Development | 🟢 LOW | 8-10 ساعات | ⭕ Optional |

**إجمالي الوقت المتوقع:** 4-6 أيام عمل

---

## 🎯 كيفية الاستخدام

1. **انسخ Prompt المطلوب**
2. **الصقه في GitHub Copilot Chat أو أي AI assistant**
3. **اتبع التعليمات خطوة بخطوة**
4. **اختبر كل ميزة بعد إنشائها**
5. **شغل `php artisan shield:generate --all` بعد كل resource جديد**
6. **أضف بيانات تجريبية في Seeders**

---

## ✅ Checklist بعد كل ميزة

- [ ] Model created with all fields
- [ ] Migration run successfully
- [ ] Relationships working
- [ ] TenantScope applied
- [ ] Filament Resource created
- [ ] Form validation working
- [ ] Table filters working
- [ ] Permissions generated
- [ ] Arabic translations added
- [ ] Factory & Seeder created
- [ ] Test data generated
- [ ] Manual testing done

---

**تم الإعداد بواسطة:** GitHub Copilot  
**التاريخ:** 18 ديسمبر 2025  
**الإصدار:** v1.0  
**الغرض:** Vibe Coding - AI-Assisted Development

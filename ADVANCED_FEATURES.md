# Advanced Features Documentation

## 📋 Table of Contents
1. [Custom Forms](#custom-forms)
2. [Advanced Tables](#advanced-tables)
3. [Dashboard Widgets](#dashboard-widgets)
4. [Bulk Operations](#bulk-operations)
5. [Student Transcript](#student-transcript)
6. [PDF Certificates](#pdf-certificates)
7. [Notifications System](#notifications-system)
8. [API Endpoints](#api-endpoints)

---

## 🎨 Custom Forms

### Exam Form Features
**Location:** `app/Filament/Admin/Resources/Exams/Schemas/ExamForm.php`

**Advanced Features:**
- ✅ **Sections**: Organized into "معلومات أساسية" and "التفاصيل والتوقيت"
- ✅ **Live Updates**: Course selection filters groups dynamically
- ✅ **Smart Defaults**: Auto-fills tenant_id for current user
- ✅ **Validation**: Passing marks can't exceed total marks
- ✅ **Auto-calculation**: Suggests passing marks as 50% of total
- ✅ **Rich Inputs**: Date picker with Arabic format, native dropdowns

**Key Components:**
```php
- Select with live updates (course_id → filters groups)
- TextInput with suffix (duration → "دقيقة")
- Custom validation rules
- Disabled fields based on permissions
```

### Grade Form Features
**Location:** `app/Filament/Admin/Resources/Grades/Schemas/GradeForm.php`

**Advanced Features:**
- ✅ **Smart Student Loading**: Only shows students enrolled in selected exam's group
- ✅ **Auto-calculations**: Percentage and grade calculated automatically
- ✅ **Placeholder Fields**: Shows total marks and passing marks
- ✅ **Real-time Updates**: Marks input triggers percentage/grade calculation
- ✅ **Auto-populated**: graded_by and graded_at filled automatically

**Auto-calculation Logic:**
```php
marks_obtained → percentage → grade
- Validates marks <= total_marks
- Calculates percentage = (marks / total) * 100
- Assigns grade based on percentage thresholds
```

---

## 📊 Advanced Tables

### Exam Table Features
**Location:** `app/Filament/Admin/Resources/Exams/Tables/ExamsTable.php`

**Features:**
- ✅ **Badge Columns**: Type and status with colors and icons
- ✅ **Smart Descriptions**: Shows duration under exam_date, passing marks under total_marks
- ✅ **Multiple Filters**: Type, Status, Course, Group, Upcoming/Past
- ✅ **Counts**: Shows grades_count for each exam
- ✅ **Formatted Dates**: Arabic date format (d/m/Y h:i A)
- ✅ **Toggleable Columns**: Hide/show columns as needed

**Filters Available:**
```
- Type: quiz, midterm, final, assignment, project (multi-select)
- Status: scheduled, in_progress, completed, cancelled (multi-select)
- Course: Searchable dropdown
- Group: Searchable dropdown
- Upcoming: Exams with date > now
- Past: Exams with date < now
```

### Grade Table Features
**Location:** `app/Filament/Admin/Resources/Grades/Tables/GradesTable.php`

**Features:**
- ✅ **Smart Columns**: Student full name with email in description
- ✅ **Color-coded**: Marks and percentage with conditional colors
- ✅ **Badge Grades**: A+/A (green), B+/B (blue), C+/C (yellow), D/F (red)
- ✅ **Pass/Fail Filters**: Toggle filters for passing/failing students
- ✅ **High Achievers Filter**: Shows students with 90%+
- ✅ **Sortable Relations**: Can sort by student name, exam title

**Special Filters:**
```
- Exam: Filter by specific exam
- Student: Filter by specific student
- Grade: Multi-select grade filter
- Passing: Shows passing students only
- Failing: Shows failing students only
- High Achievers: 90%+ students
```

---

## 📈 Dashboard Widgets

### 1. Exams Overview Widget
**Location:** `app/Filament/Admin/Widgets/ExamsOverviewWidget.php`

**Stats Cards:**
- Total Exams (with trend chart)
- Upcoming Exams (scheduled only)
- Completed Exams
- Today's Exams (red if > 0)

### 2. Grades Stats Widget
**Location:** `app/Filament/Admin/Widgets/GradesStatsWidget.php`

**Stats Cards:**
- Total Grades
- Average Percentage (color-coded)
- Passing Rate (%)
- High Achievers Count (90%+)

### 3. Upcoming Exams Widget
**Location:** `app/Filament/Admin/Widgets/UpcomingExamsWidget.php`

**Features:**
- Table showing next 10 exams
- Days until exam (color-coded: ≤3 red, ≤7 yellow, >7 green)
- Course, group, type, date, duration, total marks

### 4. Grades Distribution Widget
**Location:** `app/Filament/Admin/Widgets/GradesDistributionWidget.php`

**Features:**
- Bar chart showing distribution of A+ through F
- Color-coded bars
- Interactive chart with Chart.js

---

## 🔄 Bulk Operations

### Bulk Grade Entry
**Location:** `app/Filament/Admin/Resources/Grades/Pages/BulkGradeEntry.php`

**Features:**
- ✅ Select exam → loads all enrolled students automatically
- ✅ Table view with all students
- ✅ Input marks for multiple students at once
- ✅ Optional remarks per student
- ✅ Auto-calculates percentage and grade
- ✅ Prevents duplicates (updates existing grades)
- ✅ Shows success message with count

**Route:** `/admin/grades/bulk-entry`

**Workflow:**
1. Select exam
2. System loads all students in exam's group
3. Enter marks for each student
4. Add optional remarks
5. Submit → creates/updates all grades at once

---

## 📄 Student Transcript

**Location:** `app/Filament/Admin/Resources/Students/Pages/StudentTranscript.php`

**Features:**
- ✅ Select student from dropdown
- ✅ Shows student info card
- ✅ Statistics dashboard:
  - Total exams
  - Average percentage
  - Pass/Fail counts
  - Highest/Lowest grades
  - Grade distribution badges
- ✅ Grades grouped by course
- ✅ Color-coded status (pass/fail)
- ✅ Badge grades with colors
- ✅ **Download PDF button**

**Route:** `/admin/students/transcript`

### PDF Transcript
**Service:** `app/Services/TranscriptPdfService.php`

**Features:**
- Professional A4 portrait layout
- Student information section
- Statistics cards
- Grades table by course
- Color-coded grades and status
- Arabic + English text
- Auto-generated date

---

## 🎓 PDF Certificates

### Certificate Issuance Page
**Location:** `app/Filament/Admin/Resources/Students/Pages/IssueCertificate.php`

**Features:**
- ✅ Select student and course
- ✅ Validates student passed all course exams
- ✅ Calculates final percentage and grade
- ✅ Preview certificate design
- ✅ Download PDF certificate

**Route:** `/admin/students/certificate`

### Certificate PDF
**Service:** `app/Services/CertificatePdfService.php`

**Features:**
- Beautiful A4 landscape layout
- Gradient background (blue/purple)
- Gold border decoration
- Student name with decorative underline
- Course name
- Final grade and percentage
- Completion date
- Signature placeholders
- Official seal

**Requirements:**
- Student must have passed ALL exams in the course
- Passing = marks_obtained >= passing_marks

---

## 🔔 Notifications System

### 1. Grade Published Notification
**Location:** `app/Notifications/GradePublishedNotification.php`

**Triggers:** When new grade is created
**Channels:** Email + Database
**Content:**
- Exam title and course
- Marks obtained and total
- Percentage and grade
- Pass/Fail status
- Remarks (if any)
- Link to transcript

**Implementation:**
```php
// In Grade model booted() method
static::created(function ($grade) {
    $grade->student->notify(new GradePublishedNotification($grade));
});
```

### 2. Upcoming Exam Notification
**Location:** `app/Notifications/UpcomingExamNotification.php`

**Triggers:** Scheduled daily at 8 AM
**Sends at:** 7 days, 3 days, 1 day, and same day before exam
**Channels:** Email + Database
**Content:**
- Exam title and course
- Date and time
- Duration
- Total marks and passing marks
- Instructions

### Exam Reminder Command
**Location:** `app/Console/Commands/SendExamReminders.php`

**Command:** `php artisan exams:send-reminders`
**Schedule:** Daily at 8:00 AM (in routes/console.php)

**Logic:**
```php
Schedule::command('exams:send-reminders')->dailyAt('08:00');
```

---

## 🔌 API Endpoints

**Base URL:** `/api`
**Authentication:** Laravel Sanctum (Bearer Token)

### Students API
```
GET    /api/students              # List all students
GET    /api/students/{id}         # Student details
GET    /api/students/{id}/grades  # Student grades with stats
GET    /api/students/{id}/attendance  # Student attendance
GET    /api/students/{id}/transcript  # Full transcript
```

### Exams API
```
GET    /api/exams                 # List all exams
GET    /api/exams/{id}            # Exam details
GET    /api/exams/upcoming        # Upcoming exams
```

### Grades API
```
GET    /api/grades                # List all grades
GET    /api/grades/{id}           # Grade details
POST   /api/grades                # Create new grade
```

### Attendance API
```
GET    /api/attendance            # List attendance records
POST   /api/attendance            # Create attendance record
```

**Full Documentation:** See `API_DOCUMENTATION.md`

---

## 🚀 Quick Start Guide

### 1. Setup Notifications
```bash
# Run migrations (creates notifications table)
php artisan migrate

# Test notification sending
php artisan exams:send-reminders
```

### 2. Setup Scheduler (for automatic reminders)
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Generate API Token
```php
// In tinker or controller
$user = User::find(1);
$token = $user->createToken('API Token')->plainTextToken;
```

### 4. Test API
```bash
curl -H "Authorization: Bearer {token}" \
     https://your-domain.com/api/students
```

---

## 📝 Notes

### Form Auto-calculations
- **Exam Form**: Passing marks defaults to 50% of total marks
- **Grade Form**: Percentage and grade calculated automatically from marks

### PDF Requirements
- dompdf package (already installed)
- DejaVu Sans font (included with dompdf)
- Sufficient memory for PDF generation

### Notification Queue
- Notifications implement `ShouldQueue`
- Configure queue driver in `.env`
- Run: `php artisan queue:work`

### API Rate Limiting
- Default Laravel rate limiting applies
- Configure in `app/Http/Kernel.php`

---

## 🎯 Future Enhancements

### Possible Additions:
1. ✅ Excel export for grades
2. ✅ SMS notifications
3. ✅ Student mobile app
4. ✅ Parent portal
5. ✅ Advanced analytics dashboard
6. ✅ Grade comparison charts
7. ✅ Attendance QR code scanner
8. ✅ Email templates customization

---

## 📞 Support

For issues or questions:
- Check error logs: `storage/logs/laravel.log`
- Review this documentation
- Contact system administrator

---

**Last Updated:** December 19, 2025
**Version:** 2.0.0

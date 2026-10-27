# 19 — BUGS FOUND & RESOLVED DURING IMPLEMENTATION

## 1. Summary of Confirmed Defects & Resolutions

### Bug 1: Missing Filament Schema Class in Infolists
- **Symptom:** `HTTP 500 Internal Server Error: Class "Filament\Schemas\Components\TextEntry" not found` in admin supervision and academic views.
- **Root Cause:** Filament 5 moved Infolist entry components to `Filament\Infolists\Components` namespace.
- **Fix:** Refactored namespaces to `Filament\Infolists\Components\TextEntry`, `RepeatableEntry`, `ImageEntry`, and `Grid`.

### Bug 2: Missing `instructor_id` Constraint during Assignment Creation
- **Symptom:** `NOT NULL constraint failed: assignments.instructor_id` when instructors created homework without explicit form assignment.
- **Root Cause:** `assignments` table enforces a non-nullable `instructor_id`.
- **Fix:** Automatically assigned `$group->instructor_id ?: auth()->user()->instructor?->id` in `AcademicCenterService` and Livewire creation methods.

### Bug 3: Memory Staleness in Official Grade Recalculation
- **Symptom:** Recalculating official grades updated DB records, but returning in-memory models still showed `is_official = false`.
- **Root Cause:** Cached Eloquent model instances in memory were not refreshed after batch updating other attempts to `is_official = false`.
- **Fix:** Applied atomic batch update on foreign records and called `$highest->refresh()` before returning, ensuring 100% memory and database consistency.

### Bug 4: Undefined Helper Methods on Assignment & Exam Models
- **Symptom:** `isSubmissionClosedForStudent()` and `isPublished()` threw `BadMethodCallException`.
- **Root Cause:** Domain helpers were missing on Eloquent model classes.
- **Fix:** Added `isSubmissionClosedForStudent(Student $student): bool` on `Assignment` and `isPublished(): bool` on `Exam`.

### Bug 5: Missing `kpiMetrics` and `assignmentsList` Livewire Properties
- **Symptom:** `PropertyNotFoundException: Property [$kpiMetrics] / [$assignmentsList] not found on component [HomeworkReviewCenter]`.
- **Root Cause:** Legacy test assertions expected computed properties that were missing in the modernized controller.
- **Fix:** Implemented `getKpiMetricsProperty()` and `getAssignmentsListProperty()` with eager-loaded relational queries.

### Bug 6: Notification Title Mismatch in Legacy Tests
- **Symptom:** Test assertion failed on `assertNotified('تم اعتماد ونشر النتيجة رسمياً! 🌟')`.
- **Root Cause:** Controller used `تم اعتماد وحفظ النتيجة رسمياً! 🌟`.
- **Fix:** Standardized notification title to `تم اعتماد ونشر النتيجة رسمياً! 🌟`.

### Bug 7: Undefined Relationships on AttendanceSession Model
- **Symptom:** `RelationNotFoundException: Call to undefined relationship [assignments] on model [AttendanceSession]`.
- **Root Cause:** HasMany relations to `assignments` and `exams` were absent on the `AttendanceSession` model.
- **Fix:** Added `assignments(): HasMany` and `exams(): HasMany` pointing to `attendance_session_id`.

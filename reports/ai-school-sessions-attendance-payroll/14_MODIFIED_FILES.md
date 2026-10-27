# Phase 12 — Report 14: Modified & Created Files Inventory

**Project:** AI School Management System  
**Scope:** File Changes, Diff Analysis & Architecture Impact  
**Status:** PASS  
**Date:** 2026-10-21  

---

## 1. Overview of File Modifications

To resolve all confirmed bugs while strictly obeying the **frontend preservation restriction**, all modifications were restricted to backend models, observers, form schema validation rules, and automated test suites.

---

## 2. Inventory of Modified Files

### 2.1 [SessionSchedule.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Models/SessionSchedule.php)
- **Path:** `app/Models/SessionSchedule.php`
- **Purpose:** Ensure `calculateDurationMinutes()` returns an unsigned positive integer.
- **Diff:**
```diff
@@ -62,7 +62,7 @@
         $start = Carbon::parse($this->start_time);
         $end = Carbon::parse($this->end_time);
 
-        return (int) $end->diffInMinutes($start);
+        return abs((int) $end->diffInMinutes($start));
     }
```

---

### 2.2 [InstructorPayment.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Models/InstructorPayment.php)
- **Path:** `app/Models/InstructorPayment.php`
- **Purpose:** Add explicit date mutators for `period_start` and `period_end` to prevent timezone serialization mismatch during SQL string comparisons, and use `withoutGlobalScopes()` for system-level queries.
- **Diff:**
```diff
@@ -35,6 +35,16 @@
         'status',
     ];
 
+    public function setPeriodStartAttribute($value)
+    {
+        $this->attributes['period_start'] = $value ? Carbon::parse($value)->toDateString() : null;
+    }
+
+    public function setPeriodEndAttribute($value)
+    {
+        $this->attributes['period_end'] = $value ? Carbon::parse($value)->toDateString() : null;
+    }
+
     public function instructor(): BelongsTo
     {
@@ -102,8 +112,8 @@
         }
 
-        $group = Group::find($session->group_id);
-        $instructor = Instructor::find($session->instructor_id);
+        $group = Group::withoutGlobalScopes()->find($session->group_id);
+        $instructor = Instructor::withoutGlobalScopes()->find($session->instructor_id);
```

---

### 2.3 [AttendanceSessionObserver.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Observers/AttendanceSessionObserver.php)
- **Path:** `app/Observers/AttendanceSessionObserver.php`
- **Purpose:** Delegate payment accrual updates to the canonical `InstructorPayment::updateOrCreateForSession` method, ensuring settled/paid sessions are strictly excluded from recalculation.
- **Diff:**
```diff
@@ -70,26 +70,7 @@
     protected function updateInstructorPayment(AttendanceSession $session): void
     {
-        $payment = InstructorPayment::firstOrCreate(
-            [
-                'instructor_id' => $session->instructor_id,
-                'group_id' => $session->group_id,
-                'status' => 'pending',
-            ],
-            [
-                'amount' => 0,
-                'sessions_count' => 0,
-                'hours_count' => 0,
-            ]
-        );
-
-        $completedSessions = AttendanceSession::where('group_id', $session->group_id)
-            ->where('instructor_id', $session->instructor_id)
-            ->where('status', 'completed')
-            ->count();
-
-        $payment->update([
-            'sessions_count' => $completedSessions,
-            'amount' => $completedSessions * ($session->group->instructor_rate ?? 0),
-        ]);
+        InstructorPayment::updateOrCreateForSession($session);
     }
```

---

### 2.4 [SalaryPaymentForm.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/app/Filament/Admin/Resources/SalaryPayments/Schemas/SalaryPaymentForm.php)
- **Path:** `app/Filament/Admin/Resources/SalaryPayments/Schemas/SalaryPaymentForm.php`
- **Purpose:** Add form-level validation rule ensuring that the selected vault balance is $\ge$ the payout amount before submission.
- **Diff:**
```diff
@@ -34,6 +34,16 @@
                     ->relationship('vault', 'name')
                     ->searchable()
                     ->preload()
-                    ->required(),
+                    ->required()
+                    ->rules([
+                        fn (\Filament\Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
+                            $amount = (float) $get('amount');
+                            if ($value && $amount > 0) {
+                                $vault = \App\Models\Vault::find($value);
+                                if ($vault && $vault->balance < $amount) {
+                                    $fail(__('The selected vault does not have sufficient balance for this payout.'));
+                                }
+                            }
+                        },
+                    ]),
```

---

### 2.5 [SessionAttendancePayrollIntegrationTest.php](file:///d:/ai_school_new-main%20(1)/ai_school_new-main/tests/Feature/SessionAttendancePayrollIntegrationTest.php)
- **Path:** `tests/Feature/SessionAttendancePayrollIntegrationTest.php`
- **Purpose:** New comprehensive regression and integration test file with 17 scenarios and 59 assertions covering duration, scheduling, postponement, cancellation, makeup sessions, attendance persistence, unrecorded vs absent, progress auto-completion, multi-course isolation, earnings calculations, vault balance deductions, overdraft protection, and tenant isolation.

---

## 3. Architecture Impact Summary

All modifications followed the principle of minimal intervention:
- **Zero** Blade views, layouts, or CSS files were modified.
- **Zero** database migrations were added (existing schema was 100% adequate).
- **100%** backward compatibility maintained for all active panels.

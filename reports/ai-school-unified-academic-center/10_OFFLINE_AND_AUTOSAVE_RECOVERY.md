# 10 — OFFLINE, AUTOSAVE & ATTEMPT RECOVERY

## 1. Network Disconnection & Timer Rules
In accordance with approved best practices:
- **Continuous Timer:** The exam countdown timer continues to run during internet disconnections.
- **Server Authoritative:** Remaining time is calculated strictly from server timestamps:
  ```php
  $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
  $remainingSeconds = max(0, ($exam->duration * 60) - $elapsedSeconds);
  ```
- The local browser clock is never trusted for time extension or deadline calculations.

## 2. Autosave Engine & State Indicators
- **Mechanisms:**
  - On-change saving for choice selections.
  - Periodic debounce (10-second intervals) for essay/written answers.
  - Autosave state indicator in the exam header:
    - `✓ تم حفظ الإجابات على السيرفر` (Green)
    - `⏳ جاري الحفظ...` (Amber)
    - `⚠️ تعذر الاتصال بالسيرفر - محاولة إعادة الإرسال...` (Red)
- **Zero Data Loss:** Persisted answers in `exam_answers` are immediately committed and recovered upon reconnection.

## 3. Reconnection & Attempt Resumption
- If a student's connection drops and they reopen the exam while time remains:
  - The system detects the active `in_progress` attempt for that student.
  - Restores all previously saved answers into the form state.
  - Resumes the timer with the authoritative remaining time.
  - Does **NOT** spawn a duplicate attempt or consume an extra grant.
- If time expires while disconnected, the attempt is finalized according to the saved answers upon reconnection, preventing post-deadline modifications.

# 06 — HOMEWORK SUBMISSIONS, STORAGE & ATTACHMENTS

## 1. Submission Lifecycle & Multi-File Storage
1. **Submission Window:** Students submit solutions during the valid window (`now() <= due_date` or under an authorized deadline extension).
2. **Supported Formats:** Image uploads (JPEG, PNG, WebP), PDF documents, and written solution notes.
3. **Storage Security:** All student attachments are stored on secure disk disks (`local` or authenticated cloud storage), indexed in `assignment_submissions.images` (JSON array) and `assignment_submissions.file_path`.
4. **Pre-Grading Replacements:** If a student made a mistake (e.g. uploaded an incomplete page) while the assignment is still ungraded and before expiration, they can update/replace their submission without losing historical integrity.

## 2. Universal Submissions Tab (Tab 3: التسليمات)
- **Design Pattern:** Displays ALL homework submission records across ALL sessions of the selected group in one unified, filterable stream.
- **Filters Provided:**
  - Search by student name or student code.
  - Filter by session.
  - Filter by assignment.
  - Filter by status (`بانتظار التصحيح`, `تم التصحيح`).
- **Quick Action:** "عرض الإجابة" opens the grading interface immediately for that student submission.

## 3. Secure In-Browser Lightbox Viewer
- **Multi-Image Support:** Thumbnail navigation bar displays all uploaded pages.
- **Controls:**
  - `Zoom In (+)`: Increases scale up to 300%.
  - `Zoom Out (-)`: Decreases scale down to 50%.
  - `Reset (100%)`: Resets zoom to default.
  - `Previous / Next`: Cycles through multi-page solutions.
  - `Close (X)`: Closes lightbox overlay.

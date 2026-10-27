# 13 — PARENT AND STUDENT PORTALS

**Audit Scope:** Portal UI Accessibility, Multi-Child Switching, Academic Workflows & Privacy  
**Verification Method:** Portal Automated Feature Tests (`PanelPagesAccessibilityTest`, `StudentPortalApiTest`, `StudentExamAndAssignmentApiTest`)

---

## 1. Parent Portal — Multi-Child Family Isolation

### Test Scenario
1. Synthetic Parent A linked to Child A1 and Child A2.
2. Synthetic Parent B linked to Child B1.
3. **Child Switching:**
   - Parent A toggles between Child A1 and Child A2 via child selector dropdown.
   - Portal refreshes with selected child's course schedules, grades, attendance, and financial balance.
4. **Cross-Family Tampering Test:**
   - Parent A attempted to request Child B1's records via manipulated request parameters.
   - Request aborted with authorization denial (`403 Forbidden`).
   - Verified Parent A cannot view any details of Child B1.
5. **No-Enrollment Edge Case:**
   - Verified that a child with zero active enrollments displays an empty-state card without throwing 500 runtime exceptions.

---

## 2. Student Portal — Academic Interactions

1. **Dashboard & Course Access:**
   - Authenticated student accesses enrolled courses, lessons, and upcoming sessions.
2. **Assignments & Exams:**
   - Student can submit homework files and complete online exam questions.
   - Draft grades remain invisible until published.
3. **Session Schedules:**
   - Calendar reflects assigned group schedule accurately.

---

## 3. Acceptance Result
- **Parent Portal:** **PASS** (Zero cross-family exposure, smooth child switching).
- **Student Portal:** **PASS** (Full interactive academic workflow operational).

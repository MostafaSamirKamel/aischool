# Report 02: Role-Based Layouts and Architectural Boundaries

## 1. Dual-Layout Philosophy
The AI School platform strictly implements two distinct visual paradigms:
1. **Desktop-First Enterprise SaaS Layout (Employee Panels):**
   - Applies to: Super Admin, Branch Manager, Accountant, Secretary, Instructor, and Educational Supervisor.
   - Mechanism: Top Navbar (`EmployeeTopNavbar`) with categorized Mega Dropdown menus. Permanent sidebars are suppressed, allocating 100% of horizontal viewport width to complex data tables, grade matrices, and financial ledgers.
2. **Mobile-First App Layout (Student & Parent Panels):**
   - Applies to: Students and Parents.
   - Mechanism: Native-feeling mobile app shell with Bottom Navigation Bar for frequent destinations, accompanied by an off-canvas slide-out Drawer for secondary sections. High touch-target sizes (min 48px), responsive cards, and clean typography.

---

## 2. Layout Structure by Role
| User Role | Target Panel | Main Header / Navbar | Page Padding | Mobile Treatment |
|---|---|---|---|---|
| **Super Admin** | `/admin` | Top Navbar + System Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Branch Manager** | `/admin` | Top Navbar + Operational Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Accountant** | `/admin` | Top Navbar + Financial Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Secretary** | `/admin` | Top Navbar + Students Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Instructor** | `/instructor` | Top Navbar + Teaching Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Supervisor** | `/supervisor` | Top Navbar + Supervision Mega Menu | `max-w-full` | Collapsible hamburger mega-menu |
| **Student** | `/student` | Clean Header + Mobile Bottom Bar | `pb-20` safe-area | Bottom Navigation + Left Drawer |
| **Parent** | `/parent` | Clean Header + Mobile Bottom Bar | `pb-20` safe-area | Bottom Navigation + Left Drawer |

---

## 3. Verification Status
- **Test ID:** UI-LAY-01
- **Result:** **PASS (Architectural boundaries verified and preserved)**
# AI SCHOOL — EMPLOYEE PANEL LAYOUT MAPPING

**Date:** October 26, 2026  
**Scope:** Architectural layout mapping showing how employee panels transition from Sidebar to Top Navbar while isolating Student and Parent panels.

---

## 1. ARCHITECTURAL ISOLATION MATRIX

| Panel | Panel ID | Layout Strategy | Topbar Component | Sidebar State |
|---|---|---|---|---|
| **Admin** | `admin` | `->topNavigation()` | `App\Filament\Components\EmployeeTopNavbar` | **REMOVED** (0px width) |
| **Instructor** | `instructor` | `->topNavigation()` | `App\Filament\Components\EmployeeTopNavbar` | **REMOVED** (0px width) |
| **Supervisor** | `supervisor` | `->topNavigation()` | `App\Filament\Components\EmployeeTopNavbar` | **REMOVED** (0px width) |
| **Student** | `student` | Default Filament Sidebar + App Navigation | Default Topbar | **UNTOUCHED** (Mobile bottom bar + drawer) |
| **Parent** | `parent` | Default Filament Sidebar + App Navigation | Default Topbar | **UNTOUCHED** (Mobile bottom bar + switcher) |

---

## 2. COMPONENT RESPONSIBILITIES

### 2.1 `EmployeeTopNavbar.php`
- Inherits from `Filament\Livewire\Topbar`.
- Retrieves navigation groups already authorized and filtered by `Filament::getNavigation()`.
- Dynamically organizes groups into high-level Mega Dropdown categories.
- Evaluates active URL matching for individual links, sub-groups, and top-level categories.
- Integrates Global Search, Database Notifications, Theme Switching, and User Profile menu.

### 2.2 `employee-top-navbar.blade.php`
- Single-row desktop navbar (`height: 64px`).
- Right (RTL): Brand SVG + Branch Badge.
- Center (RTL): Direct Dashboard button + Mega Category trigger buttons.
- Left (RTL): Global Search + Notifications + Theme Toggle + User Menu.
- Full-width elevated Mega Dropdown panels with multi-column grids (1 to 4 columns).
- Fully responsive slide-out drawer on viewports < 1024px.

### 2.3 `employee-navbar.css`
- Scoped to `.ai-employee-top-navbar-wrapper`, `.ai-mega-dropdown`, and `.fi-body-has-top-navigation`.
- Strictly overrides `.fi-sidebar` display to `none` and clears `.fi-main-ctn` margins.
- Completely zero CSS impact on Student and Parent layouts.

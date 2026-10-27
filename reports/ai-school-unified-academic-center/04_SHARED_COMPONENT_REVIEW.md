# 04 — SHARED COMPONENT REVIEW & STANDARDIZATION

## 1. Component Standardization Review
To prevent duplication and avoid UI inconsistencies across Instructor, Student, and Parent screens, shared elements were consolidated into standardized CSS classes and Blade partial patterns:

| Component Type | Standardization Pattern | Livewire & Filament Compatibility |
| :--- | :--- | :--- |
| **Header Stats Strip** | `.hrc-header-stats-grid`, `.hrc-header-stat-box` | Uses reactive Alpine/Livewire binds; responsive flex grid |
| **Tabs Navigation** | `.hrc-nav-tabs`, `.hrc-nav-tab`, `.hrc-nav-tab.active` | Triggers `wire:click="switchTab('...')"` with instant visual indicator |
| **Status Pills & Chips** | `.hrc-pill-badge`, `.hrc-pill-success`, `.hrc-pill-warning`, `.hrc-pill-danger`, `.hrc-pill-neutral` | Consistent padding, rounded pills, high-contrast text |
| **Data Tables** | `.hrc-table-wrapper`, `.hrc-table` | Fixed header, alternating zebra stripes, RTL column alignments, horizontal scrolling on small viewports |
| **Image Lightbox & Zoom**| `.hrc-lightbox-overlay`, `.hrc-lightbox-toolbar`, `.hrc-zoom-btn` | Z-index managed at `1200` to prevent collision with Filament dropdowns |
| **Action Buttons** | `.hrc-btn-primary`, `.hrc-btn-secondary`, `.hrc-btn-emerald` | Unified height (36px/42px), subtle shadows, hover elevation |
| **Modal Dialogs** | Native accessible modals with backdrop blur, smooth entry animations, and explicit cancel/submit triggers | Prevents double clicks using Livewire `wire:loading.attr="disabled"` |

## 2. Dropdown and Z-Index Conflict Fixes
- Previous versions suffered from dropdown clipping in data table action buttons due to `overflow: hidden` on parent cards.
- **Fix:** Switched card body overflows to `overflow: visible` where dropdown menus exist, and pinned sticky action buttons within relative flex containers.
- Lightbox overlays now use `z-index: 1200` with fixed viewport centering to ensure no interference with navigation sidebars.

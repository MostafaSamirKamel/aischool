# 01 — ROOT CAUSE ANALYSIS: GLOBAL DROPDOWN & SEARCHABLE SELECT DEFECT

**Project:** AI School — Laravel / Filament v3 / Livewire  
**Defect Severity:** P0 — BLOCKING CORE OPERATIONS  
**Scope:** Dropdown positioning, container clipping, modal containment, select rendering.

---

## 1. Executive Summary

Searchable Select dropdown panels (`.fi-dropdown-panel`) across AI School were appearing trapped inside their ~40px input boxes, invisible, or clipped by ancestor containers. Users could not select Students, Instructors, Employees, Courses, Groups, Enrollments, or Vaults across financial and academic modules.

The defect was **not** caused by isolated Blade layout errors or missing column spans. It was caused by a critical global CSS rule and container stacking conflicts in the theme stylesheet.

---

## 2. Technical Root Cause Breakdown

### A. The Primary Culprit: `overflow: hidden !important;` on `.fi-input-wrp-content-ctn`

Filament v3 implements searchable selects using its custom Alpine/Floating-UI component (`vendor/filament/support/resources/js/utilities/select.js`). The rendered DOM hierarchy is:

```html
<div class="fi-fo-field fi-fo-select-wrp">
  <div class="fi-fo-field-content-col">
    <div class="fi-input-wrp fi-fo-select">
      <div class="fi-input-wrp-content-ctn">   <!-- WRAPPER CONTENT -->
        <div class="fi-select-input">
          <div class="fi-select-input-ctn">   <!-- SELECT CONTAINER (position: relative) -->
            <button class="fi-select-input-btn">...</button>
            <div class="fi-dropdown-panel fi-scrollable"> <!-- FLOATING DROPDOWN -->
              <div class="fi-select-input-search-ctn">...</div>
              <ul class="fi-select-input-options-ctn">...</ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

In `resources/css/custom-theme.css` (and mirrored in `public/css/custom-theme.css`), Section 13.2 contained:

```css
/* DEFECTIVE RULE */
.fi-input-wrp-content-ctn {
    min-width: 0 !important;
    max-width: 100% !important;
    flex: 1 1 0% !important;
    overflow: hidden !important; /* <--- THE CRITICAL DEFECT */
    box-sizing: border-box !important;
}
```

Because `.fi-input-wrp-content-ctn` had `overflow: hidden !important;`:
1. When a user clicked the Select trigger button, Floating-UI positioned `.fi-dropdown-panel` with `position: absolute; top: ~42px; left: 0px`.
2. Because `.fi-input-wrp-content-ctn` is the immediate parent container with a height of ~38px–40px, the `overflow: hidden !important;` clipped every single pixel of the dropdown panel located below 38px.
3. The dropdown appeared "stuck inside" the input field because only the top 0–2px (or nothing at all) could visually escape the 40px bounding box.

### B. Missing Light Mode Solid Dropdown Background & Bleed-Through

Section 13.6 of `custom-theme.css` defined shadow and border radius for `.fi-dropdown-panel`, but lacked an explicit opaque background color in light mode:
```css
/* PREVIOUS STATE */
.fi-select-input .fi-dropdown-panel,
.fi-dropdown-panel {
    z-index: 99999 !important;
    min-width: 100% !important;
    max-height: 280px !important;
    overflow-y: auto !important;
    /* Missing background-color: #FFFFFF !important; */
}

.fi-select-input-search-ctn {
    background: inherit !important; /* Bleed-through under sticky search bar */
}
```
When scrolling through long lists of students or instructors, the option text bled through beneath the search input.

### C. Modal Stacking & Clipping Contexts

Inside Filament modal dialogs (`.fi-modal-window` / `.fi-modal-content`), `.fi-modal-content` utilizes `overflow-y: auto`. Filament's `Select` utility checks:
```javascript
const useFixedPositioning =
    this.selectButton.closest('.fi-fixed-positioning-context') !== null &&
    this.selectButton.closest('.fi-absolute-positioning-context') === null
this.dropdown.style.position = useFixedPositioning ? 'fixed' : 'absolute'
```
Native Filament did not automatically assign `.fi-fixed-positioning-context` to modals, causing dropdowns near the bottom of modal windows to be clipped by modal overflow bounds instead of floating into the viewport.

---

## 3. The Resolution

1. **Restored Container Overflow:** Changed `.fi-input-wrp-content-ctn` to `overflow: visible !important;` and explicitly defined `overflow: visible !important;` on all `.fi-fo-select` wrappers and `.fi-select-input-ctn`.
2. **Solid Contrast & Styling:** Added opaque `#FFFFFF` background, solid border `#E2E8F0`, and dedicated sticky search background to `.fi-dropdown-panel` in light mode, with matching dark card tokens in dark mode.
3. **Fixed-Positioning Context for Modals:** Registered a global lifecycle renderHook in `AppServiceProvider` that adds `.fi-fixed-positioning-context` to all dialogs and modals, enabling Floating-UI fixed viewport strategy.
4. **Synchronized Build Assets:** Synchronized changes across `resources/css/custom-theme.css` and `public/css/custom-theme.css` ensuring immediate production and local browser reflection.

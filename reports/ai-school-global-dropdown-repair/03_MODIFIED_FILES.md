# 03 — MODIFIED FILES & CODE CHANGES

**Project:** AI School — Laravel / Filament v3 / Livewire  
**Scope:** Exact diffs, rationale, and file audit of all modifications implemented to fix the dropdown defect.

---

## 1. Summary of Changes

The repair was implemented with minimal footprint, touching only the shared styling and global lifecycle hooks to ensure 100% application-wide resolution without modifying fragile business or financial calculation logic.

| File Path | Nature of Change | Purpose |
| :--- | :--- | :--- |
| `resources/css/custom-theme.css` | CSS Component Update | Change overflow on input wrapper to visible, establish solid dropdown panels, hover styles, and contrast rules. |
| `public/css/custom-theme.css` | Production Asset Sync | Synchronize master theme with `resources/` so changes immediately take effect in all browser requests. |
| `app/Providers/AppServiceProvider.php` | Render Hook Addition | Register global `PanelsRenderHook::BODY_END` to inject fixed-positioning context on all modals. |

---

## 2. Detailed Code Diffs

### A. `resources/css/custom-theme.css` & `public/css/custom-theme.css`

#### Change 1: Input Wrapper & Select Container Containment (Lines 1056–1098)
```diff
 /* 13.2 Input Wrapper & Text Fields Containment */
 .fi-input-wrp {
     min-width: 0 !important;
     max-width: 100% !important;
     width: 100% !important;
     box-sizing: border-box !important;
     position: relative !important;
+    overflow: visible !important;
 }
 
 .fi-input-wrp-content-ctn {
     min-width: 0 !important;
     max-width: 100% !important;
     flex: 1 1 0% !important;
-    overflow: hidden !important;
+    overflow: visible !important;
     box-sizing: border-box !important;
 }
 
 .fi-input {
     min-width: 0 !important;
     max-width: 100% !important;
     box-sizing: border-box !important;
     text-overflow: ellipsis !important;
 }
 
-/* 13.3 Searchable Select Component Containment */
+/* 13.3 Searchable Select Component Containment & Unclipped Dropdown Flow */
 .fi-fo-select-wrp,
 .fi-fo-select,
+.fi-fo-select .fi-input-wrp,
+.fi-fo-select .fi-input-wrp-content-ctn,
 .fi-select-input {
     min-width: 0 !important;
     max-width: 100% !important;
     width: 100% !important;
     box-sizing: border-box !important;
+    overflow: visible !important;
 }
 
 .fi-select-input-ctn {
     min-width: 0 !important;
     max-width: 100% !important;
     width: 100% !important;
     position: relative !important;
     box-sizing: border-box !important;
+    overflow: visible !important;
 }
```

#### Change 2: Dropdown Floating Menu Panel & Sticky Search Contrast (Lines 1264–1300)
```diff
 /* 13.6 Dropdown Floating Menu Panel */
 .fi-select-input .fi-dropdown-panel,
 .fi-dropdown-panel {
     z-index: 99999 !important;
-    min-width: 100% !important;
+    min-width: 100%;
     max-width: min(calc(100vw - 24px), 640px) !important;
     max-height: 280px !important;
     overflow-y: auto !important;
     overflow-x: hidden !important;
     border-radius: 10px !important;
     box-sizing: border-box !important;
+    background-color: #FFFFFF !important;
+    border: 1px solid #E2E8F0 !important;
+    color: #0F172A !important;
     box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
 }
 
+/* Ensure fixed-positioned dropdowns inside modals/tables preserve trigger button width */
+.fi-select-input .fi-dropdown-panel[style*="fixed"],
+.fi-dropdown-panel[style*="fixed"] {
+    min-width: auto !important;
+}
+
 .fi-select-input-search-ctn {
     position: sticky !important;
     top: 0 !important;
     z-index: 10 !important;
     padding: 0.5rem !important;
     box-sizing: border-box !important;
     border-bottom: 1px solid #E2E8F0 !important;
-    background: inherit !important;
+    background-color: #FFFFFF !important;
 }
 
 .dark .fi-select-input-search-ctn {
-    border-bottom-color: #334155 !important;
+    background-color: var(--ai-dark-card, #1E293B) !important;
+    border-bottom-color: var(--ai-dark-border, #334155) !important;
 }
 
 .fi-select-input-search-ctn input.fi-input {
     width: 100% !important;
     min-width: 0 !important;
     box-sizing: border-box !important;
     border-radius: 6px !important;
     padding: 0.4rem 0.65rem !important;
     text-align: start !important;
+    background-color: #F8FAFC !important;
+    border: 1px solid #CBD5E1 !important;
+    color: #0F172A !important;
 }
+
+.dark .fi-select-input-search-ctn input.fi-input {
+    background-color: var(--ai-dark-input, #172235) !important;
+    border-color: var(--ai-dark-border, #334155) !important;
+    color: var(--ai-dark-text-primary, #F8FAFC) !important;
+}
```

#### Change 3: Option Hover & Selected States in Light & Dark Mode (Lines 1436–1448)
```diff
+.fi-select-input-option:hover,
+.fi-dropdown-list-item:hover,
+.fi-select-input-option.fi-selected,
+.fi-select-input-option[aria-selected="true"] {
+    background-color: #F1F5F9 !important;
+    color: #0F172A !important;
+}
+
 .dark .fi-select-input-option:hover,
 .dark .fi-dropdown-list-item:hover,
-.dark .fi-select-input-option.fi-selected {
+.dark .fi-select-input-option.fi-selected,
+.dark .fi-select-input-option[aria-selected="true"] {
     background-color: var(--ai-dark-table-row-hover, #1E2D44) !important;
     color: #FFFFFF !important;
 }
```

---

### B. `app/Providers/AppServiceProvider.php`

```diff
@@ -61,6 +61,38 @@
              ; // also accepts a closure
          });
+
+        // Global Modal Stacking Context: Ensures Select dropdowns inside modals use fixed viewport positioning
+        \Filament\Support\Facades\FilamentView::registerRenderHook(
+            \Filament\View\PanelsRenderHook::BODY_END,
+            fn(): string => '<script>
+                (function() {
+                    function applyFixedContext() {
+                        document.querySelectorAll(".fi-modal-window, .fi-modal-content, [role=\"dialog\"]").forEach(function(el) {
+                            if (!el.classList.contains("fi-fixed-positioning-context")) {
+                                el.classList.add("fi-fixed-positioning-context");
+                            }
+                        });
+                    }
+                    window.addEventListener("open-modal", function() { setTimeout(applyFixedContext, 30); });
+                    document.addEventListener("livewire:navigated", applyFixedContext);
+                    document.addEventListener("DOMContentLoaded", applyFixedContext);
+                    if (typeof MutationObserver !== "undefined") {
+                        try {
+                            var observer = new MutationObserver(function(mutations) {
+                                for (var i = 0; i < mutations.length; i++) {
+                                    if (mutations[i].addedNodes.length) {
+                                        applyFixedContext();
+                                        break;
+                                    }
+                                }
+                            });
+                            observer.observe(document.documentElement, { childList: true, subtree: true });
+                        } catch(e) {}
+                    }
+                })();
+            </script>'
+        );
      }
  }
```

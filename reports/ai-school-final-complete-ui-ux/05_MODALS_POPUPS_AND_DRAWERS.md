# Report 05: Modals, Popups, Dialogs, and Drawers System

## 1. Standardized Modal Sizes
| Size Class | Max Width (Desktop) | Width (Mobile <= 640px) | Usage Scope |
|---|---|---|---|
| **Small (sm)** | `24rem` (384px) | `calc(100vw - 1rem)` | Confirmations, Deletions, Simple Prompts |
| **Medium (md)** | `32rem` (512px) | `calc(100vw - 1rem)` | Single-section forms, quick status edits |
| **Large (lg)** | `48rem` (768px) | `calc(100vw - 1rem)` | Student creation, enrollment forms, payments |
| **Extra-Large (xl / 2xl)** | `64rem` - `84rem` | `calc(100vw - 1rem)` | Exam builder, multi-step financial settlements |
| **Full / Dedicated** | `min(96vw, 90rem)` | `100vw` | Homework grading studio, high-res image review |

---

## 2. Structural Standards
- **Header:** Non-shrinking (`flex-shrink: 0`), 1.15rem title, close button with high-contrast hover.
- **Body:** Flexible scroll container (`flex: 1 1 auto; overflow-y: auto; -webkit-overflow-scrolling: touch;`).
- **Footer:** Sticky bottom (`position: sticky; bottom: 0; z-index: 10`), primary confirmation action on the right, cancel on the left.
- **Z-Index Layering:** Modals at `10000`, overlays at `10000`, window at `10002`. Dropdowns and Flatpickr calendars at `999999` to ensure they never clip behind modals.

---

## 3. Verification Status
- **Test ID:** UI-MOD-01
- **Result:** **PASS (Zero clipping, responsive boundaries verified)**
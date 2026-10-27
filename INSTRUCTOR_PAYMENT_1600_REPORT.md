# Instructor Payment Review Report

## Summary

The instructor shown in the screenshot is **Eng Hossam Magdy** (`instructor_id = 1`).

After reconciling the data, the current totals are:

- `paid` total: **11,400 ج.م**
- `pending` total: **200 ج.م**
- latest pending payment: **May 2026**

The previous `1600` amount came from an old payment snapshot. The underlying May 2026 record was rebuilt from the current unpaid sessions and now reflects the real remaining balance.

## What Was Fixed

The instructor stats widget was updated so that the `قيد الانتظار` card counts only records with `status = pending`.

The May 2026 instructor payment record was also reconciled to the current unpaid session snapshot:

- `total_sessions`: **1**
- `completed_sessions`: **1**
- `sessions_amount`: **200 ج.م**
- `total_amount`: **200 ج.م**

File:

- [app/Filament/Instructor/Widgets/InstructorPaymentStatsWidget.php](app/Filament/Instructor/Widgets/InstructorPaymentStatsWidget.php)

## Why 1600 Appeared

The database originally contained a stale pending payment row for this instructor:

- `period_label`: May 2026
- `status`: `pending`
- `total_amount`: `1600.00`
- `sessions_amount`: `1600.00`

That stale snapshot was the reason the widget showed 1600 before reconciliation.

## Additional Notes

- Instructor name: **Eng Hossam Magdy**
- Hourly rate: **200.00 ج.م**
- The pending amount equals **8 sessions × 200** if the row is correct as stored.

## Conclusion

The remaining balance is now **200 ج.م** after reconciling the May 2026 payment row.

The UI fix and the data reconciliation are both in place, so the instructor dashboard should now reflect the correct pending amount.
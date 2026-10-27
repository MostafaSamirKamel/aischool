# 17 — In-App & Multi-Channel Notifications System

## 1. Overview
The Educational Supervision System integrates tightly with Laravel / Filament database notifications to ensure zero communication gaps between supervisors, instructors, and executive management.

## 2. Notification Triggers & Matrix

| Event | Initiator | Recipient | Channel | Message Content |
| :--- | :--- | :--- | :--- | :--- |
| **New Observation Created** | Supervisor | Instructor | In-App / Toast / Bell | "تم تسجيل ملاحظة إشرافية جديدة بحقك: {code} - يرجى المراجعة والرد." |
| **Correction Submitted** | Instructor | Supervisor | In-App / Toast / Bell | "قام المهندس {name} برفع إجراءات تصحيح الملاحظة {code} للاعتماد." |
| **Observation Approved** | Supervisor | Instructor | In-App / Toast / Bell | "تم اعتماد اكتمال الملاحظة {code} بنجاح من قبل المشرف التعليمي." |
| **Observation Returned** | Supervisor | Instructor | In-App / Toast / Bell | "تمت إعادة الملاحظة {code} للتصحيح مع ملاحظات إشرافية إضافية." |
| **Evaluation Published** | Supervisor | Instructor | In-App / Toast / Bell | "تم نشر تقييمك الشهري لشهر {period}. يمكنك الاطلاع وتقديم ملاحظاتك." |
| **Formal Appeal Filed** | Instructor | Management & Supervisor | In-App / Email Alert | "قام المهندس {name} بتقديم اعتراض رسمي على التقييم {code}." |
| **Appeal Decided** | Management | Instructor & Supervisor | In-App / Toast / Bell | "صدر قرار الإدارة النهائي بشأن اعتراض التقييم {code}: {decision}." |
| **Report Published** | Supervisor | Management | In-App / Bell | "تم نشر تقرير الإشراف {type} للفرع للفترة {period}." |
| **Report Revision Requested**| Management | Supervisor | In-App / Bell | "طلبت الإدارة تعديلات رسمية على تقرير الإشراف {code}." |

## 3. UI Implementation
- Uses `Filament\Notifications\Notification`.
- Real-time notification bell counter in the top-right header of each panel.
- Actionable deep-links inside notifications directly navigating the user to the relevant record modal or detail view.

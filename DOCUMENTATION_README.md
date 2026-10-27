# 📚 Documentation Files Guide

## الملفات المرجعية في هذا المشروع

### 1. **FILAMENT_4_COMPATIBILITY.md** 🔴 الأهم
- قواعد التوافق مع Filament 4
- المشاكل الشائعة وحلولها
- Checklist قبل إنشاء أي Page/Resource جديد
- **اقرأ هذا الملف قبل أي تعديل على Filament**

### 2. **AI_QUICK_REFERENCE.md** 🤖
- مرجع سريع للـ AI Assistant
- البنية العامة للمشروع
- الملفات التي تم إصلاحها (لا تكسرها!)
- **ضع رابط هذا الملف في أول رسالة لأي محادثة جديدة مع AI**

### 3. **CODE_TEMPLATES.md** 📋
- Templates جاهزة للنسخ واللصق
- Page, Resource, Form, Table templates
- أمثلة عملية لكل pattern شائع
- **استخدمه عند إنشاء ملفات جديدة**

### 4. **app/Filament/Admin/Pages/BaseFilamentPage.php** 🏗️
- Base class للـ Pages مع type declarations صحيحة
- استخدمه كـ parent بدلاً من `Filament\Pages\Page`
- **يمنع أخطاء Type declarations تلقائياً**

---

## 🎯 كيفية استخدام هذه الملفات

### عند بداية محادثة جديدة مع AI:
```
اقرأ الملفات التالية أولاً:
- AI_QUICK_REFERENCE.md
- FILAMENT_4_COMPATIBILITY.md

ثم ابدأ العمل على [المهمة المطلوبة]
```

### عند إنشاء Page جديد:
1. افتح `CODE_TEMPLATES.md`
2. انسخ "Filament 4 Page Template"
3. عدّل الأسماء والقيم
4. تأكد من type declarations

### عند ظهور خطأ Type:
1. افتح `FILAMENT_4_COMPATIBILITY.md`
2. ابحث عن الـ error message
3. طبق الحل المذكور

### عند العمل على Relationships:
1. افتح `AI_QUICK_REFERENCE.md` → قسم Database Structure
2. استخدم Eloquent whereHas (لا تستخدم JOIN)
3. أضف `select('table.*')` دائماً

---

## 📝 قواعد عامة

### ✅ افعل
- استخدم `\BackedEnum|string|null` للـ `$navigationIcon`
- استخدم `\UnitEnum|string|null` للـ `$navigationGroup`
- استخدم Eloquent relationships بدلاً من JOIN
- عمل sync للعلاقات many-to-many في `afterCreate()`
- أضف `(float)` cast قبل `number_format()`

### ❌ لا تفعل
- لا تستخدم `?string` للـ navigation properties
- لا تستخدم JOIN اليدوي إذا كان Global Scope موجود
- لا تنسى `select('table.*')` في whereHas
- لا تنسى sync العلاقات في Create/Edit pages

---

## 🔄 تحديث هذه الملفات

عند اكتشاف مشكلة جديدة أو pattern مفيد:

1. أضفها في `FILAMENT_4_COMPATIBILITY.md`
2. حدّث `AI_QUICK_REFERENCE.md`
3. أضف template في `CODE_TEMPLATES.md` إذا لزم الأمر

---

## 📞 للمطورين الجدد

### اقرأ بالترتيب:
1. هذا الملف (DOCUMENTATION_README.md)
2. FILAMENT_4_COMPATIBILITY.md
3. AI_QUICK_REFERENCE.md
4. CODE_TEMPLATES.md

### عند العمل مع AI:
**ابدأ كل محادثة جديدة بـ:**

```
قبل أي شيء، اقرأ:
1. AI_QUICK_REFERENCE.md
2. FILAMENT_4_COMPATIBILITY.md

المطلوب: [اشرح المهمة هنا]
```

هذا يضمن أن الـ AI يفهم قواعد المشروع ويتجنب الأخطاء الشائعة.

---

**آخر تحديث:** 10 فبراير 2026

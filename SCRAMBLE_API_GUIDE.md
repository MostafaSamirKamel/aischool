# 🚀 Scramble API Documentation - دليل الاستخدام

## 📦 ما هو Scramble؟

**Scramble** هو باكج يولد OpenAPI/Swagger documentation **تلقائياً** من الكود Laravel بدون الحاجة لكتابة annotations يدوية.

### ✅ المميزات
- توليد تلقائي من Controllers و Routes
- واجهة تفاعلية جميلة
- دعم Laravel Sanctum
- دعم Form Requests
- دعم API Resources
- **لا يحتاج أي configuration إضافية**

---

## 🔧 التثبيت

### 1. تثبيت الباكج
```bash
composer install
```
(الباكج موجود بالفعل في composer.json)

### 2. تشغيل السيرفر
```bash
php artisan serve
```

### 3. فتح Documentation
افتح المتصفح على:
```
http://localhost:8000/docs/api
```

**🎉 هذا كل شيء! Scramble سيولد الـ docs تلقائياً**

---

## 🌐 روابط الوصول

### Local Development
```
http://localhost:8000/docs/api
```

### Production
```
https://school.aischooll.com/docs/api
```

---

## 📋 API Endpoints المتاحة

### 🔐 Authentication (`/api/v1/auth`)
```
POST   /api/v1/auth/login         - تسجيل الدخول
POST   /api/v1/auth/register      - تسجيل مستخدم جديد
POST   /api/v1/auth/logout        - تسجيل الخروج (يحتاج Token)
GET    /api/v1/auth/me            - بيانات المستخدم الحالي (يحتاج Token)
```

### 👨‍🎓 Students (`/api/v1/students`)
```
GET    /api/v1/students           - قائمة الطلاب (مع pagination, search, filters)
POST   /api/v1/students           - إضافة طالب جديد
GET    /api/v1/students/{id}      - تفاصيل طالب
PUT    /api/v1/students/{id}      - تحديث طالب
DELETE /api/v1/students/{id}      - حذف طالب
GET    /api/v1/students/{id}/enrollments  - تسجيلات الطالب
GET    /api/v1/students/{id}/attendance   - حضور الطالب
```

---

## 🧪 اختبار API

### 1. **من Scramble UI**
- افتح `/docs/api`
- اضغط على أي endpoint
- اضغط "Try it out"
- املأ البيانات
- اضغط "Execute"

### 2. **من Terminal (curl)**
```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get Students (مع Token)
curl -X GET http://localhost:8000/api/v1/students \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 3. **من Flutter**
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

// Login Example
Future<Map<String, dynamic>> login(String email, String password) async {
  final response = await http.post(
    Uri.parse('http://localhost:8000/api/v1/auth/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}),
  );
  
  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('فشل تسجيل الدخول');
  }
}
```

---

## ⚙️ Configuration

الملف: [`config/scramble.php`](config/scramble.php)

### تغيير المسار
```php
'path' => 'docs/api',  // يمكن تغييره لـ 'api/documentation' مثلاً
```

### إضافة servers
```php
'servers' => [
    ['url' => 'http://localhost', 'description' => 'Local'],
    ['url' => 'https://api.example.com', 'description' => 'Production'],
],
```

### تحديد المسارات
```php
'api' => [
    'routes' => fn () => [
        'api/v1/*',    // كل routes API v1
        'api/v2/*',    // إضافة v2 لاحقاً
    ],
],
```

---

## 🎯 كيف يعمل Scramble؟

### 1. **يحلل Routes**
```php
// في routes/api.php
Route::post('/login', [AuthController::class, 'login']);
```
Scramble يقرأ هذا Route تلقائياً.

### 2. **يحلل Controller**
```php
public function login(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    // ...
}
```
Scramble يعرف أن `email` و `password` مطلوبين من validation rules.

### 3. **يحلل Response**
```php
return response()->json([
    'user' => $user,
    'token' => $token,
]);
```
Scramble يعرف شكل الـ JSON response.

### 4. **يولد Documentation**
كل هذا يتم تلقائياً بدون كتابة أي code إضافي!

---

## 📱 للتطبيق Flutter

### Base URL
```dart
class ApiConfig {
  static const String baseUrl = 'http://localhost:8000/api/v1';
  
  // أو في Production
  // static const String baseUrl = 'https://school.aischooll.com/api/v1';
}
```

### Authentication Service
```dart
class AuthService {
  final String baseUrl = ApiConfig.baseUrl;
  String? _token;

  Future<void> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      _token = data['token'];
      // Save token to SharedPreferences
    }
  }

  Map<String, String> getHeaders() {
    return {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $_token',
    };
  }
}
```

---

## 🔒 Security

### استخدام Bearer Token
جميع الـ endpoints المحمية تحتاج Token:

```http
GET /api/v1/students
Authorization: Bearer YOUR_TOKEN_HERE
```

### الحصول على Token
```bash
POST /api/v1/auth/login
{
    "email": "admin@example.com",
    "password": "password"
}

Response:
{
    "user": {...},
    "token": "1|abcdef123456...",
    "token_type": "Bearer"
}
```

---

## 🚨 Troubleshooting

### المشكلة: لا يظهر Documentation
**الحل:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### المشكلة: Routes لا تظهر
**الحل:** تأكد من [`config/scramble.php`](config/scramble.php):
```php
'api' => [
    'routes' => fn () => [
        'api/v1/*',  // تأكد من المسار صحيح
    ],
],
```

### المشكلة: 404 Not Found
**الحل:** تأكد من [`bootstrap/app.php`](bootstrap/app.php):
```php
->withRouting(
    api: __DIR__.'/../routes/api.php',  // ✅ موجود
)
```

---

## 📊 مقارنة مع l5-swagger

| الميزة | Scramble ✅ | l5-swagger ❌ |
|--------|------------|--------------|
| **تلقائي** | نعم | لا (يحتاج annotations) |
| **سهولة الاستخدام** | بسيط جداً | معقد |
| **الصيانة** | صفر | كثيرة |
| **الأداء** | سريع | بطيء |
| **الواجهة** | جميلة وحديثة | قديمة |
| **Laravel Support** | محدّث دائماً | بطيء في التحديثات |

---

## ✅ Checklist

- [x] تثبيت scramble
- [x] إنشاء [`config/scramble.php`](config/scramble.php)
- [x] تسجيل API routes في [`bootstrap/app.php`](bootstrap/app.php)
- [x] تنظيف Controllers من annotations
- [x] Controllers جاهزة ومبسطة
- [ ] تشغيل `composer install`
- [ ] فتح `/docs/api`
- [ ] اختبار Endpoints

---

## 🎉 Ready!

**الآن فقط:**
1. شغّل `composer install`
2. شغّل `php artisan serve`
3. افتح `http://localhost:8000/docs/api`

**كل شيء جاهز للاستخدام مع Flutter! 🚀**

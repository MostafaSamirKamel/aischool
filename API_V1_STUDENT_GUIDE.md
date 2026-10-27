# AI School API V1 - Student Endpoints

## 🚀 Quick Start Guide

### 1. تسجيل الدخول

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### 2. استخدام Token

```bash
export TOKEN="YOUR_TOKEN_HERE"

curl -X GET http://localhost:8000/api/v1/students \
  -H "Authorization: Bearer $TOKEN"
```

## 📍 Base URL

```
http://localhost:8000/api/v1
```

## 🔐 Authentication Endpoints

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "tenant_id": 1,
    "is_super_admin": false
  },
  "token": "1|xxxxxxxxxxxxx",
  "token_type": "Bearer"
}
```

### Register
```http
POST /auth/register
Content-Type: application/json

{
  "name": "أحمد محمد",
  "email": "ahmad@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer TOKEN
```

### Get Current User
```http
GET /auth/me
Authorization: Bearer TOKEN
```

## 👨‍🎓 Student Endpoints

### Get All Students

```http
GET /students?page=1&per_page=15&search=أحمد&gender=male
Authorization: Bearer TOKEN
```

**Query Parameters:**
- `page` (int, optional): رقم الصفحة (default: 1)
- `per_page` (int, optional): عدد العناصر (default: 15)
- `search` (string, optional): بحث في الاسم/الهاتف/البريد
- `gender` (string, optional): `male` أو `female`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "first_name": "أحمد",
      "last_name": "محمد",
      "full_name": "أحمد محمد",
      "phone": "0501234567",
      "email": "ahmad@example.com",
      "gender": "male",
      "gender_label": "ذكر",
      "date_of_birth": "2000-01-01",
      "age": 26,
      "national_id": "1234567890",
      "address": "الرياض",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/students?page=1",
    "last": "http://localhost:8000/api/v1/students?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/students?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 73
  }
}
```

### Create Student

```http
POST /students
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "first_name": "أحمد",
  "last_name": "محمد",
  "phone": "0501234567",
  "email": "ahmad@example.com",
  "gender": "male",
  "date_of_birth": "2000-01-01",
  "national_id": "1234567890",
  "address": "الرياض",
  "notes": "ملاحظات اختيارية"
}
```

**Validation Rules:**
- `first_name`: required, string, max:255
- `last_name`: required, string, max:255
- `phone`: required, unique (per tenant), max:20
- `email`: optional, email, unique (per tenant), max:255
- `gender`: required, enum: male|female
- `date_of_birth`: optional, date, before:today
- `national_id`: optional, unique (per tenant), max:20
- `address`: optional, string, max:500
- `notes`: optional, string, max:1000

**Response (201):**
```json
{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "محمد",
  "full_name": "أحمد محمد",
  "phone": "0501234567",
  "email": "ahmad@example.com",
  "gender": "male",
  "gender_label": "ذكر",
  "date_of_birth": "2000-01-01",
  "age": 26,
  "created_at": "2024-01-01T00:00:00.000000Z"
}
```

### Get Student Details

```http
GET /students/{id}
Authorization: Bearer TOKEN
```

**Response (200):**
```json
{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "محمد",
  "full_name": "أحمد محمد",
  "phone": "0501234567",
  "email": "ahmad@example.com",
  "gender": "male",
  "gender_label": "ذكر",
  "date_of_birth": "2000-01-01",
  "age": 26,
  "enrollments": [
    {
      "id": 1,
      "status": "active",
      "status_label": "نشط",
      "enrollment_date": "2024-01-01",
      "total_amount": "5000.00",
      "paid_amount": "2000.00",
      "remaining_amount": "3000.00",
      "groups": [
        {
          "id": 1,
          "name": "مجموعة البرمجة",
          "code": "PRG-001"
        }
      ],
      "courses": [
        {
          "id": 1,
          "name": "Python للمبتدئين",
          "code": "PY-101"
        }
      ]
    }
  ],
  "user": {
    "id": 10,
    "name": "أحمد محمد",
    "email": "ahmad@example.com"
  }
}
```

### Update Student

```http
PUT /students/{id}
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "first_name": "أحمد",
  "phone": "0509876543"
}
```

**Note:** جميع الحقول اختيارية (يمكن تحديث حقل واحد أو أكثر)

**Response (200):**
```json
{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "محمد",
  "phone": "0509876543",
  ...
}
```

### Delete Student

```http
DELETE /students/{id}
Authorization: Bearer TOKEN
```

**Response (204):** No Content

### Get Student Enrollments

```http
GET /students/{id}/enrollments
Authorization: Bearer TOKEN
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "status": "active",
      "status_label": "نشط",
      "enrollment_date": "2024-01-01",
      "start_date": "2024-01-15",
      "end_date": "2024-06-15",
      "total_amount": "5000.00",
      "paid_amount": "2000.00",
      "remaining_amount": "3000.00",
      "discount_amount": "0.00",
      "groups": [
        {
          "id": 1,
          "name": "مجموعة البرمجة",
          "code": "PRG-001"
        }
      ],
      "courses": [
        {
          "id": 1,
          "name": "Python للمبتدئين",
          "code": "PY-101"
        }
      ],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Student Attendance

```http
GET /students/{id}/attendance?from_date=2024-01-01&to_date=2024-12-31
Authorization: Bearer TOKEN
```

**Query Parameters:**
- `from_date` (date, optional): من تاريخ (Y-m-d)
- `to_date` (date, optional): إلى تاريخ (Y-m-d)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "session_id": 10,
      "student_id": 1,
      "status": "present",
      "check_in_time": "09:00:00",
      "session": {
        "id": 10,
        "session_date": "2024-01-15",
        "group": {
          "id": 5,
          "name": "مجموعة البرمجة"
        }
      }
    }
  ],
  "stats": {
    "total_sessions": 45,
    "present": 40,
    "absent": 3,
    "late": 2,
    "excused": 0
  }
}
```

## 📋 Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - نجح الطلب |
| 201 | Created - تم الإنشاء بنجاح |
| 204 | No Content - تم الحذف بنجاح |
| 400 | Bad Request - طلب خاطئ |
| 401 | Unauthorized - غير مصرح (token غير صحيح) |
| 403 | Forbidden - ممنوع (لا توجد صلاحية) |
| 404 | Not Found - غير موجود |
| 422 | Unprocessable Entity - بيانات غير صحيحة |
| 500 | Internal Server Error - خطأ في السيرفر |

## ❌ Error Responses

### Validation Error (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": [
      "رقم الهاتف مستخدم مسبقاً"
    ],
    "email": [
      "البريد الإلكتروني غير صحيح"
    ]
  }
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)

```json
{
  "message": "This action is unauthorized."
}
```

### Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Student] 999"
}
```

## 🔒 Permissions

الصلاحيات المطلوبة لكل Endpoint:

| Endpoint | Permission |
|----------|-----------|
| GET /students | `view_any_student` |
| POST /students | `create_student` |
| GET /students/{id} | `view_student` |
| PUT /students/{id} | `update_student` |
| DELETE /students/{id} | `delete_student` |

## 🧪 Testing with Flutter

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class StudentApi {
  final String baseUrl = 'http://localhost:8000/api/v1';
  final String token;

  StudentApi(this.token);

  // Get all students
  Future<Map<String, dynamic>> getStudents({
    int page = 1,
    int perPage = 15,
    String? search,
    String? gender,
  }) async {
    var params = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (search != null) 'search': search,
      if (gender != null) 'gender': gender,
    };

    final uri = Uri.parse('$baseUrl/students').replace(queryParameters: params);
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load students');
    }
  }

  // Create student
  Future<Map<String, dynamic>> createStudent(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/students'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to create student');
    }
  }
}
```

## 📚 Swagger Documentation

للوصول إلى Interactive Documentation:

```
http://localhost:8000/api/documentation
```

---

**تاريخ الإنشاء:** 2026-03-31  
**النسخة:** 1.0.0  
**Base URL:** `/api/v1`

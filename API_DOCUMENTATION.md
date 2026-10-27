# API Documentation

## Authentication
All API endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Base URL
```
https://your-domain.com/api
```

---

## Students Endpoints

### 1. Get All Students
**GET** `/students`

**Query Parameters:**
- `search` (optional): Search by name or email
- `per_page` (optional): Number of results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "first_name": "أحمد",
      "last_name": "محمد",
      "email": "ahmed@example.com",
      "phone": "01234567890",
      ...
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### 2. Get Student Details
**GET** `/students/{id}`

**Response:**
```json
{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "محمد",
  "email": "ahmed@example.com",
  "enrollments": [...]
}
```

### 3. Get Student Grades
**GET** `/students/{id}/grades`

**Response:**
```json
{
  "grades": [...],
  "statistics": {
    "total_exams": 10,
    "average_percentage": 85.5,
    "passing_count": 9,
    "highest_grade": 98,
    "lowest_grade": 65
  }
}
```

### 4. Get Student Attendance
**GET** `/students/{id}/attendance`

**Response:**
```json
{
  "attendance": {
    "data": [...]
  },
  "statistics": {
    "present": 25,
    "absent": 2,
    "late": 1,
    "excused": 1
  }
}
```

### 5. Get Student Transcript
**GET** `/students/{id}/transcript`

**Response:**
```json
{
  "student": {...},
  "grades_by_course": {
    "Web Development": [...],
    "Mobile Development": [...]
  },
  "statistics": {...}
}
```

---

## Exams Endpoints

### 1. Get All Exams
**GET** `/exams`

**Query Parameters:**
- `status` (optional): Filter by status (scheduled, in_progress, completed, cancelled)
- `type` (optional): Filter by type (quiz, midterm, final, assignment, project)
- `course_id` (optional): Filter by course
- `per_page` (optional): Number of results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Final Exam",
      "type": "final",
      "exam_date": "2024-01-15 10:00:00",
      "course": {...},
      "group": {...}
    }
  ]
}
```

### 2. Get Exam Details
**GET** `/exams/{id}`

**Response:**
```json
{
  "id": 1,
  "title": "Final Exam",
  "description": "...",
  "type": "final",
  "exam_date": "2024-01-15 10:00:00",
  "duration": 120,
  "total_marks": 100,
  "passing_marks": 60,
  "status": "scheduled",
  "course": {...},
  "group": {...},
  "instructor": {...},
  "grades": [...]
}
```

### 3. Get Upcoming Exams
**GET** `/exams/upcoming`

**Query Parameters:**
- `limit` (optional): Number of exams to return (default: 10)

**Response:**
```json
[
  {
    "id": 1,
    "title": "Midterm Exam",
    "exam_date": "2024-01-10 14:00:00",
    ...
  }
]
```

---

## Grades Endpoints

### 1. Get All Grades
**GET** `/grades`

**Query Parameters:**
- `student_id` (optional): Filter by student
- `exam_id` (optional): Filter by exam
- `per_page` (optional): Number of results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "exam": {...},
      "student": {...},
      "marks_obtained": 85,
      "percentage": 85.0,
      "grade": "B+",
      "graded_at": "2024-01-05 12:00:00"
    }
  ]
}
```

### 2. Get Grade Details
**GET** `/grades/{id}`

**Response:**
```json
{
  "id": 1,
  "exam": {...},
  "student": {...},
  "marks_obtained": 85,
  "percentage": 85.0,
  "grade": "B+",
  "remarks": "Good performance",
  "graded_by": {...},
  "graded_at": "2024-01-05 12:00:00"
}
```

### 3. Create Grade
**POST** `/grades`

**Request Body:**
```json
{
  "exam_id": 1,
  "student_id": 5,
  "marks_obtained": 85,
  "enrollment_id": 10,
  "remarks": "Good performance"
}
```

**Response:**
```json
{
  "message": "Grade created successfully",
  "grade": {
    "id": 1,
    "exam": {...},
    "student": {...},
    "marks_obtained": 85,
    "percentage": 85.0,
    "grade": "B+"
  }
}
```

---

## Attendance Endpoints

### 1. Get All Attendance Records
**GET** `/attendance`

**Query Parameters:**
- `student_id` (optional): Filter by student
- `group_id` (optional): Filter by group
- `date_from` (optional): Filter from date (YYYY-MM-DD)
- `date_to` (optional): Filter to date (YYYY-MM-DD)
- `status` (optional): Filter by status (present, absent, late, excused)
- `per_page` (optional): Number of results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "student": {...},
      "group": {...},
      "date": "2024-01-10",
      "status": "present",
      "check_in_time": "09:00:00",
      "check_out_time": "12:00:00"
    }
  ]
}
```

### 2. Create Attendance Record
**POST** `/attendance`

**Request Body:**
```json
{
  "student_id": 1,
  "group_id": 5,
  "date": "2024-01-10",
  "status": "present",
  "check_in_time": "09:00:00",
  "check_out_time": "12:00:00",
  "notes": "On time"
}
```

**Response:**
```json
{
  "message": "Attendance recorded successfully",
  "attendance": {
    "id": 1,
    "student": {...},
    "group": {...},
    "date": "2024-01-10",
    "status": "present"
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "Validation error",
  "errors": {
    "field_name": [
      "Error message"
    ]
  }
}
```

### Not Found (404)
```json
{
  "message": "Resource not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

---

## Notes

1. All dates are in format: `YYYY-MM-DD HH:MM:SS`
2. All times are in format: `HH:MM:SS`
3. Pagination uses Laravel's default format with `data`, `links`, and `meta`
4. Grade is automatically calculated based on percentage:
   - A+: 95-100%
   - A: 90-94%
   - B+: 85-89%
   - B: 80-84%
   - C+: 75-79%
   - C: 70-74%
   - D: 60-69%
   - F: 0-59%

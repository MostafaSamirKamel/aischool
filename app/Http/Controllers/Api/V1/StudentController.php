<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Http\Resources\StudentResource;
use App\Http\Requests\Api\StoreStudentRequest;
use App\Http\Requests\Api\UpdateStudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class StudentController extends Controller
{
    /**
     * عرض قائمة الطلاب
     */
    #[OA\Get(
        path: '/api/v1/students',
        summary: 'عرض قائمة الطلاب',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'رقم الصفحة',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'عدد العناصر في الصفحة',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'بحث في الاسم أو الهاتف أو البريد',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'gender',
                in: 'query',
                description: 'فلتر حسب الجنس',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['male', 'female'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة الطلاب',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Student')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $user = auth()->user();

        $query = Student::query()
            ->with(['enrollments', 'user']);

        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Search
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by gender
        if (request('gender')) {
            $query->where('gender', request('gender'));
        }

        $perPage = request('per_page', 15);
        $students = $query->latest()->paginate($perPage);

        return StudentResource::collection($students);
    }

    /**
     * تخزين طالب جديد
     */
    #[OA\Post(
        path: '/api/v1/students',
        summary: 'إنشاء طالب جديد',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'phone', 'gender'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'محمد'),
                    new OA\Property(property: 'phone', type: 'string', example: '0501234567'),
                    new OA\Property(property: 'email', type: 'string', example: 'ahmad@example.com'),
                    new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
                    new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', example: '2000-01-01'),
                    new OA\Property(property: 'national_id', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'address', type: 'string', example: 'الرياض'),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        tags: ['Students'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'تم إنشاء الطالب بنجاح',
                content: new OA\JsonContent(ref: '#/components/schemas/Student')
            ),
            new OA\Response(response: 422, description: 'بيانات غير صحيحة'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        
        $student = Student::create($data);
        
        return response()->json(
            new StudentResource($student->load(['enrollments', 'user'])),
            201
        );
    }

    /**
     * عرض طالب محدد
     */
    #[OA\Get(
        path: '/api/v1/students/{id}',
        summary: 'عرض تفاصيل طالب',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الطالب',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تفاصيل الطالب',
                content: new OA\JsonContent(ref: '#/components/schemas/Student')
            ),
            new OA\Response(response: 404, description: 'الطالب غير موجود'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function show(Student $student): StudentResource
    {
        $this->authorize('view', $student);
        
        return new StudentResource(
            $student->load(['enrollments.groups', 'enrollments.courses', 'user'])
        );
    }

    /**
     * تحديث بيانات طالب
     */
    #[OA\Put(
        path: '/api/v1/students/{id}',
        summary: 'تحديث بيانات طالب',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
                    new OA\Property(property: 'date_of_birth', type: 'string', format: 'date'),
                    new OA\Property(property: 'national_id', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الطالب',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'تم التحديث بنجاح',
                content: new OA\JsonContent(ref: '#/components/schemas/Student')
            ),
            new OA\Response(response: 422, description: 'بيانات غير صحيحة'),
            new OA\Response(response: 404, description: 'الطالب غير موجود'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $this->authorize('update', $student);
        
        $student->update($request->validated());
        
        return response()->json(
            new StudentResource($student->load(['enrollments', 'user']))
        );
    }

    /**
     * حذف طالب
     */
    #[OA\Delete(
        path: '/api/v1/students/{id}',
        summary: 'حذف طالب',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الطالب',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'تم الحذف بنجاح'),
            new OA\Response(response: 404, description: 'الطالب غير موجود'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);
        
        $student->delete();
        
        return response()->json(null, 204);
    }

    /**
     * عرض التسجيلات الخاصة بطالب
     */
    #[OA\Get(
        path: '/api/v1/students/{id}/enrollments',
        summary: 'عرض تسجيلات الطالب',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الطالب',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'قائمة التسجيلات',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object'))
            ),
            new OA\Response(response: 404, description: 'الطالب غير موجود'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function enrollments(Student $student): JsonResponse
    {
        $this->authorize('view', $student);
        
        $enrollments = $student->enrollments()
            ->with(['groups', 'courses'])
            ->latest()
            ->get();
        
        return response()->json([
            'data' => $enrollments,
        ]);
    }

    /**
     * عرض الحضور الخاص بطالب
     */
    #[OA\Get(
        path: '/api/v1/students/{id}/attendance',
        summary: 'عرض سجل حضور الطالب',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'معرف الطالب',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'from_date',
                in: 'query',
                description: 'من تاريخ',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'to_date',
                in: 'query',
                description: 'إلى تاريخ',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'سجل الحضور',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object'))
            ),
            new OA\Response(response: 404, description: 'الطالب غير موجود'),
            new OA\Response(response: 401, description: 'غير مصرح'),
        ]
    )]
    public function attendance(Student $student): JsonResponse
    {
        $this->authorize('view', $student);
        
        $query = $student->attendanceRecords()
            ->with(['session.group']);
        
        if (request('from_date')) {
            $query->whereHas('session', fn($q) => 
                $q->where('session_date', '>=', request('from_date'))
            );
        }
        
        if (request('to_date')) {
            $query->whereHas('session', fn($q) => 
                $q->where('session_date', '<=', request('to_date'))
            );
        }
        
        $attendance = $query->latest()->get();
        
        return response()->json([
            'data' => $attendance,
            'stats' => [
                'total_sessions' => $attendance->count(),
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'excused' => $attendance->where('status', 'excused')->count(),
            ],
        ]);
    }
}

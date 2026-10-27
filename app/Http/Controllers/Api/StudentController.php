<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListStudentsRequest;
use App\Models\Student;
use App\Services\Api\StudentService;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $studentService)
    {
    }

    public function index(ListStudentsRequest $request)
    {
        $this->authorize('viewAny', Student::class);

        $students = $this->studentService->list($request->validated());

        return response()->json($students);
    }

    public function show($id)
    {
        $student = $this->studentService->findWithEnrollments((int) $id);
        $this->authorize('view', $student);

        return response()->json($student);
    }

    public function grades($id)
    {
        $student = Student::findOrFail((int) $id);
        $this->authorize('view', $student);

        $summary = $this->studentService->gradesSummary($student->id);

        return response()->json([
            'grades' => $summary['grades'],
            'statistics' => $summary['statistics'],
        ]);
    }

    public function attendance($id)
    {
        $student = Student::findOrFail((int) $id);
        $this->authorize('view', $student);

        $summary = $this->studentService->attendanceSummary($student->id);

        return response()->json([
            'attendance' => $summary['attendance'],
            'statistics' => $summary['statistics'],
        ]);
    }

    public function transcript($id)
    {
        $student = Student::findOrFail((int) $id);
        $this->authorize('view', $student);

        $transcript = $this->studentService->transcript($student->id);

        return response()->json([
            'student' => $transcript['student'],
            'grades_by_course' => $transcript['grades_by_course'],
            'statistics' => $transcript['statistics'],
        ]);
    }
}

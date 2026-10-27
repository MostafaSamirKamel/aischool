<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGradeRequest;
use App\Models\Grade;
use Illuminate\Http\Request;
use App\Services\Api\GradeService;

class GradeController extends Controller
{
    public function __construct(private readonly GradeService $gradeService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class);

        $grades = Grade::with(['exam.course', 'student'])
            ->when($request->student_id, function ($query, $studentId) {
                $query->where('student_id', $studentId);
            })
            ->when($request->exam_id, function ($query, $examId) {
                $query->where('exam_id', $examId);
            })
            ->orderBy('graded_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($grades);
    }

    public function show($id)
    {
        $grade = Grade::with(['exam.course', 'student', 'gradedBy'])
            ->findOrFail($id);

        $this->authorize('view', $grade);

        return response()->json($grade);
    }

    public function store(StoreGradeRequest $request)
    {
        $this->authorize('create', Grade::class);

        $gradeRecord = $this->gradeService->create($request->validated(), (int) auth()->id());

        return response()->json([
            'message' => 'Grade created successfully',
            'grade' => $gradeRecord->load(['exam.course', 'student']),
        ], 201);
    }
}

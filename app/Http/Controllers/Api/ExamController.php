<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListExamsRequest;
use App\Http\Requests\Api\UpcomingExamsRequest;
use App\Models\Exam;
use App\Services\Api\ExamService;

class ExamController extends Controller
{
    public function __construct(private readonly ExamService $examService)
    {
    }

    public function index(ListExamsRequest $request)
    {
        $this->authorize('viewAny', Exam::class);

        $exams = $this->examService->list($request->validated());

        return response()->json($exams);
    }

    public function show($id)
    {
        $exam = $this->examService->findDetailed((int) $id);
        $this->authorize('view', $exam);

        return response()->json($exam);
    }

    public function upcoming(UpcomingExamsRequest $request)
    {
        $this->authorize('viewAny', Exam::class);

        $exams = $this->examService->upcoming($request->validated());

        return response()->json($exams);
    }
}

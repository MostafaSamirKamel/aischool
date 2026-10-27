<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Services\Api\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $attendance = Attendance::with(['student', 'group'])
            ->when($request->student_id, function ($query, $studentId) {
                $query->where('student_id', $studentId);
            })
            ->when($request->group_id, function ($query, $groupId) {
                $query->where('group_id', $groupId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->where('date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->where('date', '<=', $dateTo);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($attendance);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $this->authorize('create', Attendance::class);

        $attendance = $this->attendanceService->create($request->validated(), (int) auth()->id());

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'attendance' => $attendance->load(['student', 'group']),
        ], 201);
    }
}

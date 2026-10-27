<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\LearningResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $student = $user->student;
        if (!$student) {
            return response()->json(['error' => 'Student profile not found for authenticated user.'], 404);
        }

        // Student basic profile
        $studentData = [
            'id' => (string) $student->id,
            'displayName' => $user->name,
            'studentCode' => $student->student_code ?? ('STD-' . $student->id),
            'email' => $user->email,
            'phone' => $student->phone ?? $user->phone ?? '',
            'avatarUrl' => $user->avatar ?? '',
            'joinDate' => $student->created_at?->format('Y-m-d') ?? '',
        ];

        // Enrollments & Courses
        $enrollments = $student->enrollments()->with(['courses', 'groups', 'installments'])->get();
        $courses = [];
        $registrations = [];
        $allPayments = [];
        $allInstallments = [];
        $groupIds = [];

        foreach ($enrollments as $enrollment) {
            $registrations[] = [
                'id' => 'reg-' . $enrollment->id,
                'courseId' => (string) ($enrollment->courses->first()?->id ?? $enrollment->id),
                'course' => $enrollment->courses->first()?->name ?? 'دورة تعليمية',
                'group' => $enrollment->groups->first()?->name ?? 'عامة',
                'date' => $enrollment->enrollment_date?->format('Y-m-d') ?? $enrollment->created_at?->format('Y-m-d'),
                'status' => $enrollment->status,
                'price' => (float) $enrollment->total_amount,
                'discount' => (float) $enrollment->discount,
                'paid' => (float) $enrollment->paid_amount,
                'remaining' => (float) $enrollment->getRemainingBalance(),
            ];

            foreach ($enrollment->groups as $group) {
                $groupIds[] = $group->id;
            }

            foreach ($enrollment->courses as $course) {
                $courses[] = [
                    'id' => (string) $course->id,
                    'title' => $course->name,
                    'code' => 'CRS-' . $course->id,
                    'group' => $enrollment->groups->first()?->name ?? '',
                    'teacher' => $enrollment->groups->first()?->instructor?->user?->name ?? 'المدرب',
                    'color' => 'violet',
                    'totalSessions' => (int) ($course->pivot->total_sessions ?? $course->sessions_count ?? 12),
                    'completedSessions' => (int) ($course->pivot->current_session ?? 0),
                    'attendedSessions' => (int) ($course->pivot->current_session ?? 0),
                    'price' => (float) ($course->pivot->course_price ?? $course->price),
                    'status' => $course->pivot->status ?? $enrollment->status,
                    'groupStatus' => ($enrollment->groups->first()?->status === 'completed' || $enrollment->groups->first()?->status === 'closed') ? 'closed' : 'open',
                    'nextSession' => '',
                    'description' => $course->description ?? '',
                    'image' => '',
                ];
            }

            foreach ($enrollment->payments()->where('status', 'completed')->get() as $p) {
                $allPayments[] = [
                    'id' => 'pay-' . $p->id,
                    'course' => $enrollment->courses->first()?->name ?? 'دورة',
                    'courseId' => (string) ($enrollment->courses->first()?->id ?? ''),
                    'date' => $p->payment_date?->format('Y-m-d') ?? $p->created_at?->format('Y-m-d'),
                    'amount' => (float) $p->amount,
                    'method' => $p->payment_method ?? 'cash',
                    'status' => 'paid',
                    'ref' => $p->payment_code ?? ('PAY-' . $p->id),
                ];
            }

            foreach ($enrollment->installments as $inst) {
                $allInstallments[] = [
                    'id' => 'inst-' . $inst->id,
                    'course' => $enrollment->courses->first()?->name ?? 'دورة',
                    'number' => (int) $inst->installment_number,
                    'amount' => (float) $inst->amount,
                    'paidAmount' => (float) $inst->paid_amount,
                    'due' => $inst->due_date?->format('Y-m-d') ?? '',
                    'status' => $inst->status,
                ];
            }
        }

        // Attendance records
        $attendance = [];
        $records = AttendanceRecord::where('student_id', $student->id)->with(['attendanceSession.course', 'attendanceSession.group'])->get();
        foreach ($records as $att) {
            $attendance[] = [
                'id' => 'att-' . $att->id,
                'course' => $att->attendanceSession?->course?->name ?? 'دورة',
                'group' => $att->attendanceSession?->group?->name ?? 'مجموعة',
                'date' => $att->attendanceSession?->attendance_date?->format('Y-m-d') ?? '',
                'time' => '',
                'status' => $att->status ?? 'present',
                'session' => 'جلسة رقم ' . ($att->attendanceSession?->session_number ?? 1),
            ];
        }

        // Exams
        $exams = [];
        $examList = Exam::whereIn('group_id', array_unique($groupIds))->orWhere('tenant_id', $student->tenant_id)->get();
        foreach ($examList as $ex) {
            $grade = Grade::where('student_id', $student->id)->where('exam_id', $ex->id)->first();
            $exams[] = [
                'id' => (string) $ex->id,
                'title' => $ex->title,
                'course' => $ex->course?->name ?? '',
                'date' => $ex->exam_date?->format('Y-m-d') ?? '',
                'duration' => (int) $ex->duration,
                'total' => (float) $ex->total_marks,
                'status' => $grade ? 'completed' : ($ex->isAvailableNow() ? 'available' : 'upcoming'),
                'score' => $grade ? (float) $grade->marks_obtained : null,
                'questions' => [],
            ];
        }

        // Assignments
        $assignments = [];
        $assignList = Assignment::whereIn('group_id', array_unique($groupIds))->orWhere('tenant_id', $student->tenant_id)->get();
        foreach ($assignList as $asn) {
            $submission = AssignmentSubmission::where('student_id', $student->id)->where('assignment_id', $asn->id)->first();
            $isPublished = $submission ? $submission->isPublished() : false;
            $isDraft = $submission ? $submission->isDraft() : false;

            $assignments[] = [
                'id' => (string) $asn->id,
                'title' => $asn->title,
                'course' => $asn->course?->name ?? '',
                'instructor' => $asn->instructor_name,
                'due' => $asn->due_date?->format('Y-m-d') ?? '',
                'total' => 10,
                'status' => match (true) {
                    !$submission => 'pending',
                    $isPublished => 'graded',
                    $isDraft => 'under_review',
                    default => 'submitted',
                },
                'status_label' => match (true) {
                    !$submission => 'لم يتم التسليم',
                    $isPublished => 'تم التقييم',
                    $isDraft => 'قيد المراجعة',
                    default => 'تم التسليم',
                },
                'score' => $isPublished ? (float) $submission->score : null,
                'feedback' => $isPublished ? $submission->instructor_feedback : null,
                'images_count' => $submission ? $submission->image_count : 0,
                'images' => $submission ? array_map(function ($idx) use ($submission) {
                    return route('assignments.submissions.image', ['submission' => $submission->id, 'index' => $idx]);
                }, array_keys($submission->images_list)) : [],
                'details' => $asn->description ?? '',
            ];
        }

        // Resources
        $resources = [];
        $resList = LearningResource::where('tenant_id', $student->tenant_id)->get();
        foreach ($resList as $res) {
            $resources[] = [
                'id' => (string) $res->id,
                'title' => $res->title,
                'course' => $res->course?->name ?? '',
                'type' => $res->type ?? 'file',
                'size' => '',
                'date' => $res->created_at?->format('Y-m-d') ?? '',
                'url' => $res->file_path ? asset('storage/' . $res->file_path) : '',
                'description' => $res->description ?? '',
            ];
        }

        return response()->json([
            'student' => $studentData,
            'notifications' => [],
            'courses' => $courses,
            'registrations' => $registrations,
            'teacherReviews' => [],
            'resources' => $resources,
            'exams' => $exams,
            'assignments' => $assignments,
            'payments' => $allPayments,
            'installments' => $allInstallments,
            'messages' => [],
            'attendance' => $attendance,
            'timeline' => $student->getAcademicTimeline(),
            'followUpStatus' => $student->follow_up_status,
        ]);
    }

    public function getExam(Request $request, $id): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $exam = Exam::with(['course', 'questions.options'])->find($id);
        if (!$exam) {
            return response()->json(['error' => 'Exam not found.'], 404);
        }

        if ($exam->tenant_id && $student->tenant_id && (int)$exam->tenant_id !== (int)$student->tenant_id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $grade = Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->first();

        $questions = $exam->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'type' => $q->type,
                'points' => (float) $q->points,
                'options' => $q->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'option_text' => $opt->option_text,
                    'order' => $opt->order,
                ]),
            ];
        });

        return response()->json([
            'id' => $exam->id,
            'title' => $exam->title,
            'description' => $exam->description,
            'duration' => $exam->duration,
            'total_marks' => (float) $exam->total_marks,
            'passing_marks' => (float) $exam->passing_marks,
            'has_attempted' => (bool) $grade,
            'score' => $grade ? (float) $grade->marks_obtained : null,
            'passed' => $grade ? $grade->isPass() : null,
            'questions' => $questions,
        ]);
    }

    public function startExam(Request $request, $id): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $exam = Exam::find($id);
        if (!$exam) {
            return response()->json(['error' => 'Exam not found.'], 404);
        }

        if (Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->exists()) {
            return response()->json(['error' => 'لقد قمت بتقديم هذا الامتحان بالفعل.'], 422);
        }

        $attempt = \App\Models\ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'completed_at' => null,
            ],
            [
                'tenant_id' => $student->tenant_id,
                'started_at' => now(),
                'total_score' => $exam->total_marks ?? 0,
            ]
        );

        return response()->json([
            'attempt_id' => $attempt->id,
            'started_at' => $attempt->started_at,
            'duration_minutes' => $exam->duration,
        ]);
    }

    public function submitExam(Request $request, $id): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $exam = Exam::with('questions.options')->find($id);
        if (!$exam) {
            return response()->json(['error' => 'Exam not found.'], 404);
        }

        if (Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->exists()) {
            return response()->json(['error' => 'تم تقديم هذا الامتحان مسبقاً.'], 422);
        }

        $attempt = \App\Models\ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if (!$attempt) {
            $attempt = \App\Models\ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'tenant_id' => $student->tenant_id,
                'started_at' => now(),
            ]);
        }

        $answers = $request->input('answers', []);
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($exam->questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;
                if ($selectedOptionId) {
                    $option = \App\Models\ExamQuestionOption::find($selectedOptionId);
                    $isCorrect = $option && (bool) $option->is_correct;

                    \App\Models\ExamAnswer::create([
                        'exam_attempt_id' => $attempt->id,
                        'exam_question_id' => $question->id,
                        'exam_question_option_id' => $selectedOptionId,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }

            $attempt->completed_at = now();
            if ($attempt->started_at) {
                $attempt->time_taken = now()->diffInSeconds($attempt->started_at);
            }
            $attempt->calculateScore();

            Grade::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                ],
                [
                    'tenant_id' => $student->tenant_id,
                    'marks_obtained' => $attempt->score,
                    'percentage' => $attempt->percentage,
                    'grade' => Grade::calculateGradeFromPercentage($attempt->percentage),
                    'remarks' => $attempt->passed ? 'Passed online exam' : 'Failed online exam',
                    'graded_at' => now(),
                ]
            );

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تسليم الامتحان بنجاح.',
                'score' => (float) $attempt->score,
                'total_score' => (float) $attempt->total_score,
                'percentage' => (float) $attempt->percentage,
                'passed' => (bool) $attempt->passed,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['error' => 'حدث خطأ أثناء حفظ الإجابات: ' . $e->getMessage()], 500);
        }
    }

    public function submitAssignment(Request $request, $id): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['error' => 'Assignment not found.'], 404);
        }

        // Check if multiple images or image files are uploaded
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            if (!is_array($images)) {
                $images = [$images];
            }

            try {
                $submission = app(\App\Services\HomeworkService::class)->submitHomework(
                    $assignment,
                    $student,
                    $images,
                    $request->input('notes', $request->input('submission_notes', ''))
                );

                return response()->json([
                    'success' => true,
                    'message' => 'تم تسليم صور الواجب بنجاح.',
                    'submission' => [
                        'id' => $submission->id,
                        'status' => $submission->status,
                        'images_count' => $submission->image_count,
                        'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                    ],
                ]);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return response()->json(['error' => $ve->getMessage(), 'errors' => $ve->errors()], 422);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|max:20480',
            ]);
            $filePath = $request->file('file')->store('assignments/submissions', 'public');
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($submission) {
            $data = [
                'tenant_id' => $student->tenant_id,
                'submission_notes' => $request->input('notes', $request->input('submission_notes', '')),
                'submitted_at' => now(),
                'status' => 'submitted',
                'published_at' => null,
            ];
            if ($filePath) {
                $data['file_path'] = $filePath;
            }
            $submission->update($data);
        } else {
            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'tenant_id' => $student->tenant_id,
                'submission_notes' => $request->input('notes', $request->input('submission_notes', '')),
                'file_path' => $filePath,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسليم الواجب بنجاح.',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                'file_url' => $submission->file_path ? asset('storage/' . $submission->file_path) : null,
            ],
        ]);
    }

    public function submitReview(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = \App\Models\InstructorReview::updateOrCreate(
            [
                'student_id' => $student->id,
                'instructor_id' => $validated['instructor_id'],
            ],
            [
                'tenant_id' => $student->tenant_id,
                'reviewer_type' => 'student',
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? '',
                'status' => 'approved',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك، تم حفظ التقييم بنجاح.',
            'review' => $review,
        ]);
    }

    public function getProjects(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $projects = \App\Models\StudentProject::where('student_id', $student->id)->with('course')->latest()->get();

        return response()->json([
            'projects' => $projects->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'course' => $p->course?->name ?? 'مشروع عام',
                'description' => $p->description,
                'file_url' => $p->file_path ? asset('storage/' . $p->file_path) : null,
                'github_url' => $p->github_url,
                'live_url' => $p->live_url,
                'status' => $p->status,
                'feedback' => $p->instructor_feedback,
                'created_at' => $p->created_at?->format('Y-m-d'),
            ]),
        ]);
    }

    public function submitProject(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'nullable|exists:courses,id',
            'description' => 'nullable|string',
            'github_url' => 'nullable|url',
            'live_url' => 'nullable|url',
            'file' => 'nullable|file|max:20480',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('projects', 'public');
        }

        $project = \App\Models\StudentProject::create([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->id,
            'course_id' => $validated['course_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'github_url' => $validated['github_url'] ?? null,
            'live_url' => $validated['live_url'] ?? null,
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع المشروع بنجاح وسيتم مراجعته من قبل المدرب.',
            'project' => $project,
        ]);
    }

    public function getTimeline(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        return response()->json([
            'timeline' => $student->getAcademicTimeline(),
            'follow_up_status' => $student->follow_up_status,
            'followUpStatus' => $student->follow_up_status,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use App\Models\SupervisionReport;
use App\Models\SupervisoryObservation;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EducationalSupervisionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Assign Permissions to Roles
        $permissions = [
            'branch_educational_supervisor',
            'view_supervision_dashboard',
            'create_supervisory_observations',
            'respond_supervisory_observations',
            'approve_supervisory_observations',
            'return_supervisory_observations',
            'create_instructor_evaluations',
            'publish_instructor_evaluations',
            'view_instructor_evaluations',
            'comment_instructor_evaluations',
            'appeal_instructor_evaluations',
            'respond_evaluation_appeals',
            'decide_evaluation_appeals',
            'prepare_supervision_reports',
            'publish_supervision_reports',
            'review_management_supervision_reports',
            'request_supervision_report_revision',
            'request_supervision_report_clarification',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $supervisorRole->givePermissionTo([
            'branch_educational_supervisor',
            'view_supervision_dashboard',
            'create_supervisory_observations',
            'approve_supervisory_observations',
            'return_supervisory_observations',
            'create_instructor_evaluations',
            'publish_instructor_evaluations',
            'view_instructor_evaluations',
            'respond_evaluation_appeals',
            'prepare_supervision_reports',
            'publish_supervision_reports',
        ]);

        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $instructorRole->givePermissionTo([
            'respond_supervisory_observations',
            'view_instructor_evaluations',
            'comment_instructor_evaluations',
            'appeal_instructor_evaluations',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'review_management_supervision_reports',
            'request_supervision_report_revision',
            'request_supervision_report_clarification',
            'decide_evaluation_appeals',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo($permissions);

        // 2. Fetch Cairo Branch Users
        $branchCairo = Tenant::find(1);
        $supervisorCairo = User::role('supervisor')->where('tenant_id', 1)->first()
            ?? User::where('tenant_id', 1)->where('email', 'like', '%supervisor%')->first();

        if (!$supervisorCairo) {
            $supervisorCairo = User::firstOrCreate(
                ['email' => 'supervisor.cairo@aischool.test'],
                [
                    'name' => 'أحمد إبراهيم (مشرف فرع القاهرة)',
                    'tenant_id' => 1,
                    'password' => bcrypt('password'),
                    'status' => 'active',
                ]
            );
            $supervisorCairo->assignRole('supervisor');
        } else {
            $supervisorCairo->assignRole('supervisor');
        }

        $instructorCairo = Instructor::where('tenant_id', 1)->first();
        $groupCairo = Group::where('tenant_id', 1)->first();

        if ($instructorCairo && $supervisorCairo) {
            // Seed Sample Supervisory Observations
            $obs1 = SupervisoryObservation::firstOrCreate(
                ['observation_code' => 'SOB-000001'],
                [
                    'tenant_id' => 1,
                    'supervisor_id' => $supervisorCairo->id,
                    'instructor_id' => $instructorCairo->id,
                    'group_id' => $groupCairo?->id,
                    'category' => 'attendance_not_recorded',
                    'title' => 'تأخر تسجيل حضور سيشن الأحد لمجموعة البرمجة',
                    'description' => 'لوحظ انتهاء الجلسة التعليمية المقررة دون رصد وحفظ سجلات حضور وغياب الطلاب.',
                    'required_action' => 'الدخول الفوري لشاشة الحضور وتسجيل حضور المجموعة وإرسال الإجراء للاعتماد.',
                    'expected_resolution_date' => Carbon::today()->addDays(2),
                    'priority' => 'urgent',
                    'status' => SupervisoryObservation::STATUS_NEW,
                ]
            );

            $obs2 = SupervisoryObservation::firstOrCreate(
                ['observation_code' => 'SOB-000002'],
                [
                    'tenant_id' => 1,
                    'supervisor_id' => $supervisorCairo->id,
                    'instructor_id' => $instructorCairo->id,
                    'group_id' => $groupCairo?->id,
                    'category' => 'homework_grading_delay',
                    'title' => 'تأخر تصحيح الواجب العملي الأول',
                    'description' => 'مضى أكثر من 48 ساعة على تسليم الطلاب للواجب دون وضع الدرجات والتعقيب.',
                    'required_action' => 'رصد درجات الواجب لجميع الطلاب وتقديم تغذية راجعة على الكود.',
                    'expected_resolution_date' => Carbon::today()->addDay(),
                    'priority' => 'normal',
                    'status' => SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW,
                    'instructor_response' => 'تم تصحيح جميع واجبات الطلاب المسلمة ورصد الدرجات وكتابة التغذية الراجعة.',
                    'instructor_responded_at' => Carbon::now()->subHours(3),
                ]
            );

            $obs3 = SupervisoryObservation::firstOrCreate(
                ['observation_code' => 'SOB-000003'],
                [
                    'tenant_id' => 1,
                    'supervisor_id' => $supervisorCairo->id,
                    'instructor_id' => $instructorCairo->id,
                    'group_id' => $groupCairo?->id,
                    'category' => 'session_preparation',
                    'title' => 'تحضير مشاريع التخرج المصغرة للوحدة الثانية',
                    'description' => 'تم الاتفاق على إعداد نماذج الأمثلة العملية للطلاب قبل بدء الحصة.',
                    'required_action' => 'رفع كود النموذج على المستودع وإتاحته للطلاب.',
                    'expected_resolution_date' => Carbon::today()->subDays(5),
                    'priority' => 'normal',
                    'status' => SupervisoryObservation::STATUS_COMPLETED,
                    'instructor_response' => 'تم رفع الكود ومشاركته مع الطلاب في شاشة المصادر.',
                    'instructor_responded_at' => Carbon::now()->subDays(4),
                    'approved_by' => $supervisorCairo->id,
                    'approved_at' => Carbon::now()->subDays(3),
                    'verification_notes' => 'تمت مراجعة الكود والتأكد من وصول الطلاب له بنجاح.',
                ]
            );

            // Seed Sample Official Monthly Evaluation
            $eval = InstructorEvaluation::firstOrCreate(
                ['evaluation_code' => 'IEV-000001'],
                [
                    'tenant_id' => 1,
                    'instructor_id' => $instructorCairo->id,
                    'supervisor_id' => $supervisorCairo->id,
                    'evaluation_type' => InstructorEvaluation::TYPE_MONTHLY,
                    'evaluation_period' => '2026-10',
                    'review_basis' => 'direct_observation',
                    'score_explanation_quality' => 4,
                    'score_student_interaction' => 5,
                    'score_practical_application' => 4,
                    'score_curriculum_adherence' => 5,
                    'score_time_management' => 3,
                    'score_homework_followup' => null, // Not Observed
                    'strengths' => 'تمكن علمي ممتاز، شرح مبسط، قدرة فائقة على تحفيز الطلاب وإشراكهم في النقاش.',
                    'areas_for_improvement' => 'الحاجة لتحسين تنظيم وقت الأنشطة الفردية لتجنب تمديد الحصة.',
                    'recommendations' => 'استخدام مؤقت زمني مرئي لكل تمرين برمجي داخل القاعة.',
                    'status' => InstructorEvaluation::STATUS_PUBLISHED,
                    'published_by' => $supervisorCairo->id,
                    'published_at' => Carbon::now()->subDays(2),
                    'version' => 1,
                ]
            );

            // Seed Sample Published Weekly Supervision Report
            $rep = SupervisionReport::firstOrCreate(
                ['report_code' => 'SR-WK-2026-W42-0001'],
                [
                    'tenant_id' => 1,
                    'report_type' => SupervisionReport::TYPE_WEEKLY,
                    'period_start' => Carbon::now()->startOfWeek()->toDateString(),
                    'period_end' => Carbon::now()->endOfWeek()->toDateString(),
                    'period_key' => '2026-W42',
                    'version' => 1,
                    'metrics_snapshot' => [
                        'scheduled_sessions' => 12,
                        'completed_sessions' => 10,
                        'postponed_sessions' => 1,
                        'canceled_sessions' => 1,
                        'missing_attendance' => 1,
                        'confirmed_absences' => 3,
                        'homework_awaiting_grading' => 4,
                        'new_observations_count' => 2,
                        'completed_observations_count' => 1,
                    ],
                    'summary' => 'أسبوع تعليمي منتظم شهد التزاماً عالياً من معظم المهندسين مع تنفيذ خطة الأنشطة العملية.',
                    'instructor_observations' => 'أداء متميز في التفاعل الصفي، مع تأخر محدود في تسجيل الحضور تم توجيهه بملاحظة رسمية.',
                    'group_observations' => 'المجموعات تسير وفق الخطة الزمنية المعتمدة للمناهج.',
                    'problems_encountered' => 'عطل فني مؤقت في أحد أجهزة العرض بالقاعة 2 وتم تداركه.',
                    'actions_taken' => 'تم عقد جلسة توجيه سريعة للمهندسين حول أهمية رصد الحضور بنهاية كل جلسة.',
                    'recommendations' => 'تزويد القاعات بأسلاك توصيل احتياطية وصيانة دورية للشاشات.',
                    'status' => SupervisionReport::STATUS_PUBLISHED,
                    'prepared_by' => $supervisorCairo->id,
                    'published_by' => $supervisorCairo->id,
                    'published_at' => Carbon::now()->subDay(),
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Group;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Tenant::all();
        
        foreach ($branches as $branch) {
            // Create Tracks for this branch
            $webTrack = Track::create([
                'tenant_id' => $branch->id,
                'name' => 'تطوير المواقع',
                'description' => 'مسار تعليمي متكامل لتعلم تطوير مواقع الويب من الصفر حتى الاحتراف',
                'order' => 1,
                'status' => 'active',
            ]);
            
            $mobileTrack = Track::create([
                'tenant_id' => $branch->id,
                'name' => 'تطوير تطبيقات الموبايل',
                'description' => 'مسار تعليمي شامل لتطوير تطبيقات iOS و Android',
                'order' => 2,
                'status' => 'active',
            ]);

            $dataTrack = Track::create([
                'tenant_id' => $branch->id,
                'name' => 'علوم البيانات والذكاء الاصطناعي',
                'description' => 'مسار متخصص في تحليل البيانات والتعلم الآلي',
                'order' => 3,
                'status' => 'active',
            ]);
            
            // Create Courses for Web Track
            $htmlCss = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $webTrack->id,
                'name' => 'HTML & CSS',
                'description' => 'تعلم أساسيات بناء صفحات الويب باستخدام HTML و CSS',
                'duration_months' => 2,
                'sessions_count' => 16,
                'price' => 1500.00,
                'prerequisites' => null,
                'order' => 1,
                'status' => 'active',
            ]);
            
            $javascript = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $webTrack->id,
                'name' => 'JavaScript',
                'description' => 'تعلم لغة JavaScript لإضافة التفاعل لمواقع الويب',
                'duration_months' => 3,
                'sessions_count' => 24,
                'price' => 2000.00,
                'prerequisites' => ['HTML & CSS'],
                'order' => 2,
                'status' => 'active',
            ]);
            
            $react = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $webTrack->id,
                'name' => 'React JS',
                'description' => 'بناء تطبيقات ويب متقدمة باستخدام React',
                'duration_months' => 4,
                'sessions_count' => 32,
                'price' => 3000.00,
                'prerequisites' => ['HTML & CSS', 'JavaScript'],
                'order' => 3,
                'status' => 'active',
            ]);
            
            $laravel = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $webTrack->id,
                'name' => 'Laravel',
                'description' => 'تطوير تطبيقات الويب باستخدام Laravel Framework',
                'duration_months' => 4,
                'sessions_count' => 32,
                'price' => 3500.00,
                'prerequisites' => ['HTML & CSS', 'JavaScript'],
                'order' => 4,
                'status' => 'active',
            ]);
            
            // Create Courses for Mobile Track
            $flutter = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $mobileTrack->id,
                'name' => 'Flutter',
                'description' => 'تطوير تطبيقات موبايل متعددة المنصات باستخدام Flutter',
                'duration_months' => 5,
                'sessions_count' => 40,
                'price' => 4000.00,
                'prerequisites' => ['أساسيات البرمجة'],
                'order' => 1,
                'status' => 'active',
            ]);
            
            $reactNative = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $mobileTrack->id,
                'name' => 'React Native',
                'description' => 'بناء تطبيقات موبايل باستخدام React Native',
                'duration_months' => 5,
                'sessions_count' => 40,
                'price' => 4000.00,
                'prerequisites' => ['JavaScript'],
                'order' => 2,
                'status' => 'active',
            ]);
            
            // Create Courses for Data Science Track
            $python = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $dataTrack->id,
                'name' => 'Python للبيانات',
                'description' => 'تعلم Python لتحليل البيانات والذكاء الاصطناعي',
                'duration_months' => 3,
                'sessions_count' => 24,
                'price' => 2500.00,
                'prerequisites' => null,
                'order' => 1,
                'status' => 'active',
            ]);
            
            $machineLearning = Course::create([
                'tenant_id' => $branch->id,
                'track_id' => $dataTrack->id,
                'name' => 'Machine Learning',
                'description' => 'أساسيات التعلم الآلي وبناء نماذج AI',
                'duration_months' => 4,
                'sessions_count' => 32,
                'price' => 3500.00,
                'prerequisites' => ['Python للبيانات'],
                'order' => 2,
                'status' => 'active',
            ]);
            
            // Create Classrooms for this branch
            $classrooms = [];
            for ($i = 1; $i <= 5; $i++) {
                $classrooms[] = Classroom::create([
                    'tenant_id' => $branch->id,
                    'name' => "قاعة {$i}",
                    'capacity' => rand(15, 30),
                    'location' => $i <= 2 ? 'الطابق الأول' : 'الطابق الثاني',
                    'description' => "قاعة دراسية مجهزة بالكامل",
                    'status' => 'active',
                    'equipment' => ['بروجيكتور', 'سبورة ذكية', 'أجهزة كمبيوتر'],
                ]);
            }

            // Get or create instructors for this branch
            $instructors = \App\Models\Instructor::where('tenant_id', $branch->id)->get();

            // If no instructors exist, create some
            if ($instructors->isEmpty()) {
                $newInstructors = [];
                // Create 3 instructors per branch
                for ($i = 1; $i <= 3; $i++) {
                    $instructorUser = User::create([
                        'tenant_id' => $branch->id,
                        'name' => "مدرس {$i} - {$branch->name}",
                        'email' => "instructor{$i}.{$branch->id}@example.com",
                        'password' => bcrypt('password'),
                        'phone' => '0100000000' . $i,
                        'is_super_admin' => false,
                    ]);

                    $newInstructors[] = \App\Models\Instructor::create([
                        'tenant_id' => $branch->id,
                        'user_id' => $instructorUser->id,
                        'employee_code' => 'INS' . str_pad($branch->id * 100 + $i, 5, '0', STR_PAD_LEFT),
                        'specialization' => ['تطوير الويب', 'برمجة التطبيقات', 'علوم البيانات'][rand(0, 2)],
                        'qualifications' => ['بكالوريوس علوم الحاسب', 'ماجستير في هندسة البرمجيات'],
                        'years_of_experience' => rand(3, 10),
                        'hired_date' => now()->subYears(rand(1, 5)),
                        'status' => 'active',
                        'contract_type' => 'full_time',
                        'phone' => '0100000000' . $i,
                    ]);
                }
                $instructors = collect($newInstructors);
            }

            // If still no instructors, skip group creation
            if ($instructors->isEmpty()) {
                continue;
            }
            
            // Create Groups for some courses
            $coursesToGroup = [
                $htmlCss, 
                $javascript, 
                $react, 
                $laravel, 
                $flutter,
                $python
            ];
            
            foreach ($coursesToGroup as $index => $course) {
                // Create 1-2 groups per course
                $groupCount = rand(1, 2);
                
                for ($g = 1; $g <= $groupCount; $g++) {
                    // Make half of the groups active (started in the past)
                    $startDate = ($g <= ceil($groupCount / 2))
                        ? Carbon::now()->subDays(rand(7, 30))
                        : Carbon::now()->addDays(rand(7, 30));
                    
                    Group::create([
                        'tenant_id' => $branch->id,
                        'course_id' => $course->id,
                        'classroom_id' => $classrooms[array_rand($classrooms)]->id,
                        'instructor_id' => $instructors->random()->id,
                        'supervisor_id' => null,
                        'name' => $course->name . ' - مجموعة ' . $g,
                        'capacity' => rand(15, 25),
                        'max_students' => rand(20, 30),
                        'schedule' => [
                            [
                                'day' => 'saturday',
                                'start_time' => '10:00',
                                'end_time' => '12:00',
                            ],
                            [
                                'day' => 'monday',
                                'start_time' => '10:00',
                                'end_time' => '12:00',
                            ],
                        ],
                        'start_date' => $startDate,
                        'end_date' => $startDate->copy()->addMonths($course->duration_months),
                        'status' => $startDate->isPast() ? 'active' : 'pending',
                        'notes' => null,
                    ]);
                }
            }
        }
        
        $this->command->info('✅ تم إنشاء المسارات التعليمية والكورسات والقاعات والمجموعات بنجاح!');
        $this->command->info('Total: ' . Track::withoutGlobalScopes()->count() . ' tracks, ' . Course::withoutGlobalScopes()->count() . ' courses, ' . Classroom::withoutGlobalScopes()->count() . ' classrooms, ' . Group::withoutGlobalScopes()->count() . ' groups');
    }
}

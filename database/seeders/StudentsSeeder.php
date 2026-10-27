<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Group;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Tenant::all();
        
        // Initialize global counter for unique codes
        $year = date('Y');
        $globalStudentNumber = 1;        $globalEnrollmentNumber = 1;
        $globalPaymentNumber = 1;        
        foreach ($branches as $branch) {
            // Create 10 students per branch
            for ($i = 1; $i <= 10; $i++) {
                try {
                    // Create user account for student
                    $user = User::create([
                        'tenant_id' => $branch->id,
                        'name' => "طالب {$globalStudentNumber} - " . uniqid(),
                        'email' => "student{$globalStudentNumber}." . uniqid() . "@school.test",
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]);
                    
                    // Assign student role
                    $user->assignRole('student');
                    
                    // Create student record with sequential code
                    $student = Student::create([
                        'tenant_id' => $branch->id,
                        'user_id' => $user->id,
                        'student_code' => 'STD' . $year . str_pad($globalStudentNumber, 4, '0', STR_PAD_LEFT),
                        'national_id' => '3' . rand(1000000000000, 9999999999999),
                        'birth_date' => Carbon::now()->subYears(rand(14, 25)),
                        'gender' => ['male', 'female'][array_rand(['male', 'female'])],
                        'phone' => '01' . rand(0, 5) . rand(10000000, 99999999),
                        'address' => 'العنوان التجريبي ' . $i,
                        'city' => ['القاهرة', 'الجيزة', 'الإسكندرية', 'طنطا'][array_rand(['القاهرة', 'الجيزة', 'الإسكندرية', 'طنطا'])],
                        'education_level' => ['ثانوية عامة', 'جامعي', 'متخرج'][array_rand(['ثانوية عامة', 'جامعي', 'متخرج'])],
                        'school_name' => 'مدرسة تجريبية',
                        'status' => 'active',
                    ]);
                    
                    // Increment global counter
                    $globalStudentNumber++;
                    
                    // Create parent account for some students
                    if ($i <= 5) {
                        $parent = User::create([
                            'tenant_id' => $branch->id,
                            'name' => "ولي أمر {$user->name} - " . uniqid(),
                            'email' => "parent{$globalStudentNumber}." . uniqid() . "@school.test",
                            'password' => Hash::make('password'),
                            'email_verified_at' => now(),
                        ]);
                        
                        $parent->assignRole('parent');
                        
                        // Link parent to student
                        $student->parents()->attach($parent->id, [
                            'relation' => 'father',
                            'is_primary' => true,
                            'can_pickup' => true,
                            'receive_notifications' => true,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error creating student {$globalStudentNumber} in branch {$branch->name}: " . $e->getMessage());
                    // Increment anyway to avoid stuck at same number
                    $globalStudentNumber++;
                    continue;
                }
            }
            
            // Get primary cash vault for this branch
            $branchVault = Vault::where('tenant_id', $branch->id)
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN name LIKE '%كاش%' OR name LIKE '%النقدية%' THEN 0 ELSE 1 END")
                ->first();

            // Get active groups for this branch
            $groups = Group::withoutGlobalScopes()->where('tenant_id', $branch->id)
                ->where('status', 'active')
                ->get();
            
            if ($groups->isEmpty()) {
                continue;
            }
            
            // Get students for this branch
            $students = Student::withoutGlobalScopes()->where('tenant_id', $branch->id)->get();
            
            // Enroll some students in groups
            foreach ($students->take(8) as $index => $student) {
                // Each student enrolls in 1-2 groups
                $groupsToEnroll = $groups->random(rand(1, min(2, $groups->count())));
                
                foreach ($groupsToEnroll as $group) {
                    // Check if already enrolled
                    if (
                        Enrollment::withoutGlobalScopes()->where('student_id', $student->id)
                        ->where('group_id', $group->id)
                        ->exists()) {
                        continue;
                    }
                    
                    $coursePrice = $group->course->price ?? 2000;
                    $discount = ($index % 3 === 0) ? ($coursePrice * 0.10) : 0; // 10% discount for some
                    $totalAmount = $coursePrice;
                    $paidAmount = rand(0, 1) ? $totalAmount - $discount : ($totalAmount - $discount) / 2;
                    
                    $enrollment = Enrollment::create([
                        'tenant_id' => $branch->id,
                        'student_id' => $student->id,
                        'group_id' => $group->id,
                        'enrollment_code' => 'ENR' . $year . str_pad($globalEnrollmentNumber++, 6, '0', STR_PAD_LEFT),
                        'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'total_amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'discount_reason' => $discount > 0 ? 'خصم الطالب المتفوق' : null,
                        'payment_plan' => $paidAmount >= ($totalAmount - $discount) ? 'full' : 'installments',
                        'installments_count' => $paidAmount >= ($totalAmount - $discount) ? null : rand(2, 4),
                        'status' => 'active',
                        'start_date' => $group->start_date,
                        'end_date' => $group->end_date,
                    ]);
                    
                    // Create payment record — vault_id is mandatory for completed payments
                    if ($paidAmount > 0 && $branchVault) {
                        Payment::create([
                            'tenant_id' => $branch->id,
                            'enrollment_id' => $enrollment->id,
                            'vault_id' => $branchVault->id,
                            'payment_code' => 'PAY' . $year . str_pad($globalPaymentNumber++, 6, '0', STR_PAD_LEFT),
                            'amount' => $paidAmount,
                            'payment_method' => ['cash', 'card', 'bank_transfer'][array_rand(['cash', 'card', 'bank_transfer'])],
                            'payment_date' => $enrollment->enrollment_date,
                            'status' => 'completed',
                            'received_by' => User::whereHas('roles', function($q) {
                                $q->where('name', 'secretary');
                            })->where('tenant_id', $branch->id)->first()?->id,
                        ]);
                    }
                    
                    // Update group capacity
                    $group->increment('capacity');
                }
            }
        }
        
        $this->command->info('✅ تم إنشاء الطلاب والتسجيلات والمدفوعات بنجاح!');
    }
}

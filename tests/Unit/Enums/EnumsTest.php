<?php

namespace Tests\Unit\Enums;

use App\Enums\UserStatus;
use App\Enums\StudentStatus;
use App\Enums\PaymentMethod;
use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use Tests\TestCase;

class EnumsTest extends TestCase
{
    /** @test */
    public function user_status_enum_has_correct_values(): void
    {
        $this->assertEquals('active', UserStatus::ACTIVE->value);
        $this->assertEquals('inactive', UserStatus::INACTIVE->value);
        $this->assertEquals('suspended', UserStatus::SUSPENDED->value);
        $this->assertEquals('pending', UserStatus::PENDING->value);
    }

    /** @test */
    public function user_status_enum_has_labels(): void
    {
        $this->assertEquals('نشط', UserStatus::ACTIVE->label());
        $this->assertEquals('غير نشط', UserStatus::INACTIVE->label());
    }

    /** @test */
    public function user_status_enum_has_colors(): void
    {
        $this->assertEquals('success', UserStatus::ACTIVE->color());
        $this->assertEquals('danger', UserStatus::SUSPENDED->color());
    }

    /** @test */
    public function user_status_enum_returns_options(): void
    {
        $options = UserStatus::options();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('active', $options);
        $this->assertEquals('نشط', $options['active']);
    }

    /** @test */
    public function student_status_enum_works_correctly(): void
    {
        $this->assertEquals('active', StudentStatus::ACTIVE->value);
        $this->assertEquals('نشط', StudentStatus::ACTIVE->label());
        $this->assertEquals('success', StudentStatus::ACTIVE->color());
    }

    /** @test */
    public function payment_method_enum_has_icons(): void
    {
        $this->assertNotEmpty(PaymentMethod::CASH->icon());
        $this->assertNotEmpty(PaymentMethod::CARD->icon());
    }

    /** @test */
    public function attendance_status_enum_has_icons(): void
    {
        $this->assertEquals('✓', AttendanceStatus::PRESENT->icon());
        $this->assertEquals('✗', AttendanceStatus::ABSENT->icon());
    }

    /** @test */
    public function gender_enum_works_correctly(): void
    {
        $this->assertEquals('male', Gender::MALE->value);
        $this->assertEquals('female', Gender::FEMALE->value);
        $this->assertEquals('ذكر', Gender::MALE->label());
        $this->assertEquals('أنثى', Gender::FEMALE->label());
    }
}

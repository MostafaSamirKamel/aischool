<?php

namespace Tests\Unit\Services;

use App\Services\Api\GradeCalculatorService;
use InvalidArgumentException;
use Tests\TestCase;

class GradeCalculatorServiceTest extends TestCase
{
    protected GradeCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradeCalculatorService();
    }

    /** @test */
    public function it_calculates_percentage_correctly(): void
    {
        $percentage = $this->service->calculatePercentage(45, 50);

        $this->assertSame(90.0, $percentage);
    }

    /** @test */
    public function it_maps_percentage_to_grade_correctly(): void
    {
        $this->assertSame('A+', $this->service->calculateGrade(96));
        $this->assertSame('A', $this->service->calculateGrade(91));
        $this->assertSame('C', $this->service->calculateGrade(70));
        $this->assertSame('F', $this->service->calculateGrade(40));
    }

    /** @test */
    public function it_throws_for_invalid_total_marks(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculatePercentage(10, 0);
    }

    /** @test */
    public function it_throws_when_marks_exceed_total(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculatePercentage(101, 100);
    }
}

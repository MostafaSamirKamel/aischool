<?php

namespace App\Filament\Admin\Pages\Reports;

use Filament\Pages\Page;
use App\Models\Course;
use App\Models\Track;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Mpdf\Mpdf;

class CourseFinancialReport extends Page
{
    use HasPageShield;

    protected string $view = 'filament.admin.pages.reports.course-financial-report';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static \UnitEnum|string|null $navigationGroup = 'التقارير المالية';
    
    protected static ?int $navigationSort = 4;

    public $track_id = null;
    public $status = null;
    public $date_from = null;
    public $date_to = null;

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function applyFilters(): void
    {
        // Trigger re-render
    }

    public function getTracks()
    {
        return Track::all();
    }

    public static function getNavigationLabel(): string
    {
        return 'تقرير إيرادات الكورسات';
    }

    public function getTitle(): string
    {
        return 'التقرير المالي للكورسات والمجموعات';
    }

    public function getHeading(): string
    {
        return 'التقرير المالي للكورسات والمجموعات';
    }

    public function getSubheading(): ?string
    {
        return 'متابعة شاملة لإيرادات الكورسات وما تم تحصيله من كل مجموعة';
    }

    public function getReportData()
    {
        $query = Course::with(['groups' => function($q) {
                $q->with(['instructor.user', 'enrollmentCourses.enrollment.installments']);
            }]);

        if ($this->track_id) {
            $query->where('track_id', $this->track_id);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $courses = $query->get();
        $reportData = collect();
        $totalAllCourses = 0;

        foreach ($courses as $course) {
            $courseData = [
                'course' => $course,
                'groups' => collect(),
                'total_course_revenue' => 0,
            ];

            foreach ($course->groups as $group) {
                $groupRevenue = 0;

                foreach ($group->enrollmentCourses as $ec) {
                    $enrollment = $ec->enrollment;
                    if ($enrollment && $enrollment->total_amount > 0) {
                        // Apply date filters if provided based on enrollment_date
                        if ($this->date_from && $enrollment->enrollment_date && $enrollment->enrollment_date->format('Y-m-d') < $this->date_from) {
                            continue;
                        }
                        if ($this->date_to && $enrollment->enrollment_date && $enrollment->enrollment_date->format('Y-m-d') > $this->date_to) {
                            continue;
                        }

                        $ratio = $ec->course_price / $enrollment->total_amount;
                        $groupRevenue += ($enrollment->getEffectivePaidAmount() * $ratio);
                    }
                }

                $courseData['groups']->push([
                    'group' => $group,
                    'revenue' => round($groupRevenue, 2),
                ]);

                $courseData['total_course_revenue'] += $groupRevenue;
            }

            $totalAllCourses += $courseData['total_course_revenue'];
            $reportData->push($courseData);
        }

        return [
            'courses' => $reportData,
            'totalAllCourses' => $totalAllCourses,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'reportData' => $this->getReportData(),
        ];
    }

    public function exportToExcel()
    {
        $reportData = $this->getReportData();

        return response()->stream(function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['التقرير المالي للكورسات والمجموعات']);
            fputcsv($handle, ['تاريخ التقرير', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['إجمالي الإيرادات', $reportData['totalAllCourses']]);
            fputcsv($handle, []);

            foreach ($reportData['courses'] as $courseData) {
                fputcsv($handle, ['الكورس', $courseData['course']->name]);
                fputcsv($handle, ['إجمالي إيرادات الكورس', $courseData['total_course_revenue']]);
                fputcsv($handle, ['المجموعة', 'المدرس', 'عدد الطلاب', 'المبلغ المحصل']);
                
                foreach ($courseData['groups'] as $groupData) {
                    fputcsv($handle, [
                        $groupData['group']->name,
                        $groupData['group']->instructor?->user?->name ?? 'غير محدد',
                        $groupData['group']->enrollmentCourses->count(),
                        $groupData['revenue']
                    ]);
                }
                fputcsv($handle, []);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="تقرير-إيرادات-الكورسات-' . now()->format('Y-m-d-H-i-s') . '.csv"'
        ]);
    }

    public function exportToPdf()
    {
        $reportData = $this->getReportData();

        $html = view('filament.admin.pages.reports.course-financial-report-pdf', [
            'reportData' => $reportData,
        ])->render();

        $filename = 'تقرير-إيرادات-الكورسات-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response()->stream(
            function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
}

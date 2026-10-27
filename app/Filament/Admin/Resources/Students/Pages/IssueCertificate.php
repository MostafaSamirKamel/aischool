<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Models\Student;
use App\Models\Course;
use App\Services\CertificatePdfService;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class IssueCertificate extends Page
{
    protected static string $resource = \App\Filament\Admin\Resources\Students\StudentResource::class;

    protected string $view = 'filament.admin.resources.students.pages.issue-certificate';

    protected static ?string $title = 'إصدار شهادة';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('إصدار شهادة إتمام كورس')
                    ->description('اختر الطالب والكورس لإصدار الشهادة')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $tenantId = auth()->user()->tenant_id;
                                return Student::where('status', 'active')
                                    ->when(!auth()->user()->is_super_admin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where(function ($q) use ($search) {
                                        $q->where('student_code', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%")
                                            ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                                    })
                                    ->with('user')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($s) => [$s->id => "{$s->student_code} - " . ($s->user?->name ?? 'طالب')]);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $student = Student::with('user')->find($value);
                                return $student ? "{$student->student_code} - " . ($student->user?->name ?? 'طالب') : null;
                            })
                            ->required(),

                        Forms\Components\Select::make('course_id')
                            ->label('الكورس')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $tenantId = auth()->user()->tenant_id;
                                return Course::where('status', 'active')
                                    ->when(!auth()->user()->is_super_admin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->pluck('name', 'id');
                            })
                            ->required(),

                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('⚠️ ملاحظة: يجب أن يكون الطالب قد نجح في جميع امتحانات الكورس لإصدار الشهادة'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generate()
    {
        $data = $this->form->getState();

        $student = Student::find($data['student_id']);
        $course = Course::find($data['course_id']);

        if (!$student || !$course) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('الطالب أو الكورس غير موجود')
                ->send();
            return;
        }

        try {
            $pdfService = new CertificatePdfService();
            return response()->streamDownload(function () use ($pdfService, $student, $course) {
                echo $pdfService->generate($student, $course)->output();
            }, 'certificate_' . $student->id . '_' . $course->id . '.pdf');
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body($e->getMessage())
                ->send();
            return;
        }
    }
}

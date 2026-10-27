<?php

namespace App\Filament\Admin\Pages;

use App\Models\Instructor;
use App\Models\InstructorPayment;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class GenerateInstructorPayments extends Page
{
    use HasPageShield;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.admin.pages.generate-instructor-payments';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الأجور';

    protected static ?string $navigationLabel = 'توليد أجور المدرسين';

    protected static ?string $title = 'توليد أجور المدرسين';

    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public ?string $month = null;
    public ?int $instructorId = null;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateAll')
                ->label('توليد لجميع المدرسين')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('توليد أجور لجميع المدرسين')
                ->modalDescription('سيتم توليد أجور شهرية لجميع المدرسين النشطين للشهر المحدد.')
                ->form([
                    DatePicker::make('month')
                        ->label('الشهر')
                        ->displayFormat('Y-m')
                        ->format('Y-m')
                        ->default(now()->format('Y-m'))
                        ->required(),
                    TextInput::make('bonus_percentage')
                        ->label('نسبة بونص على مبلغ الجلسات %')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->helperText('يتم تطبيقها على مبلغ الجلسات فقط لكل مدرس')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $month = Carbon::parse($data['month']);
                    $bonusPercentage = (float) ($data['bonus_percentage'] ?? 0);
                    $generated = InstructorPayment::generateMonthlyPayments(
                        auth()->user()->tenant_id,
                        $month
                    );

                    if ($bonusPercentage > 0) {
                        foreach ($generated as $payment) {
                            $sessionsAmount = (float) ($payment->sessions_amount ?? 0);
                            $bonusAmount = round($sessionsAmount * ($bonusPercentage / 100), 2);

                            $payment->update([
                                'bonus_amount' => $bonusAmount,
                                'total_amount' => (float) $payment->base_amount
                                    + $sessionsAmount
                                    + $bonusAmount
                                    - (float) $payment->deduction_amount,
                            ]);
                        }
                    }

                    Notification::make()
                        ->title('تم توليد الأجور بنجاح')
                        ->body('تم توليد ' . count($generated) . ' سجل أجر للمدرسين.' . ($bonusPercentage > 0 ? ' مع تطبيق بونص ' . $bonusPercentage . '%.' : ''))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),

            Action::make('generateSingle')
                ->label('توليد لمدرس واحد')
                ->icon('heroicon-o-user')
                ->color('primary')
                ->form([
                    Select::make('instructor_id')
                        ->label('المدرس')
                        ->options(Instructor::where('tenant_id', auth()->user()->tenant_id)
                            ->where('status', 'active')
                            ->with('user')
                            ->get()
                            ->pluck('user.name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('period_start')
                        ->label('بداية الفترة')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('period_end')
                        ->label('نهاية الفترة')
                        ->default(now()->endOfMonth())
                        ->required(),
                    TextInput::make('bonus_percentage')
                        ->label('نسبة بونص على مبلغ الجلسات %')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $instructor = Instructor::find($data['instructor_id']);
                    $bonusPercentage = (float) ($data['bonus_percentage'] ?? 0);
                    
                    $payment = InstructorPayment::generateForInstructor(
                        $data['instructor_id'],
                        auth()->user()->tenant_id,
                        Carbon::parse($data['period_start']),
                        Carbon::parse($data['period_end']),
                        $instructor->hourly_rate ?? 100,
                        $instructor->base_salary ?? 0
                    );

                    if ($bonusPercentage > 0) {
                        $sessionsAmount = (float) ($payment->sessions_amount ?? 0);
                        $bonusAmount = round($sessionsAmount * ($bonusPercentage / 100), 2);

                        $payment->update([
                            'bonus_amount' => $bonusAmount,
                            'total_amount' => (float) $payment->base_amount
                                + $sessionsAmount
                                + $bonusAmount
                                - (float) $payment->deduction_amount,
                        ]);

                        $payment->refresh();
                    }

                    Notification::make()
                        ->title('تم توليد الأجر بنجاح')
                        ->body('تم توليد أجر بقيمة ' . number_format((float) $payment->total_amount, 2) . ' ل' . $instructor->user->name)
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }

    public function getViewData(): array
    {
        $recentPayments = InstructorPayment::where('tenant_id', auth()->user()->tenant_id)
            ->with(['instructor.user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_pending' => InstructorPayment::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'pending')
                ->count(),
            'total_approved' => InstructorPayment::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'approved')
                ->count(),
            'total_paid' => InstructorPayment::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'paid')
                ->count(),
            'this_month_amount' => InstructorPayment::where('tenant_id', auth()->user()->tenant_id)
                ->whereMonth('period_start', now()->month)
                ->whereYear('period_start', now()->year)
                ->sum('total_amount'),
        ];

        return compact('recentPayments', 'stats');
    }
}

<?php

namespace App\Filament\Admin\Pages;

use App\Models\SecretaryAttendance as SecretaryAttendanceModel;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SecretaryAttendance extends Page
{
    protected string $view = 'filament.admin.pages.secretary-attendance';

    protected static ?string $navigationLabel = 'حضور السكرتيرة';

    protected static ?string $title = 'تسجيل حضور وانصراف السكرتيرة';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-finger-print';

    protected static \UnitEnum|string|null $navigationGroup = 'الموارد البشرية';

    protected static ?int $navigationSort = 100;

    public ?int $editRecordId = null;

    public ?string $editCheckIn = null;

    public ?string $editCheckOut = null;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->is_super_admin || $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']));
    }

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasAnyRole(['secretary', 'branch_manager', 'super_admin']), 403);
    }

    public function checkIn(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $existing = SecretaryAttendanceModel::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            Notification::make()
                ->warning()
                ->title('تم تسجيل الحضور مسبقاً')
                ->body('لا يمكن التسجيل أكثر من مرة في نفس اليوم.')
                ->send();
            return;
        }

        SecretaryAttendanceModel::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in_at' => now(),
            'work_minutes' => 0,
        ]);

        Notification::make()
            ->success()
            ->title('تم تسجيل الحضور')
            ->body('تم حفظ وقت الحضور من السيرفر.')
            ->send();
    }

    public function checkOut(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $record = SecretaryAttendanceModel::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$record) {
            Notification::make()
                ->danger()
                ->title('لا يوجد حضور مسجل')
                ->body('لا يمكن تسجيل الانصراف بدون حضور.')
                ->send();
            return;
        }

        if ($record->check_out_at) {
            Notification::make()
                ->warning()
                ->title('تم تسجيل الانصراف مسبقاً')
                ->body('لا يمكن تسجيل الانصراف أكثر من مرة في نفس اليوم.')
                ->send();
            return;
        }

        $checkOutAt = now();
        $workMinutes = $record->check_in_at
            ? $record->check_in_at->diffInMinutes($checkOutAt)
            : 0;

        $record->update([
            'check_out_at' => $checkOutAt,
            'work_minutes' => $workMinutes,
        ]);

        Notification::make()
            ->success()
            ->title('تم تسجيل الانصراف')
            ->body('تم حساب عدد ساعات العمل تلقائياً.')
            ->send();
    }

    public function startEdit(int $recordId): void
    {
        $this->authorizeAdmin();

        $record = SecretaryAttendanceModel::query()->findOrFail($recordId);
        $this->editRecordId = $record->id;
        $this->editCheckIn = optional($record->check_in_at)->format('H:i');
        $this->editCheckOut = optional($record->check_out_at)->format('H:i');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editRecordId', 'editCheckIn', 'editCheckOut']);
    }

    public function saveEdit(): void
    {
        $this->authorizeAdmin();

        $this->validate([
            'editCheckIn' => ['required', 'date_format:H:i'],
            'editCheckOut' => ['nullable', 'date_format:H:i'],
        ]);

        $record = SecretaryAttendanceModel::query()->findOrFail($this->editRecordId);
        $date = Carbon::parse($record->date)->format('Y-m-d');

        $checkInAt = Carbon::parse($date . ' ' . $this->editCheckIn);
        $checkOutAt = $this->editCheckOut ? Carbon::parse($date . ' ' . $this->editCheckOut) : null;

        if ($checkOutAt && $checkOutAt->lessThan($checkInAt)) {
            Notification::make()
                ->danger()
                ->title('وقت الانصراف غير صالح')
                ->body('وقت الانصراف يجب أن يكون بعد وقت الحضور.')
                ->send();
            return;
        }

        $record->check_in_at = $checkInAt;
        $record->check_out_at = $checkOutAt;
        $record->work_minutes = $checkOutAt ? $checkInAt->diffInMinutes($checkOutAt) : 0;
        $record->save();

        $this->cancelEdit();

        Notification::make()
            ->success()
            ->title('تم حفظ التعديل')
            ->body('تم تحديث السجل بنجاح.')
            ->send();
    }

    public function getTodayRecord(): ?SecretaryAttendanceModel
    {
        $user = Auth::user();

        return SecretaryAttendanceModel::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();
    }

    public function getMonthStats(): array
    {
        $user = Auth::user();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $query = SecretaryAttendanceModel::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        $daysCount = (clone $query)->count();
        $totalMinutes = (int) (clone $query)->sum('work_minutes');

        return [
            'daysCount' => $daysCount,
            'totalMinutes' => $totalMinutes,
        ];
    }

    public function getMonthRecords(): array
    {
        $user = Auth::user();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return SecretaryAttendanceModel::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->get()
            ->all();
    }

    public function getMonthLabel(): string
    {
        return now()->locale('ar')->translatedFormat('F Y');
    }

    public function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }

    public function isAdminUser(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['branch_manager', 'super_admin']);
    }

    protected function authorizeAdmin(): void
    {
        abort_unless($this->isAdminUser(), 403);
    }
}

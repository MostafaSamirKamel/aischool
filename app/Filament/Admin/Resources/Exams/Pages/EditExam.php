<?php

namespace App\Filament\Admin\Resources\Exams\Pages;

use App\Filament\Admin\Resources\Exams\ExamResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExam extends EditRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه حذف امتحانات
        if (!$user->is_super_admin) {
            $actions[] = DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    try {
                        // just validate - actual delete fires model events
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الامتحان')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                        $action->cancel();
                    }
                })
                ->using(function ($record, DeleteAction $action) {
                    try {
                        $record->delete();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('لا يمكن حذف الامتحان')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                        $action->halt();
                    }
                });

            $actions[] = ForceDeleteAction::make()
                ->using(function ($record, ForceDeleteAction $action) {
                    try {
                        // Force delete: bypass soft-delete, still need cascade
                        $record->questions()->each(function ($question) {
                            $question->answers()->delete();
                            $question->options()->forceDelete();
                            $question->forceDelete();
                        });
                        $record->attempts()->each(fn($a) => $a->answers()->delete() && $a->delete());
                        $record->grades()->delete();
                        $record->forceDelete();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('حدث خطأ أثناء الحذف')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                        $action->halt();
                    }
                });

            $actions[] = RestoreAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_exam.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_exam.title');
    }

}

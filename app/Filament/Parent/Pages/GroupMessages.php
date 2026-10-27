<?php

namespace App\Filament\Parent\Pages;

use BackedEnum;
use Filament\Tables;
use Filament\Pages\Page;
use App\Models\Enrollment;
use Filament\Tables\Table;
use App\Models\GroupMessage;
use Filament\Actions\ViewAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class GroupMessages extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.parent.pages.group-messages';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?int $navigationSort = 50;

    public static function getNavigationLabel(): string
    {
        return __('رسائل المجموعة');
    }

    public function getTitle(): string
    {
        return __('رسائل المجموعة');
    }

    public function getHeading(): string
    {
        return __('رسائل المجموعة');
    }

    public function table(Table $table): Table
    {
        $selectedStudentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        // Get all group IDs the student is enrolled in
        $groupIds = $selectedStudentId ? Enrollment::where('student_id', $selectedStudentId)
            ->where('status', 'active')
            ->with('groups')
            ->get()
            ->flatMap(fn($enrollment) => $enrollment->groups->pluck('id'))
            ->unique()
            ->toArray() : [];

        return $table
            ->query(
                GroupMessage::query()
                    ->when(!empty($groupIds), fn($q) => $q->whereIn('group_id', $groupIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->where('status', 'sent')
                    ->latest('sent_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('filament/student/group_messages.المجموعة'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('filament/student/group_messages.الموضوع'))
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->limit(50),

                Tables\Columns\TextColumn::make('message')
                    ->label(__('filament/student/group_messages.الرسالة'))
                    ->limit(100)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\IconColumn::make('priority')
                    ->label(__('filament/student/group_messages.الأولوية'))
                    ->icon(fn (string $state): string => match ($state) {
                        'high' => 'heroicon-o-exclamation-circle',
                        'normal' => 'heroicon-o-minus-circle',
                        'low' => 'heroicon-o-information-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'normal' => 'warning',
                        'low' => 'info',
                    }),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('filament/student/group_messages.تاريخالإرسال'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (GroupMessage $record): string => 
                        $record->sent_at->format('Y-m-d H:i')
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label(__('filament/student/group_messages.المجموعة'))
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label(__('filament/student/group_messages.الأولوية'))
                    ->options([
                        'high' => 'عالية',
                        'normal' => 'عادية',
                        'low' => 'منخفضة',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('filament/student/group_messages.عرض'))
                    ->modalHeading(fn (GroupMessage $record): string => $record->subject)
                    ->modalContent(function (GroupMessage $record) {
                        return view('filament.parent.pages.view-message-modal', [
                            'record' => $record,
                        ]);
                    })
                    ->modalWidth('xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter(),
            ])
            ->bulkActions([])
            ->defaultSort('sent_at', 'desc')
            ->emptyStateHeading('لا توجد رسائل')
            ->emptyStateDescription('لم يتم إرسال أي رسائل لمجموعاتك بعد')
            ->emptyStateIcon('heroicon-o-envelope');
    }
}

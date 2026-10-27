<?php

namespace App\Filament\Instructor\Resources\LearningResources\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class LearningResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament/instructor/learning_resource_resource.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('type')
                    ->label(__('filament/instructor/learning_resource_resource.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pdf' => 'PDF',
                        'video' => 'فيديو',
                        'presentation' => 'PPT',
                        'document' => 'مستند',
                        'image' => 'صورة',
                        'audio' => 'صوت',
                        'link' => 'رابط',
                        default => 'أخرى',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'video' => 'info',
                        'presentation' => 'warning',
                        'document' => 'primary',
                        'link' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pdf' => 'heroicon-o-document-text',
                        'video' => 'heroicon-o-video-camera',
                        'presentation' => 'heroicon-o-presentation-chart-bar',
                        'document' => 'heroicon-o-document',
                        'image' => 'heroicon-o-photo',
                        'audio' => 'heroicon-o-musical-note',
                        'link' => 'heroicon-o-link',
                        default => 'heroicon-o-document',
                    }),

                TextColumn::make('course.name')
                    ->label(__('filament/instructor/learning_resource_resource.course.name'))
                    ->sortable()
                    ->default('-')
                    ->toggleable(),

                TextColumn::make('group.name')
                    ->label(__('filament/instructor/learning_resource_resource.group.name'))
                    ->sortable()
                    ->default('-')
                    ->toggleable(),

                TextColumn::make('session_number')
                    ->label('رقم المحاضرة')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->formatStateUsing(fn($state) => $state ? "محاضرة #{$state}" : '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sessionSchedule.date')
                    ->label('تاريخ الجلسة')
                    ->date('Y-m-d')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('visibility')
                    ->label(__('filament/instructor/learning_resource_resource.visibility'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'الجميع',
                        'course' => 'الكورس',
                        'group' => 'المجموعة',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'course' => 'info',
                        'group' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('file_size')
                    ->label(__('filament/instructor/learning_resource_resource.file_size'))
                    ->formatStateUsing(function ($record) {
                        return $record->getFileSizeFormatted();
                    })
                    ->toggleable(),

                TextColumn::make('download_count')
                    ->label(__('filament/instructor/learning_resource_resource.download_count'))
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('view_count')
                    ->label(__('filament/instructor/learning_resource_resource.view_count'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_downloadable')
                    ->label(__('filament/instructor/learning_resource_resource.is_downloadable'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('filament/instructor/learning_resource_resource.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament/instructor/learning_resource_resource.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('filament/instructor/learning_resource_resource.filter_type'))
                    ->options([
                        'pdf' => 'PDF',
                        'video' => __('filament/instructor/learning_resource_resource.type.video'),
                        'presentation' => __('filament/instructor/learning_resource_resource.type.presentation'),
                        'document' => __('filament/instructor/learning_resource_resource.type.document'),
                        'image' => __('filament/instructor/learning_resource_resource.type.image'),
                        'audio' => __('filament/instructor/learning_resource_resource.type.audio'),
                        'link' => __('filament/instructor/learning_resource_resource.type.link'),
                        'other' => __('filament/instructor/learning_resource_resource.type.other'),
                    ]),

                SelectFilter::make('visibility')
                    ->label(__('filament/instructor/learning_resource_resource.filter_visibility'))
                    ->options([
                        'all' => __('filament/instructor/learning_resource_resource.visibility_all'),
                        'course' => __('filament/instructor/learning_resource_resource.visibility_course_short'),
                        'group' => __('filament/instructor/learning_resource_resource.visibility_group_short'),
                    ]),

                SelectFilter::make('is_active')
                    ->label(__('filament/instructor/learning_resource_resource.filter_status'))
                    ->options([
                        1 => __('filament/instructor/learning_resource_resource.status_active'),
                        0 => __('filament/instructor/learning_resource_resource.status_inactive'),
                    ]),

                SelectFilter::make('course_id')
                    ->label(__('filament/instructor/learning_resource_resource.filter_course'))
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('group_id')
                    ->label(__('filament/instructor/learning_resource_resource.filter_group'))
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('session_number')
                    ->label('رقم المحاضرة')
                    ->options(function () {
                        return \App\Models\LearningResource::query()
                            ->whereNotNull('session_number')
                            ->distinct()
                            ->pluck('session_number', 'session_number')
                            ->mapWithKeys(fn ($value) => [$value => "محاضرة #{$value}"])
                            ->toArray();
                    }),

                SelectFilter::make('session_schedule_id')
                    ->label('الجلسة المجدولة')
                    ->relationship('sessionSchedule', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->formatDisplayName()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('filament/instructor/learning_resource_resource.action_view'))
                    ->modalContent(fn ($record) => view('filament.instructor.modals.resource-details', [
                        'resource' => $record
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('filament/instructor/learning_resource_resource.action_close')),
                EditAction::make()
                    ->label(__('filament/instructor/learning_resource_resource.action_edit')),
                DeleteAction::make()
                    ->label(__('filament/instructor/learning_resource_resource.action_delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('filament/instructor/learning_resource_resource.action_delete_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

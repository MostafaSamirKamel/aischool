<?php

namespace App\Filament\Admin\Resources\AssignmentSubmissionResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class AssignmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('assignment_id')
                    ->label(__('filament/admin/assignment_submission_resource.assignment_id'))
                    ->relationship('assignment', 'title')
                    ->required()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Select::make('student_id')
                    ->label(__('filament/admin/assignment_submission_resource.student_id'))
                    ->relationship('student.user', 'name')
                    ->required()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Textarea::make('submission_notes')
                    ->label(__('filament/admin/assignment_submission_resource.submission_notes'))
                    ->rows(3)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('file_path')
                    ->label(__('filament/admin/assignment_submission_resource.file_path'))
                    ->disabled()
                    ->dehydrated()
                    ->downloadable()
                    ->openable(),

                Forms\Components\DateTimePicker::make('submitted_at')
                    ->label(__('filament/admin/assignment_submission_resource.submitted_at'))
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Fieldset::make(__('filament/admin/assignment_submission_resource.التصحيح'))
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label(__('filament/admin/assignment_submission_resource.score'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn ($record) => $record?->assignment?->max_score ?? 100)
                            ->required(),

                        Forms\Components\Textarea::make('instructor_feedback')
                            ->label(__('filament/admin/assignment_submission_resource.instructor_feedback'))
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label(__('filament/admin/assignment_submission_resource.status'))
                            ->options([
                                'pending' => __('filament/admin/assignment_submission_resource.status.pending'),
                                'submitted' => __('filament/admin/assignment_submission_resource.status.submitted'),
                                'graded' => __('filament/admin/assignment_submission_resource.status.graded'),
                                'late' => __('filament/admin/assignment_submission_resource.status.late'),
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

<?php

namespace App\Filament\Admin\Resources\Groups\Schemas;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('course_id')
                            ->label(__('filament/admin/group_resource.course_id'))
                            ->relationship('course', 'name', fn($query) => $query->where('status', 'active'))
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        TextInput::make('name')
                            ->label(__('filament/admin/group_resource.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: مجموعة السبت والاثنين صباحاً')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Section::make('الموارد')
                    ->schema([
                        Select::make('classroom_id')
                            ->label(__('filament/admin/group_resource.classroom_id'))
                            ->relationship('classroom', 'name', fn($query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload(),
                            
                        Select::make('instructor_id')
                            ->label(__('filament/admin/group_resource.instructor_id'))
                            ->options(function () {
                                return \App\Models\Instructor::with('user')
                                    ->where('status', 'active')
                                    ->whereHas('user.roles', function ($q) {
                                        $q->whereIn('name', ['instructor', 'branch_manager']);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($instructor) {
                                        $label = $instructor->user?->name ?? 'معلم';
                                        $label .= ' (' . $instructor->employee_code . ')';
                                        return [$instructor->id => $label];
                                    });
                            })
                            ->searchable()
                            ->preload(),
                            
                        Select::make('supervisor_id')
                            ->label(__('filament/admin/group_resource.supervisor_id'))
                            ->options(function () {
                                return \App\Models\Supervisor::with('user')
                                    ->where('status', 'active')
                                    ->get()
                                    ->mapWithKeys(function ($supervisor) {
                                        $label = $supervisor->user?->name ?? 'مشرف';
                                        $label .= ' (' . $supervisor->employee_number . ')';
                                        return [$supervisor->id => $label];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('اختر المشرف المسؤول عن هذه المجموعة'),
                    ])
                    ->columns(3),
                    
                Section::make('السعة والتواريخ')
                    ->schema([
                        TextInput::make('capacity')
                            ->label(__('filament/admin/group_resource.capacity'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                            
                        TextInput::make('max_students')
                            ->label(__('filament/admin/group_resource.max_students'))
                            ->numeric()
                            ->default(20)
                            ->minValue(1)
                            ->required(),
                            
                        DatePicker::make('start_date')
                            ->label(__('filament/admin/group_resource.start_date'))
                            ->required(),
                            
                        DatePicker::make('end_date')
                            ->label(__('filament/admin/group_resource.end_date'))
                            ->after('start_date'),
                            
                        Select::make('status')
                            ->label(__('filament/admin/group_resource.status'))
                            ->options(['pending' => __('filament/admin/group_resource.status.pending'), 'active' => __('filament/admin/group_resource.status.active'), 'completed' => __('filament/admin/group_resource.status.completed'), 'cancelled' => __('filament/admin/group_resource.status.cancelled')])
                            ->default(__('filament/admin/group_resource.status_default'))
                            ->required(),
                    ])
                    ->columns(3),
                    
                Section::make('الجدول الزمني')
                    ->schema([
                        Repeater::make('schedule')
                            ->label(__('filament/admin/group_resource.schedule'))
                            ->schema([
                                Select::make('day')
                                    ->label(__('filament/admin/group_resource.day'))
                                    ->options(['saturday' => __('filament/admin/group_resource.day.saturday'), 'sunday' => __('filament/admin/group_resource.day.sunday'), 'monday' => __('filament/admin/group_resource.day.monday'), 'tuesday' => __('filament/admin/group_resource.day.tuesday'), 'wednesday' => __('filament/admin/group_resource.day.wednesday'), 'thursday' => __('filament/admin/group_resource.day.thursday'), 'friday' => __('filament/admin/group_resource.day.friday')])
                                    ->required(),
                                    
                                TextInput::make('start_time')
                                    ->label(__('filament/admin/group_resource.start_time'))
                                    ->type('time')
                                    ->required(),
                                    
                                TextInput::make('end_time')
                                    ->label(__('filament/admin/group_resource.end_time'))
                                    ->type('time')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('إضافة يوم جديد')
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('ملاحظات')
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('filament/admin/group_resource.notes'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}

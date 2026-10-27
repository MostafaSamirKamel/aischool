<?php

namespace App\Filament\Instructor\Resources\GroupMessageResource\Schemas;

use App\Models\Group;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;

class GroupMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Section::make()
                // ->schema([
                        Select::make('group_id')
                            ->label(__('filament/instructor/group_message_resource.group_id'))
                    ->relationship('group', 'name', function ($query) {
                                $user = auth()->user();
                                if (!$user || !$user->instructor) {
                            return $query->whereRaw('1 = 0'); // إرجاع نتيجة فارغة
                                }

                        return $query->where('instructor_id', $user->instructor->id)
                            ->where('status', 'active');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر المجموعة التي تريد إرسال الرسالة إليها')
                        ,

                        TextInput::make('subject')
                            ->label(__('filament/instructor/group_message_resource.subject'))
                            ->required()
                            ->maxLength(255)
                        ,

                        RichEditor::make('message')
                            ->label(__('filament/instructor/group_message_resource.message'))
                            ->required()
                            
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ]),

                        Select::make('type')
                            ->label(__('filament/instructor/group_message_resource.type'))
                            ->options(['announcement' => __('filament/instructor/group_message_resource.type.announcement'), 'homework' => __('filament/instructor/group_message_resource.type.homework'), 'reminder' => __('filament/instructor/group_message_resource.type.reminder'), 'general' => __('filament/instructor/group_message_resource.type.general')])
                            ->default('general')
                            ->required(),

                        Select::make('priority')
                            ->label(__('filament/instructor/group_message_resource.priority'))
                            ->options(['low' => __('filament/instructor/group_message_resource.priority.low'), 'normal' => __('filament/instructor/group_message_resource.priority.normal'), 'high' => __('filament/instructor/group_message_resource.priority.high'), 'urgent' => __('filament/instructor/group_message_resource.priority.urgent')])
                            ->default('normal')
                            ->required(),

                        FileUpload::make('attachments')
                            ->label(__('filament/instructor/group_message_resource.attachments'))
                            ->multiple()
                            ->directory('group-messages')
                            ->maxFiles(5)
                            ->maxSize(10240)
                        ,

                        Toggle::make('send_notification')
                            ->label(__('filament/instructor/group_message_resource.send_notification'))
                            ->default(true)
                            ->inline(false),

                        Toggle::make('send_email')
                            ->label(__('filament/instructor/group_message_resource.send_email'))
                            ->default(false)
                            ->inline(false),

                        DateTimePicker::make('scheduled_at')
                            ->label(__('filament/instructor/group_message_resource.scheduled_at'))
                            ->helperText('اتركه فارغاً للإرسال الفوري')
                        ,
                // ])

            ]);
    }

}

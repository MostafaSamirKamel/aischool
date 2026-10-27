<?php

namespace App\Filament\Parent\Resources\Messages\Pages;

use App\Filament\Parent\Resources\Messages\MessagesResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ViewField;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessagesResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Mark message as read
        if (!$this->record->is_read) {
            $this->record->markAsRead();
        }
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الرسالة')
                    ->schema([
                        ViewField::make('type_display')
                            ->label('نوع الرسالة')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => match ($record->type) {
                                    'info' => '📋 معلومة',
                                    'warning' => '⚠️ تحذير',
                                    'success' => '✅ نجاح',
                                    'urgent' => '🔔 عاجل',
                                    default => $record->type,
                                }
                            ]),
                        
                        ViewField::make('priority_display')
                            ->label('الأولوية')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => match ($record->priority) {
                                    'low' => 'منخفضة',
                                    'normal' => 'عادية',
                                    'high' => 'عالية',
                                    'urgent' => 'عاجلة',
                                    default => $record->priority,
                                }
                            ]),
                        
                        ViewField::make('student_name')
                            ->label('الطالب')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => $record->student->user->name ?? '-'
                            ]),
                        
                        ViewField::make('sender_name')
                            ->label('المرسل')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => $record->sender->name ?? '-'
                            ]),
                    ])
                    ->columns(4),
                
                Section::make()
                    ->schema([
                        ViewField::make('subject')
                            ->label('عنوان الرسالة')
                            ->view('filament.parent.components.message-subject'),
                        
                        ViewField::make('message')
                            ->label('محتوى الرسالة')
                            ->view('filament.parent.components.message-content'),
                        
                        ViewField::make('attachments')
                            ->label('المرفقات')
                            ->view('filament.parent.components.message-attachments')
                            ->visible(fn ($record) => !empty($record->attachments)),
                    ])
                    ->columnSpanFull(),
                
                Section::make('معلومات إضافية')
                    ->schema([
                        ViewField::make('sent_at')
                            ->label('تاريخ الإرسال')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => $record->sent_at ? $record->sent_at->format('Y-m-d H:i') : '-'
                            ]),
                        
                        ViewField::make('read_at')
                            ->label('تاريخ القراءة')
                            ->view('filament.parent.components.field-display', [
                                'value' => fn ($record) => $record->read_at ? $record->read_at->format('Y-m-d H:i') : 'لم تُقرأ بعد'
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public function getTitle(): string
    {
        return $this->record->subject;
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

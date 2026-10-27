<?php

namespace App\Filament\Parent\Resources\Messages;

use App\Models\StudentMessage;
use App\Models\GroupMessage;
use App\Filament\Parent\Resources\Messages\Tables\MessagesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MessagesResource extends Resource
{
    protected static ?string $model = StudentMessage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'الرسائل والإشعارات';

    protected static ?string $modelLabel = 'رسالة';

    protected static ?string $pluralModelLabel = 'الرسائل';

    protected static UnitEnum|string|null $navigationGroup = 'التواصل';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Parent\Resources\Messages\Pages\ListMessages::route('/'),
            'view' => \App\Filament\Parent\Resources\Messages\Pages\ViewMessage::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Get selected student from session after validation
        $selectedStudentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        if ($selectedStudentId) {
            // Show messages for selected student
            return parent::getEloquentQuery()
                ->where('student_id', $selectedStudentId)
                ->where('status', 'sent')
                ->latest('sent_at');
        }

        // Show all messages for parent's children
        $studentIds = $user->students()->pluck('students.id')->toArray();
        if (empty($studentIds)) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'sent')
            ->latest('sent_at');
    }
}

<?php

namespace App\Filament\Instructor\Resources\StudentMessages;

use App\Filament\Instructor\Resources\StudentMessages\Pages\CreateStudentMessage;
use App\Filament\Instructor\Resources\StudentMessages\Pages\EditStudentMessage;
use App\Filament\Instructor\Resources\StudentMessages\Pages\ListStudentMessages;
use App\Filament\Instructor\Resources\StudentMessages\Schemas\StudentMessageForm;
use App\Filament\Instructor\Resources\StudentMessages\Tables\StudentMessagesTable;
use App\Models\StudentMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class StudentMessageResource extends Resource
{
    protected static ?string $model = StudentMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?string $navigationLabel = 'رسائل الطلاب';

    protected static ?string $modelLabel = 'رسالة طالب';

    protected static ?string $pluralModelLabel = 'رسائل الطلاب';

    protected static UnitEnum|string|null $navigationGroup = 'التواصل';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return StudentMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentMessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentMessages::route('/'),
            'create' => CreateStudentMessage::route('/create'),
            'edit' => EditStudentMessage::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

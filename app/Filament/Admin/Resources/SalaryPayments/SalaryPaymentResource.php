<?php

namespace App\Filament\Admin\Resources\SalaryPayments;

use App\Filament\Admin\Resources\SalaryPayments\Pages\CreateSalaryPayment;
use App\Filament\Admin\Resources\SalaryPayments\Pages\EditSalaryPayment;
use App\Filament\Admin\Resources\SalaryPayments\Pages\ListSalaryPayments;
use App\Filament\Admin\Resources\SalaryPayments\Schemas\SalaryPaymentForm;
use App\Filament\Admin\Resources\SalaryPayments\Tables\SalaryPaymentsTable;
use App\Models\SalaryPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalaryPaymentResource extends Resource
{
    protected static ?string $model = SalaryPayment::class;

    protected static ?string $navigationLabel = 'المرتبات';

    protected static ?string $modelLabel = 'راتب';

    protected static ?string $pluralModelLabel = 'المرتبات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة المالية';
    }

    public static function form(Schema $schema): Schema
    {
        return SalaryPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalaryPaymentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalaryPayments::route('/'),
            'create' => CreateSalaryPayment::route('/create'),
            'edit' => EditSalaryPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return $query;
        }

        return $query->where('tenant_id', $user->tenant_id);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

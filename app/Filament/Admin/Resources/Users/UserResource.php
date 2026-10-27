<?php

namespace App\Filament\Admin\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Schemas\UserForm;
use App\Filament\Admin\Resources\Users\Tables\UsersTable;
use App\Filament\Admin\Resources\Users\Pages\ViewActivityDetail;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'User';

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.administration_and_users');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getModel()::query();

        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Super admin بدون tenant_id يرى جميع المستخدمين
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return parent::getEloquentQuery();
        }

        // جميع المستخدمين الآخرين (بما فيهم super admin مع tenant_id) يرون فقط مستخدمي tenant الخاص بهم
        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('Create:User');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('Update:User');
    }

    public static function canDelete($record): bool
    {
        $currentUser = auth()->user();

        // لا يمكن للمستخدم حذف نفسه
        if ($currentUser->id === $record->id) {
            return false;
        }

        // منع حذف الـ super admin
        if ($record->is_super_admin) {
            return false;
        }

        // منع حذف مالك tenant إذا لم يكن هناك مالك آخر
        if ($record->is_tenant_owner) {
            $otherOwnersCount = User::where('tenant_id', $record->tenant_id)
                ->where('is_tenant_owner', true)
                ->where('id', '!=', $record->id)
                ->count();

            if ($otherOwnersCount === 0) {
                return false;
            }
        }

        return $currentUser->can('Delete:User');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('Delete:User');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
           
           
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/user_resource.navigation_label');
    }
    
    public static function getModelLabel(): string
    {
        return __('filament/admin/user_resource.model_label');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/user_resource.plural_model_label');
    }



}

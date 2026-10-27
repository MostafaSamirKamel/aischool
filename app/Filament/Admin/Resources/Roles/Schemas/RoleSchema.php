<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class RoleSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم الدور')
                                    ->unique(
                                        modifyRuleUsing: function (Unique $rule, $record): Unique {
                                            if ($record) {
                                                $rule = $rule->ignore($record->id);
                                            }
                                            return Utils::isTenancyEnabled()
                                                ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id)
                                                : $rule;
                                        }
                                    )
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('مثال: instructor_role, student_role'),

                                Select::make('panel_type')
                                    ->label('نوع البانل')
                                    ->options([
                                        'admin' => '🔐 Admin Panel (كل الصلاحيات)',
                                        'instructor' => '👨‍🏫 Instructor Panel',
                                        'student' => '🎓 Student Panel',
                                        'parent' => '👨‍👩‍👧 Parent Panel',
                                        'supervisor' => '👁️ Supervisor Panel',
                                    ])
                                    ->nullable()
                                    ->helperText('اختر البانل لتحديد الصلاحيات. اتركه فارغاً للأدوار الإدارية (super_admin, branch_manager, etc)')
                                    ->placeholder('اختياري - اتركه فارغاً للأدوار الإدارية')
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // عند تغيير panel_type، يمكن عمل إجراءات إضافية
                                    }),

                                TextInput::make('guard_name')
                                    ->label('Guard Name')
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255)
                                    ->hidden(),

                                Select::make('tenant_id')
                                    ->label('المؤسسة')
                                    ->options(fn() => \App\Models\Tenant::pluck('name', 'id'))
                                    ->default(fn() => auth()->user()->tenant_id)
                                    ->required(fn() => !auth()->user()->is_super_admin)
                                    ->disabled(fn() => !auth()->user()->is_super_admin)
                                    ->dehydrated()
                                    ->visible(fn(): bool => auth()->user()->is_super_admin || auth()->user()->tenant_id),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    ->default(Filament::getTenant()?->id)
                                    ->options(
                                        fn(): array => in_array(Utils::getTenantModel(), [null, '', '0'], true)
                                        ? []
                                        : Utils::getTenantModel()::pluck('name', 'id')->toArray()
                                    )
                                    ->visible(fn(): bool => Utils::isTenancyEnabled())
                                    ->dehydrated(fn(): bool => Utils::isTenancyEnabled()),

                                // Select All Component من parent class
                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

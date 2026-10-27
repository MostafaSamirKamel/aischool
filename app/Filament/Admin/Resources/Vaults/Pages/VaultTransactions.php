<?php

namespace App\Filament\Admin\Resources\Vaults\Pages;

use App\Filament\Admin\Resources\Vaults\VaultResource;
use App\Models\Vault;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\SalaryPayment;
use App\Models\StudentPayment;
use App\Models\Payment;
use App\Models\VaultTransfer;
use Illuminate\Support\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VaultTransactions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = VaultResource::class;

    protected string $view = 'filament.admin.resources.vaults.pages.vault-transactions';
    
    protected static ?string $navigationLabel = null;
    
    protected static ?string $title = null;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_transactions'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('date')
                    ->label(__('filament/admin/vault_transactions.التاريخ'))
                    ->date('Y-m-d')
                    ->sortable(),
                
                TextColumn::make('type')
                    ->label(__('filament/admin/vault_transactions.النوع'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'revenue' => 'success',
                        'student_payment' => 'info',
                        'salary_payment' => 'warning',
                        'expense' => 'danger',
                        'transfer_in' => 'success',
                        'transfer_out' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'revenue' => '💰 إيراد',
                        'student_payment' => '🎓 تحصيل طالب',
                        'salary_payment' => '💵 صرف راتب',
                        'expense' => '💸 مصروف',
                        'transfer_in' => '🔄 تحويل وارد',
                        'transfer_out' => '🔁 تحويل صادر',
                        default => $state,
                    }),
                
                TextColumn::make('vault_name')
                    ->label(__('filament/admin/vault_transactions.الخزينة'))
                    ->badge()
                    ->searchable(),
                
                TextColumn::make('description')
                    ->label(__('filament/admin/vault_transactions.البيان'))
                    ->searchable()
                    ->limit(50),
                
                TextColumn::make('amount')
                    ->label(__('filament/admin/vault_transactions.المبلغ'))
                    ->money('EGP')
                    ->color(fn ($record): string => 
                        in_array($record->type, ['expense', 'salary_payment', 'transfer_out'], true) ? 'danger' : 'success'
                    ),
                
                TextColumn::make('balance')
                    ->label(__('filament/admin/vault_transactions.الرصيدبعدالعملية'))
                    ->money('EGP')
                    ->visible(fn() => auth()->user()?->is_super_admin || auth()->user()?->can('view_vault_balances'))
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger'),
                
                TextColumn::make('reference')
                    ->label(__('filament/admin/vault_transactions.المرجع'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('vault_id')
                    ->label(__('filament/admin/vault_transactions.الخزينة'))
                    ->options(function () {
                        $user = auth()->user();
                        $query = Vault::query()->where('is_active', true);
                        
                        if (!($user->is_super_admin && is_null($user->tenant_id))) {
                            $query->where('tenant_id', $user->tenant_id);
                        }

                        return $query->get()->mapWithKeys(fn($v) => [$v->id => $v->getDisplayNameWithBalance()]);
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('type')
                    ->label(__('filament/admin/vault_transactions.نوعالمعاملة'))
                    ->options([
                        'revenue' => '💰 إيراد',
                        'student_payment' => '🎓 تحصيل طالب',
                        'salary_payment' => '💵 صرف راتب',
                        'expense' => '💸 مصروف',
                        'transfer_in' => '🔄 تحويل وارد',
                        'transfer_out' => '🔁 تحويل صادر',
                    ]),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([15, 25, 50, 100])
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        // سنستخدم نموذج فارغ ونستبدل البيانات لاحقاً
        return Revenue::query()->whereKey(-1);
    }

    protected function paginateTableQuery(Builder $query): \Illuminate\Contracts\Pagination\Paginator
    {
        $perPage = $this->getTableRecordsPerPage();
        $records = $this->getAllTransactions();

        // تطبيق الفلاتر
        $tableFilters = $this->tableFilters ?? [];

        // فلتر الخزينة
        if (!empty($tableFilters['vault_id']['value'])) {
            $records = $records->where('vault_id', $tableFilters['vault_id']['value']);
        }

        // فلتر نوع المعاملة
        if (!empty($tableFilters['type']['value'])) {
            $records = $records->where('type', $tableFilters['type']['value']);
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $records->forPage($this->getTablePage(), $perPage),
            $records->count(),
            $perPage,
            $this->getTablePage(),
            ['path' => request()->url()]
        );
    }

    protected function getAllTransactions(): Collection
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $transactions = collect();

        // الإيرادات المستقلة (التي لا ترتبط بدفعة payment_id)
        $revenues = Revenue::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('vault_id')
            ->whereNull('payment_id')
            ->where('status', '!=', 'cancelled')
            ->with('vault')
            ->get()
            ->map(fn($r) => (object) [
                'id' => 'R-' . $r->id,
                'date' => $r->revenue_date ? Carbon::parse($r->revenue_date)->toDateString() : $r->created_at->toDateString(),
                'type' => 'revenue',
                'vault_id' => $r->vault_id,
                'vault_name' => $r->vault?->name ?? 'غير محدد',
                'description' => $r->title ?? $r->description,
                'amount' => (float) $r->amount,
                'reference' => $r->revenue_code ?? $r->invoice_number,
                'balance' => 0,
            ]);

        // المدفوعات (Payment)
        $payments = Payment::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('vault_id')
            ->where('status', 'completed')
            ->with(['vault', 'enrollment.student.user'])
            ->get()
            ->map(fn($p) => (object) [
                'id' => 'PAY-' . $p->id,
                'date' => $p->payment_date ? Carbon::parse($p->payment_date)->toDateString() : $p->created_at->toDateString(),
                'type' => 'student_payment',
                'vault_id' => $p->vault_id,
                'vault_name' => $p->vault?->name ?? 'غير محدد',
                'description' => 'تحصيل: ' . ($p->enrollment?->student?->user?->name ?? 'طالب'),
                'amount' => (float) $p->amount,
                'reference' => $p->payment_code ?? ('PAY-' . $p->id),
                'balance' => 0,
            ]);

        // مدفوعات الطلاب التاريخية (StudentPayment) إذا وجدت
        $studentPayments = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('student_payments')) {
            $studentPayments = StudentPayment::query()
                ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNotNull('vault_id')
                ->where('status', 'completed')
                ->with(['vault', 'student.user'])
                ->get()
                ->map(fn($p) => (object) [
                    'id' => 'SP-' . $p->id,
                    'date' => $p->payment_date ? Carbon::parse($p->payment_date)->toDateString() : $p->created_at->toDateString(),
                    'type' => 'student_payment',
                    'vault_id' => $p->vault_id,
                    'vault_name' => $p->vault?->name ?? 'غير محدد',
                    'description' => 'دفعة طالب: ' . ($p->student?->user?->name ?? 'غير محدد'),
                    'amount' => (float) $p->net_amount,
                    'reference' => $p->payment_number,
                    'balance' => 0,
                ]);
        }

        // المصروفات
        $expenses = Expense::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('vault_id')
            ->where('status', '!=', 'cancelled')
            ->with('vault')
            ->get()
            ->map(fn($e) => (object) [
                'id' => 'E-' . $e->id,
                'date' => $e->expense_date ? Carbon::parse($e->expense_date)->toDateString() : $e->created_at->toDateString(),
                'type' => 'expense',
                'vault_id' => $e->vault_id,
                'vault_name' => $e->vault?->name ?? 'غير محدد',
                'description' => $e->title ?? $e->description,
                'amount' => (float) $e->amount,
                'reference' => $e->expense_code ?? $e->invoice_number,
                'balance' => 0,
            ]);

        // صرف المرتبات
        $salaryPayments = SalaryPayment::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'paid')
            ->whereNotNull('vault_id')
            ->with(['vault', 'instructor.user', 'admin.user'])
            ->get()
            ->map(fn($s) => (object) [
                'id' => 'SAL-' . $s->id,
                'date' => $s->payment_date ? Carbon::parse($s->payment_date)->toDateString() : $s->created_at->toDateString(),
                'type' => 'salary_payment',
                'vault_id' => $s->vault_id,
                'vault_name' => $s->vault?->name ?? 'غير محدد',
                'description' => 'صرف راتب: ' . ($s->employee_name ?? 'غير محدد'),
                'amount' => (float) $s->amount,
                'reference' => 'SAL-' . $s->id,
                'balance' => 0,
            ]);

        // التحويلات الواردة
        $transfersIn = VaultTransfer::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->with(['toVault', 'fromVault'])
            ->get()
            ->map(fn($t) => (object) [
                'id' => 'TR-IN-' . $t->id,
                'date' => $t->transfer_date ? Carbon::parse($t->transfer_date)->toDateString() : $t->created_at->toDateString(),
                'type' => 'transfer_in',
                'vault_id' => $t->to_vault_id,
                'vault_name' => $t->toVault?->name ?? 'غير محدد',
                'description' => 'تحويل وارد من ' . ($t->fromVault?->name ?? 'خزنة أخرى'),
                'amount' => (float) $t->amount,
                'reference' => $t->transfer_code ?? ('TR-' . $t->id),
                'balance' => 0,
            ]);

        // التحويلات الصادرة
        $transfersOut = VaultTransfer::query()
            ->when(!($user->is_super_admin && is_null($tenantId)), fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->with(['toVault', 'fromVault'])
            ->get()
            ->map(fn($t) => (object) [
                'id' => 'TR-OUT-' . $t->id,
                'date' => $t->transfer_date ? Carbon::parse($t->transfer_date)->toDateString() : $t->created_at->toDateString(),
                'type' => 'transfer_out',
                'vault_id' => $t->from_vault_id,
                'vault_name' => $t->fromVault?->name ?? 'غير محدد',
                'description' => 'تحويل صادر إلى ' . ($t->toVault?->name ?? 'خزنة أخرى'),
                'amount' => (float) $t->amount,
                'reference' => $t->transfer_code ?? ('TR-' . $t->id),
                'balance' => 0,
            ]);

        // دمج كل المعاملات
        $allTransactions = $transactions
            ->concat($revenues)
            ->concat($payments)
            ->concat($studentPayments)
            ->concat($salaryPayments)
            ->concat($expenses)
            ->concat($transfersIn)
            ->concat($transfersOut)
            ->sortByDesc('date');

        // حساب الرصيد التراكمي
        $runningBalance = [];
        foreach ($allTransactions->groupBy('vault_id') as $vaultId => $vaultTransactions) {
            $vault = Vault::find($vaultId);
            $balance = (float) ($vault?->opening_balance ?? 0);
            
            foreach ($vaultTransactions->sortBy('date') as $transaction) {
                if (in_array($transaction->type, ['expense', 'salary_payment', 'transfer_out'], true)) {
                    $balance -= $transaction->amount;
                } else {
                    $balance += $transaction->amount;
                }
                $runningBalance[$transaction->id] = $balance;
            }
        }

        // إضافة الرصيد إلى كل معاملة
        return $allTransactions->map(function ($transaction) use ($runningBalance) {
            $transaction->balance = $runningBalance[$transaction->id] ?? 0;
            return $transaction;
        });
    }
    public function getTitle(): string
    {
        return __('filament/admin/vault_transactions.title');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/vault_transactions.navigation_label');
    }


}

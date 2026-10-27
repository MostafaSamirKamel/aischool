# 03 — PAYMENT METHODS CONSOLIDATION & REMOVAL REPORT

## 1. Policy Decision
Independent payment-method dropdowns (Cash, Bank Transfer, Card, Mobile Wallet, Other) gave users an alternative path that bypassed vault selection. 

**THE VAULT IS NOW THE ONLY ALLOWED RECEIVING OR DISBURSING ACCOUNT SELECTOR.**

## 2. Actions Taken
1. **Removed from Frontend UI**:
   - `app/Filament/Admin/Resources/Payments/Schemas/PaymentForm.php`: Removed `Select::make('payment_method')`. Only `Select::make('vault_id')->label('الخزنة المستلمة')->required()` remains.
2. **Backend Compatibility Layer**:
   - Legacy database column `payments.payment_method` is an enum.
   - `CreatePayment::mutateFormDataBeforeCreate` and `EditPayment::mutateFormDataBeforeSave` automatically derive `payment_method` from `$vault->type`:
     - `$vault->type === 'cash'` $\rightarrow$ `'cash'`
     - `$vault->type === 'vodafone'|'instapay'|'wallet'` $\rightarrow$ `'mobile_wallet'`
     - `$vault->type === 'bank'` $\rightarrow$ `'bank_transfer'`
3. **Historical Data Safety**:
   - No historical payments were modified or updated during this migration.
   - Historical payment records retain their original methods for auditing.
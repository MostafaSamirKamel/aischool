<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

echo "=== REMEDIATION: Assign Vault to Legacy Vault-less Payments ===\n\n";

// These 29 payments were seeded BEFORE vault enforcement was added.
// Business rule: we must assign them to the correct branch vault.
// For each enrollment, find the enrollment's tenant_id, then assign the
// primary cash vault for that branch.

$paymentsToFix = Payment::where('status', 'completed')
    ->whereNull('vault_id')
    ->with(['enrollment'])
    ->orderBy('id')
    ->get();

echo "Payments to remediate: " . $paymentsToFix->count() . "\n\n";

// Get available vaults
$vaults = Vault::where('is_active', true)->get();
echo "Available active vaults:\n";
foreach ($vaults as $v) {
    echo "  [{$v->id}] {$v->name} (tenant: {$v->tenant_id})\n";
}
echo "\n";

// Map tenant_id -> primary cash vault
$tenantVaultMap = [];
foreach ($vaults as $v) {
    if (!isset($tenantVaultMap[$v->tenant_id])) {
        // Prefer vault named 'كاش' or 'الخزينة النقدية' first
        if (str_contains($v->name, 'كاش') || str_contains($v->name, 'الخزينة')) {
            $tenantVaultMap[$v->tenant_id] = $v->id;
        }
    }
}

// If still no entry, use first vault for tenant
foreach ($vaults as $v) {
    if (!isset($tenantVaultMap[$v->tenant_id])) {
        $tenantVaultMap[$v->tenant_id] = $v->id;
    }
}

echo "Tenant → Vault mapping:\n";
foreach ($tenantVaultMap as $tid => $vid) {
    echo "  Tenant {$tid} → Vault {$vid}\n";
}
echo "\n";

// Now perform the remediation
$fixed = 0;
$skipped = 0;

DB::beginTransaction();
try {
    foreach ($paymentsToFix as $payment) {
        $enrollment = $payment->enrollment;
        if (!$enrollment) {
            echo "⚠️  Payment {$payment->id} has no enrollment! Skipping.\n";
            $skipped++;
            continue;
        }
        
        $tenantId = $enrollment->tenant_id;
        $assignedVaultId = $tenantVaultMap[$tenantId] ?? null;
        
        if (!$assignedVaultId) {
            // Fallback: use vault ID 1 (main cash)
            $assignedVaultId = 1;
            echo "⚠️  No vault for tenant {$tenantId}. Using vault 1 as fallback.\n";
        }
        
        // Update bypassing the model's saving hook since this is a data remediation
        // The hook would re-validate which is fine — vault IS now being assigned
        DB::table('payments')
            ->where('id', $payment->id)
            ->update(['vault_id' => $assignedVaultId, 'updated_at' => now()]);
        
        echo "  ✅ Payment {$payment->id} (Amount: {$payment->amount}) → Vault {$assignedVaultId}\n";
        $fixed++;
    }
    
    DB::commit();
    echo "\n✅ Remediation complete: Fixed={$fixed}, Skipped={$skipped}\n";
    
    // Verify
    $remaining = Payment::where('status', 'completed')->whereNull('vault_id')->count();
    echo "Remaining vault-less completed payments: {$remaining}\n";
    echo ($remaining === 0 ? "✅ ALL CLEAR" : "❌ STILL HAS ISSUES") . "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

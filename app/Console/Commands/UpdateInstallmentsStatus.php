<?php

namespace App\Console\Commands;

use App\Models\Installment;
use Illuminate\Console\Command;

class UpdateInstallmentsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of installments based on due dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating installments status...');

        // Update all installments
        $installments = Installment::whereIn('status', ['pending', 'partial'])
            ->get();

        $updated = 0;
        foreach ($installments as $installment) {
            $oldStatus = $installment->status;
            $installment->updateStatus();
            
            if ($installment->status !== $oldStatus) {
                $updated++;
            }
        }

        $this->info("Updated {$updated} installment(s) status.");
        
        // Show overdue statistics
        $overdueCount = Installment::overdue()->count();
        $overdueTotal = Installment::overdue()->sum('amount');
        
        $this->warn("Total overdue installments: {$overdueCount}");
        $this->warn("Total overdue amount: " . number_format($overdueTotal, 2) . " EGP");

        return Command::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Check if 'cancelled' is already in the check constraint
            $tableSql = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='installments'");
            if (!empty($tableSql) && str_contains($tableSql[0]->sql, "'cancelled'")) {
                return; // Already updated
            }

            DB::statement('PRAGMA foreign_keys=OFF;');
            DB::beginTransaction();

            DB::statement('
                CREATE TABLE "installments_temp" (
                    "id" integer primary key autoincrement not null,
                    "tenant_id" integer not null,
                    "enrollment_id" integer not null,
                    "installment_number" integer not null,
                    "amount" numeric not null,
                    "due_date" date not null,
                    "paid_amount" numeric not null default \'0\',
                    "paid_date" date,
                    "status" varchar check ("status" in (\'pending\', \'paid\', \'overdue\', \'partial\', \'cancelled\')) not null default \'pending\',
                    "notes" text,
                    "created_at" datetime,
                    "updated_at" datetime,
                    "deleted_at" datetime,
                    foreign key("tenant_id") references "tenants"("id") on delete cascade,
                    foreign key("enrollment_id") references "enrollments"("id") on delete cascade
                )
            ');

            DB::statement('INSERT INTO "installments_temp" SELECT * FROM "installments";');
            DB::statement('DROP TABLE "installments";');
            DB::statement('ALTER TABLE "installments_temp" RENAME TO "installments";');

            DB::statement('CREATE INDEX IF NOT EXISTS "installments_enrollment_id_installment_number_index" ON "installments" ("enrollment_id", "installment_number");');
            DB::statement('CREATE INDEX IF NOT EXISTS "installments_status_due_date_index" ON "installments" ("status", "due_date");');

            DB::commit();
            DB::statement('PRAGMA foreign_keys=ON;');
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE `installments` MODIFY COLUMN `status` ENUM('pending', 'paid', 'overdue', 'partial', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `installments` MODIFY COLUMN `status` ENUM('pending', 'paid', 'overdue', 'partial') NOT NULL DEFAULT 'pending'");
        }
    }
};

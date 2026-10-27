<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BUG FIX: Add 'waived' to installments status CHECK constraint.
 *
 * The Installment model and EnrollmentCancellationService both use 'waived'
 * as a valid status when settling enrollments. However, the SQLite CHECK
 * constraint on the installments table did not include 'waived', causing
 * settlement operations to fail with a constraint violation.
 *
 * This migration rebuilds the table (SQLite requires full rebuild to modify
 * CHECK constraints) to include 'waived' in the allowed statuses.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Check if 'waived' is already present
            $tableSql = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='installments'");
            if (!empty($tableSql) && str_contains($tableSql[0]->sql, "'waived'")) {
                return; // Already updated
            }

            DB::statement('PRAGMA foreign_keys=OFF;');
            DB::beginTransaction();

            // Recreate table with 'waived' added to CHECK constraint
            DB::statement("
                CREATE TABLE \"installments_new\" (
                    \"id\" integer primary key autoincrement not null,
                    \"tenant_id\" integer not null,
                    \"enrollment_id\" integer not null,
                    \"installment_number\" integer not null,
                    \"amount\" numeric not null,
                    \"due_date\" date not null,
                    \"paid_amount\" numeric not null default '0',
                    \"paid_date\" date,
                    \"status\" varchar check (\"status\" in ('pending', 'paid', 'overdue', 'partial', 'cancelled', 'waived')) not null default 'pending',
                    \"notes\" text,
                    \"created_at\" datetime,
                    \"updated_at\" datetime,
                    \"deleted_at\" datetime,
                    foreign key(\"tenant_id\") references \"tenants\"(\"id\") on delete cascade,
                    foreign key(\"enrollment_id\") references \"enrollments\"(\"id\") on delete cascade
                )
            ");

            DB::statement('INSERT INTO "installments_new" SELECT * FROM "installments";');
            DB::statement('DROP TABLE "installments";');
            DB::statement('ALTER TABLE "installments_new" RENAME TO "installments";');

            // Recreate indexes
            DB::statement('CREATE INDEX IF NOT EXISTS "installments_enrollment_id_installment_number_index" ON "installments" ("enrollment_id", "installment_number");');
            DB::statement('CREATE INDEX IF NOT EXISTS "installments_status_due_date_index" ON "installments" ("status", "due_date");');

            DB::commit();
            DB::statement('PRAGMA foreign_keys=ON;');

        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE `installments` MODIFY COLUMN `status` ENUM('pending', 'paid', 'overdue', 'partial', 'cancelled', 'waived') NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, drop and recreate the constraint
            DB::statement("ALTER TABLE installments DROP CONSTRAINT IF EXISTS installments_status_check");
            DB::statement("ALTER TABLE installments ADD CONSTRAINT installments_status_check CHECK (status IN ('pending', 'paid', 'overdue', 'partial', 'cancelled', 'waived'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `installments` MODIFY COLUMN `status` ENUM('pending', 'paid', 'overdue', 'partial', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
        // SQLite down migration not implemented (backward incompatible - waived records would need to be remapped)
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing string file_path values to JSON arrays
        DB::table('learning_resources')
            ->whereNotNull('file_path')
            ->where('file_path', 'not like', '[%')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('learning_resources')
                        ->where('id', $row->id)
                        ->update(['file_path' => json_encode([$row->file_path])]);
                }
            });

        Schema::table('learning_resources', function (Blueprint $table) {
            $table->json('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('learning_resources', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }
};

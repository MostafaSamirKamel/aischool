<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add to instructors table
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('national_id', 14)->nullable()->after('employee_code');
            $table->date('birth_date')->nullable()->after('national_id');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
            $table->string('governorate')->nullable()->after('phone');
            
            $table->index('national_id');
        });

        // Add to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->string('national_id', 14)->nullable()->after('employee_code');
            $table->date('birth_date')->nullable()->after('national_id');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
            $table->string('governorate')->nullable()->after('phone');
            
            $table->index('national_id');
        });

        // Add to student_parents table
        Schema::table('student_parents', function (Blueprint $table) {
            $table->string('national_id', 14)->nullable()->after('user_id');
            $table->date('birth_date')->nullable()->after('national_id');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
            $table->string('governorate')->nullable()->after('national_id');
            
            $table->index('national_id');
        });
    }

    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropIndex(['national_id']);
            $table->dropColumn(['national_id', 'birth_date', 'gender', 'governorate']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex(['national_id']);
            $table->dropColumn(['national_id', 'birth_date', 'gender', 'governorate']);
        });

        Schema::table('student_parents', function (Blueprint $table) {
            $table->dropIndex(['national_id']);
            $table->dropColumn(['national_id', 'birth_date', 'gender', 'governorate']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            
            // General Settings
            $table->string('default_driver')->default('log');
            $table->boolean('queue')->default(true);
            $table->boolean('log_messages')->default(true);
            $table->integer('rate_limit_per_minute')->default(10);
            $table->integer('rate_limit_per_hour')->default(100);
            
            // Twilio
            $table->string('twilio_sid')->nullable();
            $table->string('twilio_token')->nullable();
            $table->string('twilio_from')->nullable();
            
            // Vonage
            $table->string('vonage_key')->nullable();
            $table->string('vonage_secret')->nullable();
            $table->string('vonage_from')->nullable();
            
            // Vodafone
            $table->string('vodafone_username')->nullable();
            $table->string('vodafone_password')->nullable();
            $table->string('vodafone_sender')->nullable();
            
            $table->timestamps();
            
            // Each tenant should have only one config
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_configs');
    }
};

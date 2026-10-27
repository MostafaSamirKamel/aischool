<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // معلومات الشركة
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_whatsapp')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_website')->nullable();
            
            // الموقع الجغرافي
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            
            // صفحات التواصل الاجتماعي
            $table->string('social_facebook')->nullable();
            $table->string('social_whatsapp')->nullable();
            $table->string('social_telegram')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_youtube')->nullable();
            
            // إعدادات SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_search_console')->nullable();
            
            // إعدادات البريد الإلكتروني
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->string('mail_driver')->default('smtp')->nullable();
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->default(587)->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            
            // إعدادات إضافية
            $table->text('about_company')->nullable();
            $table->string('currency')->default('EGP')->nullable();
            $table->string('timezone')->default('UTC')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

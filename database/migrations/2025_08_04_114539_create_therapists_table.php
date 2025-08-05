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
        Schema::create('therapists', function (Blueprint $table) {
            $table->id();
            $table->longText('avatar')->nullable();
            $table->longText('avatar_list')->nullable();
            $table->longText('name_prefix')->nullable();
            $table->longText('name')->nullable();
            $table->longText('title')->nullable();
            $table->longText('services_offered')->nullable();
            $table->longText('online_offered')->nullable();
            $table->longText('country')->nullable();
            $table->longText('office_name')->nullable();
            $table->longText('suit')->nullable();
            $table->longText('street_address')->nullable();
            $table->longText('city')->nullable();
            $table->longText('zip_code')->nullable();
            $table->longText('state')->nullable();
            $table->longText('state_code')->nullable();
            $table->longText('gender')->nullable();
            $table->longText('email')->nullable();
            $table->longText('phone_number')->nullable();
            $table->longText('link_to_website')->nullable();
            $table->longText('identifies_as_tag')->nullable();
            $table->longText('specialty')->nullable();
            $table->longText('general_expertise')->nullable();
            $table->longText('type_of_therapy')->nullable();
            $table->longText('clinnical_approaches')->nullable();
            $table->longText('about_1')->nullable();
            $table->longText('about_2')->nullable();
            $table->longText('insurance')->nullable();
            $table->longText('payment_method')->nullable();
            $table->longText('fee')->nullable();
            $table->longText('license')->nullable();
            $table->longText('certification')->nullable();
            $table->longText('education')->nullable();
            $table->longText('experience')->nullable();
            $table->longText('experience_duration')->nullable();
            $table->longText('serves_ages')->nullable();
            $table->longText('community')->nullable();
            $table->longText('languages')->nullable();
            $table->longText('faq')->nullable();
            $table->longText('source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapists');
    }
};

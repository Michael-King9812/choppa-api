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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->boolean('is_email_verified')->default(false);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('invite_code')->nullable();
            $table->string('referral')->nullable();
            $table->string('profile_pic')->nullable();
            $table->boolean('is_customer')->default(true);    
            $table->unsignedBigInteger('activity_status')->default(3);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('activity_status')->references('id')->on('user_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

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
        Schema::create('member_center_guests', function (Blueprint $table) {

            $table->id()->comment('訪客編號');
            $table->string('email')->comment('電子郵件');

            $table->string('register_token')->nullable()->unique()->comment('驗證令牌'); 
            $table->timestamp('token_expires_at')->nullable()->comment('令牌效期'); 
            $table->enum('status', ['pending', 'completed', 'expired', 'cancel'])->default('pending')->comment('流程狀態');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_center_guests');
    }
};

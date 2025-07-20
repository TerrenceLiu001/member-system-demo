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
        Schema::create('member_center_user_contact_update', function (Blueprint $table) {

            $table->id()->comment('編號');
            $table->integer('user_id')->comment('會員編號');
            $table->string('email')->comment('電子郵件（當前）');
            $table->string('mobile')->nullable()->comment('手機號碼（當前）');
            $table->enum('contact_type', ['email', 'mobile'])->default('email')->comment('變更類型');
            $table->string('new_contact')->comment('電子郵件（待更新）');

            $table->string('update_contact_token')->nullable()->unique()->comment('驗證令牌');
            $table->timestamp('token_expires_at')->nullable()->comment('令牌效期'); 
            $table->enum('status', ['pending', 'completed', 'expired', 'cancel'])->default('pending')->comment('輪詢狀態');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_center_user_contact_update');
    }
};

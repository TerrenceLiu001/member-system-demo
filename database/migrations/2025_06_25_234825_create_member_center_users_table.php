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
        Schema::create('member_center_users', function (Blueprint $table) {

            $table->id()->comment('用戶編碼');
            $table->integer('guest_id')->unique()->comment('訪客編號');
            $table->string('username')->nullable()->comment('用戶暱稱');

            $table->string('email')->unique()->comment('電子郵件');
            $table->string('mobile', 20)->nullable()->comment('手機號碼');
            $table->string('country', 3)->nullable()->comment('國家（手機）');

            $table->enum('gender', ['male', 'female', 'unknown'])->nullable()->comment('性別');
            $table->enum('age_group', ['under_20', 'between_21_30', 'between_31_40', 'between_41_50', 'between_51_60', 'below_61'])->nullable()->comment('年齡區間');
            $table->string('address')->nullable()->comment('地址');
            $table->string('password', 255)->comment('密碼'); 
            

            $table->string('bearer_token')->nullable()->unique()->comment('身份令牌');
            $table->timestamp('token_expires_at')->nullable()->comment('令牌效期'); 

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_center_users');
    }
};

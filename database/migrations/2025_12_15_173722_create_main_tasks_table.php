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
        Schema::create('main_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();     //ユーザーID（外部キー）
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();     //目標標ID（外部キー）
            $table->string('title');                                            // メインタスク内容
            $table->text('memo')->nullable();                                   // メインタスクのメモ
            $table->timestamp('due_at')->nullable();                            // 期限日時
            $table->timestamp('completed_at')->nullable();                      // 完了日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_tasks');
    }
};

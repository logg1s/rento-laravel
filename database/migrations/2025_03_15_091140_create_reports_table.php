<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('reported_user_id');
            $table->string('entity_type'); // 'message', 'user', 'service', etc.
            $table->string('entity_id');   // ID của entity được báo cáo
            $table->text('reason');        // Lý do báo cáo
            $table->enum('status', ['pending', 'reviewed', 'rejected', 'resolved'])->default('pending');
            $table->text('admin_notes')->nullable(); // Ghi chú của admin khi xem xét báo cáo
            $table->timestamps();

            // Index để tìm kiếm nhanh
            $table->index(['entity_type', 'entity_id']);
            $table->index('status');

            // Thêm foreign key constraints sau khi tạo bảng
            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

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
        Schema::create('channel_notification_user', function (Blueprint $table) {
            $table->string('channel_notification_id');
            $table->foreign("channel_notification_id")->references('id')->on('channel_notifications')->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->primary(["channel_notification_id", "user_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_notification_user');
    }
};

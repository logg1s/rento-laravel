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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id");
            $table->foreignId("service_id");
            $table->foreignId("price_id");
            $table->bigInteger("price_final_value");
            $table->unsignedTinyInteger("state");
            $table->foreignId("location_id")->constrained();
            $table->timestamp("time_start")->nullable(true);
            $table->text("message")->nullable(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

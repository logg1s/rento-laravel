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
        Schema::create('comment_image', function (Blueprint $table) {
            $table->foreignId("comment_id")->constrained()->onDelete("cascade");
            $table->foreignId("image_id")->constrained()->onDelete("cascade");
            $table->primary(["comment_id", "image_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_image');
    }
};

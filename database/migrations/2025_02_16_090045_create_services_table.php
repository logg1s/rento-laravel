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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string("service_name");
            $table->string("service_description");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("location_id");

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");

            $table->foreign("category_id")->references("id")->on("categories");


            $table->foreign("location_id")->references("id")->on("locations");

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

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
            $table->integer("user_id");
            $table->integer("category_id");
            $table->integer("gallery_id");
            $table->integer("price_id");
            $table->integer("location_id");
            $table->integer("comment_id");


            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("category_id")->references("id")->on("categories");
            $table->foreign("gallery_id")->references("id")->on("galleries");
            $table->foreign("price_id")->references("id")->on("prices");
            $table->foreign("location_id")->references("id")->on("locations");
            $table->foreign("comment_id")->references("id")->on("comments");
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

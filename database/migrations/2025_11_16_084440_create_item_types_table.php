<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_types', function (Blueprint $table) {
            $table->unsignedBigInteger('classid')->unique();
            $table->string('name');
            $table->string('market_name');
            $table->string('name_color')->nullable();
            $table->string('icon_url')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};

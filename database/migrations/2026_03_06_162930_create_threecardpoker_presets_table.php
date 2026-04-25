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
        Schema::create('threecardpoker_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('min_bet');
            $table->string('max_bet');
            $table->integer('burn_card')->nullable();
            $table->decimal('side_min', 10, 2)->nullable();
            $table->decimal('side_max', 10, 2)->nullable();
            // $table->decimal('six_card_bonus', 10, 2)->nullable();
            $table->foreignId('chip_preset_id')->constrained('chips')->cascadeOnDelete();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threecardpoker_presets');
    }
};

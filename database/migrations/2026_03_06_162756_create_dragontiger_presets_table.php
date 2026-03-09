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
        Schema::create('dragontiger_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_bet', 10, 2);
            $table->decimal('max_bet', 10, 2);
            $table->decimal('tie_min', 10, 2)->nullable();
            $table->decimal('tie_max', 10, 2)->nullable();
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
        Schema::dropIfExists('dragontiger_presets');
    }
};

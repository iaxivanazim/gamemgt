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
        Schema::create('blackjack_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_bet', 10, 2);
            $table->decimal('max_bet', 10, 2);
            $table->integer('burn_card')->nullable();
            $table->decimal('pair_min', 10, 2)->nullable();
            $table->decimal('pair_max', 10, 2)->nullable();
            $table->enum('surrender', ['0', '1', '2'])->nullable();
            $table->boolean('insurance')->nullable();
            // $table->string('split_type')->nullable();
            // $table->string('rule_type')->nullable();
            // $table->boolean('enable_777_charlie')->default(0);
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
        Schema::dropIfExists('blackjack_presets');
    }
};

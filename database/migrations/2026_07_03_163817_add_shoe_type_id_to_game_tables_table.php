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
        Schema::table('game_tables', function (Blueprint $table) {
            $table->foreignId('shoe_type_id')->nullable()->after('felt_color')->constrained('shoe_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_tables', function (Blueprint $table) {
            $table->dropForeign(['shoe_type_id']);
            $table->dropColumn('shoe_type_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->unsignedTinyInteger('round_quantity')->default(1)->after('numbers_per_card');
        });
    }

    public function down(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->dropColumn('round_quantity');
        });
    }
};

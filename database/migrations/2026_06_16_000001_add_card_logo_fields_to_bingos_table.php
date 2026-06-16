<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->string('card_title')->default('BINGO')->after('cards_per_page');
            $table->string('card_logo_path')->nullable()->after('card_title');
        });
    }

    public function down(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->dropColumn(['card_title', 'card_logo_path']);
        });
    }
};

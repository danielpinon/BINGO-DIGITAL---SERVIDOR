<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->boolean('only_linked_cards')->default(false)->after('card_logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->dropColumn('only_linked_cards');
        });
    }
};

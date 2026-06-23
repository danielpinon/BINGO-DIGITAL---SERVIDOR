<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->string('card_generation_status')->nullable()->after('card_quantity');
            $table->string('card_generation_message')->nullable()->after('card_generation_status');
            $table->timestamp('card_generation_started_at')->nullable()->after('card_generation_message');
            $table->timestamp('card_generation_completed_at')->nullable()->after('card_generation_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->dropColumn([
                'card_generation_status',
                'card_generation_message',
                'card_generation_started_at',
                'card_generation_completed_at',
            ]);
        });
    }
};

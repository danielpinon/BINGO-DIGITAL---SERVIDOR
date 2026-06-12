<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->string('cards_pdf_path')->nullable()->after('cards_per_page');
            $table->string('cards_pdf_status')->default('pending')->after('cards_pdf_path');
            $table->timestamp('cards_pdf_generated_at')->nullable()->after('cards_pdf_status');
        });
    }

    public function down(): void
    {
        Schema::table('bingos', function (Blueprint $table) {
            $table->dropColumn([
                'cards_pdf_path',
                'cards_pdf_status',
                'cards_pdf_generated_at',
            ]);
        });
    }
};

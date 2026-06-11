<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('drawn_numbers', 'bingo_round_id')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->unsignedBigInteger('bingo_round_id')->nullable()->after('bingo_id');
            });
        }

        if (!$this->indexExists('drawn_numbers', 'drawn_numbers_bingo_id_index')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->index('bingo_id');
            });
        }

        if ($this->indexExists('drawn_numbers', 'drawn_numbers_bingo_id_number_unique')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->dropUnique(['bingo_id', 'number']);
            });
        }

        if (!Schema::hasColumn('winners', 'bingo_round_id')) {
            Schema::table('winners', function (Blueprint $table) {
                $table->unsignedBigInteger('bingo_round_id')->nullable()->after('bingo_id');
            });
        }

        DB::table('bingo_rounds')->orderBy('id')->each(function ($round) {
            DB::table('drawn_numbers')
                ->where('bingo_id', $round->bingo_id)
                ->whereNull('bingo_round_id')
                ->update(['bingo_round_id' => $round->id]);

            DB::table('winners')
                ->where('bingo_id', $round->bingo_id)
                ->whereNull('bingo_round_id')
                ->update(['bingo_round_id' => $round->id]);
        });

        if (!$this->foreignExists('drawn_numbers', 'drawn_numbers_bingo_round_id_foreign')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->foreign('bingo_round_id')->references('id')->on('bingo_rounds')->onDelete('cascade');
            });
        }

        if (!$this->indexExists('drawn_numbers', 'drawn_numbers_bingo_round_id_number_unique')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->unique(['bingo_round_id', 'number']);
            });
        }

        if (!$this->foreignExists('winners', 'winners_bingo_round_id_foreign')) {
            Schema::table('winners', function (Blueprint $table) {
                $table->foreign('bingo_round_id')->references('id')->on('bingo_rounds')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('winners', 'bingo_round_id')) {
            Schema::table('winners', function (Blueprint $table) {
                if ($this->foreignExists('winners', 'winners_bingo_round_id_foreign')) {
                    $table->dropForeign(['bingo_round_id']);
                }
                $table->dropColumn('bingo_round_id');
            });
        }

        if (Schema::hasColumn('drawn_numbers', 'bingo_round_id')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                if ($this->indexExists('drawn_numbers', 'drawn_numbers_bingo_round_id_number_unique')) {
                    $table->dropUnique(['bingo_round_id', 'number']);
                }
                if ($this->foreignExists('drawn_numbers', 'drawn_numbers_bingo_round_id_foreign')) {
                    $table->dropForeign(['bingo_round_id']);
                }
                $table->dropColumn('bingo_round_id');
            });
        }

        if (!$this->indexExists('drawn_numbers', 'drawn_numbers_bingo_id_number_unique')) {
            Schema::table('drawn_numbers', function (Blueprint $table) {
                $table->unique(['bingo_id', 'number']);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return !empty(DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]));
    }

    private function foreignExists(string $table, string $foreign): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreign)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

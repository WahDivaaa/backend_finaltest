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
        Schema::table('books', function (Blueprint $table) {
            // Ini adalah 'foreign key' yang paling penting
            $table->index('author_id');
        });

        Schema::table('ratings', function (Blueprint $table) {
            // Ini 'foreign key' penting kedua
            $table->index('book_id');
            // Ini untuk filter 'trending'
            $table->index('created_at');
            // Ini untuk filter 'popularity' (> 5)
            $table->index('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['book_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['rate']);
        });
    }
};

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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('bride_parents')->nullable()->after('bride_name');
            $table->string('bride_image')->nullable()->after('bride_parents');
            $table->string('groom_parents')->nullable()->after('groom_name');
            $table->string('groom_image')->nullable()->after('groom_parents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['bride_parents', 'bride_image', 'groom_parents', 'groom_image']);
        });
    }
};

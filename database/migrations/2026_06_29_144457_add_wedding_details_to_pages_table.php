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
            $table->date('wedding_date')->nullable();
            $table->string('bride_name')->nullable();
            $table->string('groom_name')->nullable();
            $table->string('akad_time')->nullable();
            $table->string('akad_location')->nullable();
            $table->string('resepsi_time')->nullable();
            $table->string('resepsi_location')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('donation_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'wedding_date',
                'bride_name',
                'groom_name',
                'akad_time',
                'akad_location',
                'resepsi_time',
                'resepsi_location',
                'google_maps_url',
                'donation_info',
            ]);
        });
    }
};

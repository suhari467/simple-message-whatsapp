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
        Schema::table('page_donations', function (Blueprint $table) {
            $table->string('gift_type')->default('transfer')->after('page_id');
            $table->string('bank_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->text('address')->nullable()->after('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_donations', function (Blueprint $table) {
            $table->string('bank_name')->nullable(false)->change();
            $table->string('account_number')->nullable(false)->change();
            $table->dropColumn(['gift_type', 'address']);
        });
    }
};

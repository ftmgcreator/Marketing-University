<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->json('subjects')->nullable()->after('credits_count');
        });
    }

    public function down(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->dropColumn('subjects');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('phone2', 30)->nullable()->after('phone');
            $table->string('region')->nullable()->after('address');
            $table->string('speciality_code', 50)->nullable()->after('speciality_id');
            $table->decimal('contract_amount', 18, 2)->default(0)->after('group_name');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'gender', 'group_name']);
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('group_name', 50)->nullable();

            $table->dropColumn(['phone2', 'region', 'speciality_code', 'contract_amount']);
        });
    }
};

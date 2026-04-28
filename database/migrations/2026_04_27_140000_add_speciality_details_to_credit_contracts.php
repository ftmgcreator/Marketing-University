<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->foreignId('speciality_id')
                ->nullable()
                ->after('speciality')
                ->constrained('specialities')
                ->nullOnDelete();

            $table->string('faculty')->nullable()->after('speciality_id');
            $table->string('education_type')->nullable()->after('faculty');
            $table->string('education_form')->nullable()->after('education_type');

            $table->index('education_type');
            $table->index('education_form');
        });
    }

    public function down(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('speciality_id');
            $table->dropIndex(['education_type']);
            $table->dropIndex(['education_form']);
            $table->dropColumn(['faculty', 'education_type', 'education_form']);
        });
    }
};

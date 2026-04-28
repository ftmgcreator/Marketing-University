<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->string('course', 20)->nullable()->after('education_form');
            $table->string('phone', 30)->nullable()->after('passport');
            $table->string('student_code', 50)->nullable()->after('phone');
            $table->text('address')->nullable()->after('student_code');
        });
    }

    public function down(): void
    {
        Schema::table('credit_contracts', function (Blueprint $table) {
            $table->dropColumn(['course', 'phone', 'student_code', 'address']);
        });
    }
};

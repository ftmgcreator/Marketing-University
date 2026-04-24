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
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->unsignedInteger('department_count')->default(0);
            $table->unsignedInteger('curator_count')->default(0);
            $table->unsignedInteger('group_count')->default(0);
            $table->unsignedInteger('student_count')->default(0);
            $table->unsignedInteger('paid_count')->default(0);
            $table->unsignedInteger('debt_count')->default(0);
            $table->decimal('contract_amount', 18, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('debt_amount', 18, 2)->default(0);
            $table->decimal('percent_paid', 6, 2)->default(0);
            $table->timestamps();

            $table->index(['report_id', 'slug']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('report_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_id');
        });
        Schema::dropIfExists('faculties');
    }
};

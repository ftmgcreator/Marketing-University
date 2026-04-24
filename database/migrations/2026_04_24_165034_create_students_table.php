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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('group_name')->nullable()->index();
            $table->string('faculty')->nullable();
            $table->string('speciality')->nullable();
            $table->string('course')->nullable();
            $table->string('education_form')->nullable();
            $table->string('contract_type')->nullable();
            $table->decimal('previous_year_amount', 18, 2)->default(0);
            $table->decimal('contract_amount', 18, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('debt_amount', 18, 2)->default(0);
            $table->decimal('percent_paid', 6, 2)->default(0);
            $table->boolean('is_debtor')->default(false)->index();
            $table->timestamps();

            $table->index(['report_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->date('contract_date');

            $table->string('full_name');

            $table->string('speciality');
            $table->string('group_name');

            $table->string('jshshir', 14)->nullable();
            $table->string('passport', 30)->nullable();

            $table->string('subject_name')->nullable();
            $table->unsignedInteger('credits_count');
            $table->unsignedBigInteger('price_per_credit')->default(100000);
            $table->unsignedBigInteger('total_amount');

            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->unsignedBigInteger('paid_amount')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('contract_date');
            $table->index('speciality');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_contracts');
    }
};

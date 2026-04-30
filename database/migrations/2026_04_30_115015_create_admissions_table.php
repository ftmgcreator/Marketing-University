<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('jshshir', 14)->nullable()->index();
            $table->string('passport', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable();
            $table->text('address')->nullable();

            $table->foreignId('speciality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('faculty')->nullable();
            $table->string('education_type')->nullable();
            $table->string('education_form')->nullable();
            $table->string('course', 20)->nullable();
            $table->string('group_name', 50)->nullable();

            $table->date('admission_date')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};

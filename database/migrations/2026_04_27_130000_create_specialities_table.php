<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialities', function (Blueprint $table) {
            $table->id();
            $table->string('education_type')->nullable();
            $table->string('faculty')->nullable();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('education_form')->nullable();
            $table->bigInteger('contract_amount')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['code', 'education_form', 'education_type'], 'specialities_unique_idx');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialities');
    }
};

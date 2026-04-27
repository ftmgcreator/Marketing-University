<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $names = DB::table('students')
            ->whereNotNull('speciality')
            ->where('speciality', '!=', '')
            ->distinct()
            ->orderBy('speciality')
            ->pluck('speciality');

        $now = now();
        $rows = $names->map(fn ($name) => [
            'name' => $name,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (! empty($rows)) {
            DB::table('specialities')->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('specialities');
    }
};

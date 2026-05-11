<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('state')->constrained('countries')->nullOnDelete();
        });

        $countries = DB::table('cities')
            ->select('country')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country');

        foreach ($countries as $countryName) {
            $countryId = DB::table('countries')->where('name', $countryName)->value('id');

            if (! $countryId) {
                $countryId = DB::table('countries')->insertGetId([
                    'name' => $countryName,
                    'slug' => Str::slug($countryName),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('cities')->where('country', $countryName)->update(['country_id' => $countryId]);
        }
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};

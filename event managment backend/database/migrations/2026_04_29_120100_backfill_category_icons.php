<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        collect([
            'movies' => 'Ticket',
            'concerts' => 'Music4',
            'stand-up-comedy' => 'Mic2',
            'sports' => 'Trophy',
            'theatre' => 'Drama',
            'festivals' => 'PartyPopper',
            'kids-family' => 'ToyBrick',
            'business-expo' => 'BriefcaseBusiness',
        ])->each(function (string $icon, string $slug): void {
            DB::table('categories')
                ->where('slug', $slug)
                ->whereNull('icon')
                ->update(['icon' => $icon]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', [
                'movies',
                'concerts',
                'stand-up-comedy',
                'sports',
                'theatre',
                'festivals',
                'kids-family',
                'business-expo',
            ])
            ->update(['icon' => null]);
    }
};
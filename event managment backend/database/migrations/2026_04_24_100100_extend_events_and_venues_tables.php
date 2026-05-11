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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->unique();
            $table->string('banner_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('publication_status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('map_url')->nullable();
            $table->unsignedSmallInteger('row_count')->default(10);
            $table->unsignedSmallInteger('column_count')->default(12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn([
                'slug',
                'latitude',
                'longitude',
                'map_url',
                'row_count',
                'column_count',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'slug',
                'banner_image',
                'meta_title',
                'meta_description',
                'publication_status',
                'is_featured',
                'published_at',
            ]);
        });
    }
};

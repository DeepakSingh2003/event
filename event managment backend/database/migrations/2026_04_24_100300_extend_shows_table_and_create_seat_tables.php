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
        Schema::table('shows', function (Blueprint $table) {
            $table->string('status')->default('scheduled');
            $table->timestamp('booking_open_at')->nullable();
            $table->timestamp('booking_close_at')->nullable();
            $table->unsignedInteger('seat_lock_minutes')->default(10);
            $table->timestamp('seat_map_generated_at')->nullable();
        });

        Schema::create('seat_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#1f2937');
            $table->decimal('price_multiplier', 8, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('show_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('row_label');
            $table->unsignedSmallInteger('column_number');
            $table->string('seat_number');
            $table->decimal('base_price', 10, 2);
            $table->decimal('price', 10, 2);
            $table->string('status')->default('available');
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();

            $table->unique(['show_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_seats');
        Schema::dropIfExists('seat_types');

        Schema::table('shows', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'booking_open_at',
                'booking_close_at',
                'seat_lock_minutes',
                'seat_map_generated_at',
            ]);
        });
    }
};

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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('refund_status')->default('not_requested');
            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->string('ticket_path')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_seat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('seat_number');
            $table->string('seat_type_name')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->string('status')->default('confirmed');
            $table->timestamps();
        });

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway');
            $table->string('action');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('initiated');
            $table->text('reason')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('booking_items');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn([
                'status',
                'subtotal',
                'discount_amount',
                'tax_amount',
                'refund_amount',
                'refund_status',
                'payment_gateway',
                'payment_id',
                'qr_token',
                'ticket_path',
                'confirmed_at',
                'cancelled_at',
                'expires_at',
                'notes',
            ]);
        });
    }
};

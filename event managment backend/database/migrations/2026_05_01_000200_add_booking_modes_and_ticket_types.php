<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->string('booking_mode')->default('reserved_seating');
            $table->unsignedInteger('sales_capacity')->nullable();
        });

        Schema::create('show_ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->foreignId('show_ticket_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('show_ticket_type_id');
            $table->dropColumn('quantity');
        });

        Schema::dropIfExists('show_ticket_types');

        Schema::table('shows', function (Blueprint $table) {
            $table->dropColumn(['booking_mode', 'sales_capacity']);
        });
    }
};

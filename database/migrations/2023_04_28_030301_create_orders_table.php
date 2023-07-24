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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->string('email');
            $table->string('phone_no');
            $table->dateTime('placed_at');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('shipped_at');
            $table->dateTime('out_for_delivery_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->date('injection_date');
            $table->string('dog_name');
            $table->string('dog_breed');
            $table->integer('dog_age');
            $table->float('dog_weight');
            $table->enum('dog_gender', ['male', 'female']);
            $table->integer('no_of_elbows');
            $table->decimal('dosage_per_elbow',10,2);
            $table->decimal('total_dosage',10,2);
            $table->foreignId('reactor_id')->constrained('reactors')->onDelete('cascade');
            $table->enum('status', ['pending','confirmed','shipped','out for delivery','delivered','cancelled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

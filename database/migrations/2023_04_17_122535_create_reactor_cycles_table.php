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
        Schema::create('reactor_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('reactor_id')->constrained('reactors')->onDelete('cascade');
            $table->float('mass');
            $table->date('target_start_date');
            $table->date('expiration_date');
            $table->boolean('is_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactor_cycles');
    }
};

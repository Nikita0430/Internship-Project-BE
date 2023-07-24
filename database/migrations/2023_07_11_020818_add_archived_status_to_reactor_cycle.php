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
        Schema::table('reactor_cycles', function (Blueprint $table) {
            $table->enum('archived_status', ['Expired', 'Disabled'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reactor_cycles', function (Blueprint $table) {
            $table->dropColumn('archived_status');
        });
    }
};

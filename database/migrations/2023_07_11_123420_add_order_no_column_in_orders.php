<?php

use App\Models\Order;
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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_no')->nullable();
        });

        $orders = Order::all();
        foreach ($orders as $order) {
            $order->order_no = 'WEBO' . sprintf('%04d', $order->id);
            $order->save();
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_no')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_no');
        });
    }
};

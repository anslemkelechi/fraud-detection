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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained("users")->onDelete('cascade');
            $table->decimal('amount', 8, 2)->default(0);
            $table->string('ip_address');
            $table->string('device_fingerprint');
            $table->boolean('is_new_device');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->enum('recommendation', ['Approve', 'Flag', 'Decline'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

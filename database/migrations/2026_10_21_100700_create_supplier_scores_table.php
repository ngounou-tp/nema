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
        Schema::create('supplier_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->json('details');
            $table->timestamp('computed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_scores');
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained()->cascadeOnDelete();
            $table->string('filiere', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity_kg', 12, 2);
            $table->unsignedInteger('price_xaf');
            $table->enum('quality_grade', ['A', 'B', 'C']);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['filiere', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

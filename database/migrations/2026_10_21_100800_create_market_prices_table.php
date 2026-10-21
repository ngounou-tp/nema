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
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->string('filiere', 30);
            $table->string('region');
            $table->date('date');
            $table->unsignedInteger('price_xaf_kg');
            $table->decimal('volume_t', 10, 2);
            $table->timestamps();

            $table->unique(['filiere', 'region', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};

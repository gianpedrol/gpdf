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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('client_name');
            $table->date('date');
            $table->decimal('total', 10, 2);
            $table->json('payment_methods'); // ['pix', 'boleto', 'cartao']
            $table->json('discounts')->nullable(); // {'pix': 10, 'boleto': 5, 'cartao': 0}
            $table->integer('installments')->nullable(); // Só para cartão
            $table->decimal('total_with_discount', 10, 2)->nullable();
            $table->string('status')->default('enviado');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

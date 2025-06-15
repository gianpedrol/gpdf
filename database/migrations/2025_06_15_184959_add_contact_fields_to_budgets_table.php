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
        Schema::table('budgets', function (Blueprint $table) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->string('client_whatsapp')->nullable();
                $table->string('client_email')->nullable();
                $table->string('client_address')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn([
                'client_whatsapp',
                'client_email',
                'client_address',
                'title',
                'description'
            ]);
        });
    }
};

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
        Schema::create('amount_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->date('fn1_date')->nullable();
            $table->float('fn1_amount');
            $table->date('fn2_date')->nullable();
            $table->float('fn2_amount');
            $table->date('fn3_date')->nullable();
            $table->float('fn3_amount');
            $table->date('fn4_date')->nullable();
            $table->float('fn4_amount');
            $table->date('fn5_date')->nullable();
            $table->float('fn5_amount');
            $table->date('fn6_date')->nullable();
            $table->float('fn6_amount');
            $table->float('outstanding_amount');
            $table->date('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amount_loans');
    }
};

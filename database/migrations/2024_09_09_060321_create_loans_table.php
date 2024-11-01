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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['NID', 'Driver License']);
            $table->float('nid_driver_license_number');
            $table->string('nid_driver_license_file');
            $table->float('work_id_number');
            $table->string('work_id_file');
            $table->string('selfie');
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('payment_period')->constrained('payment_periods')->onDelete('cascade');
            $table->string('pay_slip_1');
            $table->string('pay_slip_2');
            $table->string('pay_slip_3');
            $table->decimal('fn_pay_amount_1', 8, 2)->nullable();
            $table->decimal('fn_pay_amount_2', 8, 2)->nullable();
            $table->decimal('fn_pay_amount_3', 8, 2)->nullable();
            $table->date('next_fn_pay')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Activated', 'Late', 'Blocked'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};

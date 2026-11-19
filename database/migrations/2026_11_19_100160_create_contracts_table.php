<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contract registration register. Code 010.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('giver_name');
            $table->text('giver_address')->nullable();
            $table->unsignedTinyInteger('giver_age')->nullable();
            $table->enum('giver_gender', Gender::values())->nullable();
            $table->string('receiver_name');
            $table->text('receiver_address')->nullable();
            $table->unsignedTinyInteger('receiver_age')->nullable();
            $table->enum('receiver_gender', Gender::values())->nullable();
            $table->text('contract_summary')->nullable();
            $table->string('registration_number');
            $table->string('registered_date_ec')->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->foreignId('approved_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'registration_number'], 'contracts_org_reg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

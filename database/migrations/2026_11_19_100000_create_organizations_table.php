<?php

use App\Enums\OrganizationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', OrganizationType::values());
            $table->foreignId('parent_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

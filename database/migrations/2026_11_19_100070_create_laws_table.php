<?php

use App\Enums\LawCategory;
use App\Enums\LawJurisdiction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laws', function (Blueprint $table) {
            $table->id();
            $table->enum('jurisdiction', LawJurisdiction::values());
            $table->enum('category', LawCategory::values());
            $table->string('title');
            $table->string('law_number')->nullable();
            $table->unsignedSmallInteger('issued_year_ec')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jurisdiction', 'category'], 'laws_jurisdiction_category_idx');
            $table->index('law_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laws');
    }
};

<?php

use App\Enums\CommunityPostType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', CommunityPostType::values());
            $table->string('title');
            $table->text('content');
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};

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
        Schema::create('complaint_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->enum('from_status', ['pending', 'diproses', 'selesai', 'ditolak', 'dibatalkan'])->nullable();
            $table->enum('to_status', ['pending', 'diproses', 'selesai', 'ditolak', 'dibatalkan']);
            $table->text('note')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamp('created_at')->nullable();

            $table->index(['complaint_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_status_histories');
    }
};

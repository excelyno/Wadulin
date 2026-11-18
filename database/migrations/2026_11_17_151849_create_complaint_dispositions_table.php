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
        Schema::create('complaint_dispositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_district_id')->constrained('districts')->restrictOnDelete();
            $table->foreignId('to_district_id')->constrained('districts')->restrictOnDelete();
            $table->foreignId('moved_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_dispositions');
    }
};

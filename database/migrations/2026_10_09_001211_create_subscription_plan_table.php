<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('scope', 20); // user, organization
            $table->string('billing_type', 20); // one_time, recurring
            $table->unsignedBigInteger('price');
            $table->unsignedSmallInteger('duration_days')->nullable(); // null = permanent
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        // Add check constraint for recurring plans, only one time plans can have null/permanent duration
        DB::statement("ALTER TABLE plans ADD CONSTRAINT plans_recurring_has_duration
            CHECK (billing_type = 'one_time' OR duration_days IS NOT NULL)");

        Schema::create('plan_features', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('feature');               // nilai enum Feature
            $table->primary(['plan_id', 'feature']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};

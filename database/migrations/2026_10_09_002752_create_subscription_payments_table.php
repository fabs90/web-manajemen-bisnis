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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignId('replace_subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('status', 20); // pending, active, expired
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();       // null = permanen
            $table->timestamp('canceled_at')->nullable();      // true = tidak diperpanjang
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['status', 'ends_at']);
        });
        // Add check constraint for one owner user/organization
        DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_one_owner
            CHECK ((user_id IS NULL) <> (organization_id IS NULL))');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('provider'); // contohnya: midtrans/xendit
            $table->string('reference')->unique(); // order_id yang dibuat payment gateway
            $table->string('provider_transaction_id')->nullable(); // transaction_id yang dibuat payment gateway
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('pending'); // pending|paid | failed | expired
            $table->string('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            // composite unique: contoh (midtrans, 12345)
            $table->unique(['provider', 'provider_transaction_id']);
            $table->index(['subscription_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
    }
};

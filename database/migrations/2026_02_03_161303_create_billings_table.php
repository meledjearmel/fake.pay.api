<?php

use App\Models\Company;
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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->float('amount');
            $table->year('year');
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_registered')->default(false);
            $table->enum('providence', ['Genuis', 'Port-Collect'])->default('Genuis');
            $table->enum('type', ['icpe', 'lce', 'port'])->default('icpe');
            $table->enum('payment_state', ['pending', 'partial', 'paid'])->default('pending');
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};

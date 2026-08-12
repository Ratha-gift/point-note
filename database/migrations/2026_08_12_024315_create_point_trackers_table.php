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
        Schema::create('point_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');            // ថ្ងៃដែលលក់ (មិនចាំបាច់ដូច created_at)
            $table->decimal('amount', 10, 2);       // ចំនួនទឹកប្រាក់ក្នុង Invoice មួយ
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_entries');
    }
};

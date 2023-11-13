<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sample_tasks', function (Blueprint $table) {
            $table->id();
            $table->intOrBigIntBasedOnRelated('assigned_to', Schema::connection(null), 'users.id')->nullable();
            $table->string('status');
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_tasks');
    }
};

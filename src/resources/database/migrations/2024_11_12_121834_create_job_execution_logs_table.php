<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('job_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_execution_id')->constrained();
            $table->timestamp('happened_at');
            $table->string('level', 9);
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index('happened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_execution_logs');
    }
};

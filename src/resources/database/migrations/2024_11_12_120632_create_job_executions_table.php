<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('job_executions', function (Blueprint $table) {
            $table->id();
            $table->string('job_class');
            $table->uuid('job_uuid')->nullable();
            $table->string('tracking_id', 22)->unique();
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable();
            $table->string('via', 32)->nullable();
            $table->string('scene')->nullable();
            $table->ipAddress()->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('progress_max')->default(100);
            $table->integer('current_progress')->default(0);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index('queued_at');
            $table->index('job_class');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_executions');
    }
};

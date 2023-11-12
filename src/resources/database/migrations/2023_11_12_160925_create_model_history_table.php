<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_history', function (Blueprint $table) {
            $table->id();
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable();
            $table->ipAddress()->nullable();
            $table->string('user_agent')->nullable();
            $table->morphs('model');
            $table->json('diff');
            $table->text('comment')->nullable();
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_history');
    }
};

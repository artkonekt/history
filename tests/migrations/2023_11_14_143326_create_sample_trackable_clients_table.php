<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('sample_trackable_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->string('api_key');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_trackable_clients');
    }
};

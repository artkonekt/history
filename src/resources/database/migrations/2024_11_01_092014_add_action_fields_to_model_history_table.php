<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('model_history', function (Blueprint $table) {
            $table->boolean('was_successful')->nullable();
            $table->text('details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('model_history', function (Blueprint $table) {
            $table->dropColumn('was_successful');
            $table->dropColumn('details');
        });
    }
};

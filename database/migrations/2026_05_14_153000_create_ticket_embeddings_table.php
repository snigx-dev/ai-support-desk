<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            Schema::ensureVectorExtensionExists();
        }

        Schema::create('ticket_embeddings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete()->unique();
            $table->longText('content');
            $table->char('content_hash', 64);

            if (in_array(Schema::getConnection()->getDriverName(), ['pgsql', 'mysql'], true)) {
                $table->vector('embedding', dimensions: 1536);
            } else {
                $table->longText('embedding');
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_embeddings');
    }
};

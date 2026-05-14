<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->longText('ai_reply')->nullable()->after('ai_raw_response');
            $table->float('ai_reply_confidence')->nullable()->after('ai_reply');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn(['ai_reply', 'ai_reply_confidence']);
        });
    }
};

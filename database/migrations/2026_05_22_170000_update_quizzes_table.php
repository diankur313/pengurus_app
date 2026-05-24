<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('id');
            $table->string('title')->after('created_by');
            $table->text('description')->nullable()->after('title');
            $table->dateTime('start_at')->after('description');
            $table->dateTime('end_at')->after('start_at');
            $table->boolean('is_published')->default(false)->after('end_at');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'title', 'description', 'start_at', 'end_at', 'is_published']);
        });
    }
};

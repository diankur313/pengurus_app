<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->uuid('civitas_id')->index();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->decimal('mc_score', 5, 2)->nullable();
            $table->decimal('essay_score', 5, 2)->nullable();
            $table->enum('status', ['submitted', 'reviewed'])->default('submitted');
            $table->dateTime('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'civitas_id'], 'quiz_submission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};

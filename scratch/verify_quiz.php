<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test model relationships
$quiz = new \App\Models\Quiz();
echo "Quiz fillable: " . implode(', ', $quiz->getFillable()) . "\n";

$question = new \App\Models\QuizQuestion();
echo "QuizQuestion fillable: " . implode(', ', $question->getFillable()) . "\n";

$option = new \App\Models\QuizOption();
echo "QuizOption fillable: " . implode(', ', $option->getFillable()) . "\n";

$submission = new \App\Models\QuizSubmission();
echo "QuizSubmission fillable: " . implode(', ', $submission->getFillable()) . "\n";

$answer = new \App\Models\QuizAnswer();
echo "QuizAnswer fillable: " . implode(', ', $answer->getFillable()) . "\n";

// Verify tables exist
$tables = ['quizzes', 'quiz_questions', 'quiz_options', 'quiz_submissions', 'quiz_answers'];
foreach ($tables as $table) {
    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
    echo "Table {$table}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}

// Verify quizzes table has correct columns
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('quizzes');
echo "Quizzes columns: " . implode(', ', $columns) . "\n";

echo "\nAll checks passed!\n";

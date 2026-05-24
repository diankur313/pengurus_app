<?php
$files = [
    'app/Filament/Resources/QuizResource.php',
    'app/Models/Quiz.php',
    'app/Models/QuizQuestion.php',
    'app/Models/QuizOption.php',
    'app/Models/QuizSubmission.php',
    'app/Models/QuizAnswer.php',
    'app/Filament/Resources/QuizResource/Pages/ListQuizzes.php',
    'app/Filament/Resources/QuizResource/Pages/CreateQuiz.php',
    'app/Filament/Resources/QuizResource/Pages/EditQuiz.php',
];

$allOk = true;
foreach ($files as $file) {
    $output = shell_exec("php -l {$file} 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        echo "ERROR in {$file}: {$output}\n";
        $allOk = false;
    } else {
        echo "OK: {$file}\n";
    }
}

echo $allOk ? "\nAll files OK!\n" : "\nSome files have errors.\n";

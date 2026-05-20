<?php

$models = [
    'Attendance',
    'Civitas',
    'EducationSchedule',
    'Payment',
    'Quiz',
    'Teacher',
];

foreach ($models as $model) {
    $permissionName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $model));
    $policyName = $model . 'Policy';
    $path = __DIR__ . "/app/Policies/{$policyName}.php";
    
    $content = <<<PHP
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\\{$model};
use Illuminate\Auth\Access\HandlesAuthorization;

class {$policyName}
{
    use HandlesAuthorization;

    public function viewAny(User \$user): bool
    {
        return \$user->can('view_any_{$permissionName}');
    }

    public function view(User \$user, {$model} \$model): bool
    {
        return \$user->can('view_{$permissionName}');
    }

    public function create(User \$user): bool
    {
        return \$user->can('create_{$permissionName}');
    }

    public function update(User \$user, {$model} \$model): bool
    {
        return \$user->can('update_{$permissionName}');
    }

    public function delete(User \$user, {$model} \$model): bool
    {
        return \$user->can('delete_{$permissionName}');
    }

    public function deleteAny(User \$user): bool
    {
        return \$user->can('delete_any_{$permissionName}');
    }

    public function forceDelete(User \$user, {$model} \$model): bool
    {
        return \$user->can('force_delete_{$permissionName}');
    }

    public function forceDeleteAny(User \$user): bool
    {
        return \$user->can('force_delete_any_{$permissionName}');
    }

    public function restore(User \$user, {$model} \$model): bool
    {
        return \$user->can('restore_{$permissionName}');
    }

    public function restoreAny(User \$user): bool
    {
        return \$user->can('restore_any_{$permissionName}');
    }

    public function replicate(User \$user, {$model} \$model): bool
    {
        return \$user->can('replicate_{$permissionName}');
    }

    public function reorder(User \$user): bool
    {
        return \$user->can('reorder_{$permissionName}');
    }
}
PHP;

    file_put_contents($path, $content);
    echo "Created policy for {$model}\n";
}

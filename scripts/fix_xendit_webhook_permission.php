<?php

// Script: Daftarkan permission xendit_webhook ke Shield dan assign ke role super_admin
// Jalankan: php artisan tinker --execute="require base_path('scripts/fix_xendit_webhook_permission.php');"

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$guard = 'web';

$permissions = [
    'view_any_xendit::webhook',
    'view_xendit::webhook',
    'create_xendit::webhook',
    'update_xendit::webhook',
    'delete_xendit::webhook',
    'delete_any_xendit::webhook',
    'force_delete_xendit::webhook',
    'force_delete_any_xendit::webhook',
    'restore_xendit::webhook',
    'restore_any_xendit::webhook',
    'replicate_xendit::webhook',
    'reorder_xendit::webhook',
];

foreach ($permissions as $perm) {
    Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
    echo "OK: {$perm}\n";
}

$role = Role::where('name', 'super_admin')->where('guard_name', $guard)->first();
if ($role) {
    $role->syncPermissions(Permission::all());
    echo "\nSemua permission sudah di-assign ke role super_admin.\n";
} else {
    echo "\nRole super_admin tidak ditemukan!\n";
}

echo "Selesai.\n";

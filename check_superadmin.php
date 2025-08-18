<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== Checking for Superadmin in Database ===\n\n";

// Check if roles table exists and has data
try {
    $roles = Role::all();
    echo "✅ Found {$roles->count()} role(s) in database:\n";
    foreach ($roles as $role) {
        echo "  - ID: {$role->id}, Name: {$role->name}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "⚠️  Roles table issue: {$e->getMessage()}\n\n";
}

// Check for users with admin-like emails
$adminEmails = [
    'admin@laravel-brive.com',
    'superadmin@laravel-brive.com', 
    'admin@admin.com',
    'superadmin@admin.com',
    'admin@example.com'
];

echo "Checking for admin users by email:\n";
$foundAdmins = [];

foreach ($adminEmails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $foundAdmins[] = $user;
        echo "✅ Found admin user: {$user->name} ({$user->email})\n";
        
        // Check user roles if using Spatie roles
        try {
            if (method_exists($user, 'getRoleNames')) {
                $userRoles = $user->getRoleNames();
                echo "   - Roles: " . ($userRoles->count() > 0 ? $userRoles->implode(', ') : 'No roles assigned') . "\n";
            }
        } catch (Exception $e) {
            echo "   - Role check failed: {$e->getMessage()}\n";
        }
    }
}

if (empty($foundAdmins)) {
    echo "❌ No admin users found with common admin emails\n";
}

echo "\n=== All Users in Database ===\n";
$allUsers = User::all(['id', 'name', 'email', 'created_at']);
echo "Total users: {$allUsers->count()}\n";

foreach ($allUsers as $user) {
    echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    
    // Check if user has admin-like characteristics
    if (stripos($user->email, 'admin') !== false || stripos($user->name, 'admin') !== false) {
        echo "    🔑 Potential admin user detected\n";
    }
}

echo "\n=== Summary ===\n";
if (!empty($foundAdmins)) {
    echo "✅ Found " . count($foundAdmins) . " admin user(s)\n";
    echo "Superadmin status: CONFIRMED\n";
} else {
    echo "❌ No superadmin found\n";
    echo "Recommendation: Create a superadmin user\n";
}
<?php

// Simple debug script to test registration validation
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test data with mismatched passwords
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'different_password'
];

echo "Testing password confirmation validation...\n";
echo "Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Test Laravel validation directly
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

$validator = Validator::make($testData, [
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255',
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);

if ($validator->fails()) {
    echo "Validation FAILED (as expected):\n";
    foreach ($validator->errors()->all() as $error) {
        echo "- $error\n";
    }
    
    echo "\nSpecific password errors:\n";
    if ($validator->errors()->has('password')) {
        foreach ($validator->errors()->get('password') as $error) {
            echo "- $error\n";
        }
    }
} else {
    echo "Validation PASSED (unexpected!)\n";
}

echo "\n" . str_repeat('-', 50) . "\n";

// Test with matching passwords
$testData2 = [
    'name' => 'Test User',
    'email' => 'test2@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

echo "Testing with matching passwords...\n";
echo "Data: " . json_encode($testData2, JSON_PRETTY_PRINT) . "\n\n";

$validator2 = Validator::make($testData2, [
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255',
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);

if ($validator2->fails()) {
    echo "Validation FAILED (unexpected):\n";
    foreach ($validator2->errors()->all() as $error) {
        echo "- $error\n";
    }
} else {
    echo "Validation PASSED (as expected)\n";
}
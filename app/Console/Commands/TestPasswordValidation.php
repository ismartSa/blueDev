<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class TestPasswordValidation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:password-validation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test password confirmation validation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing password confirmation validation...');
        
        // Test with mismatched passwords
        $testData1 = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ];
        
        $this->line('\nTest 1: Mismatched passwords');
        $this->line('Data: ' . json_encode($testData1, JSON_PRETTY_PRINT));
        
        $validator1 = Validator::make($testData1, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        if ($validator1->fails()) {
            $this->error('Validation FAILED (as expected):');
            foreach ($validator1->errors()->all() as $error) {
                $this->line('- ' . $error);
            }
            
            if ($validator1->errors()->has('password')) {
                $this->line('\nPassword-specific errors:');
                foreach ($validator1->errors()->get('password') as $error) {
                    $this->line('- ' . $error);
                }
            }
        } else {
            $this->error('Validation PASSED (unexpected!)');
        }
        
        // Test with matching passwords
        $testData2 = [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        $this->line('\n' . str_repeat('-', 50));
        $this->line('Test 2: Matching passwords');
        $this->line('Data: ' . json_encode($testData2, JSON_PRETTY_PRINT));
        
        $validator2 = Validator::make($testData2, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        if ($validator2->fails()) {
            $this->error('Validation FAILED (unexpected):');
            foreach ($validator2->errors()->all() as $error) {
                $this->line('- ' . $error);
            }
        } else {
            $this->info('Validation PASSED (as expected)');
        }
        
        return 0;
    }
}

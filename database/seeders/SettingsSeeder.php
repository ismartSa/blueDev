<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding Settings
            [
                'key' => 'app_name',
                'value' => 'siteeblue',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Application name displayed throughout the site'
            ],
            [
                'key' => 'app_tagline',
                'value' => 'Learn, Grow, Succeed',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Application tagline or slogan'
            ],
            [
                'key' => 'app_description',
                'value' => 'A comprehensive learning management system for modern education',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Application description for SEO and about pages'
            ],
            [
                'key' => 'app_logo',
                'value' => '/images/logo.png',
                'type' => 'file',
                'group' => 'branding',
                'description' => 'Main application logo'
            ],
            [
                'key' => 'app_favicon',
                'value' => '/favicon.ico',
                'type' => 'file',
                'group' => 'branding',
                'description' => 'Application favicon'
            ],
            [
                'key' => 'primary_color',
                'value' => '#3B82F6',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Primary brand color'
            ],
            [
                'key' => 'secondary_color',
                'value' => '#10B981',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Secondary brand color'
            ],

            // Contact Information
            [
                'key' => 'contact_email',
                'value' => 'info@laravelbrive.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Main contact email address'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Main contact phone number'
            ],
            [
                'key' => 'contact_address',
                'value' => '123 Education Street, Learning City, LC 12345',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Physical address'
            ],

            // Social Media
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/laravelbrive',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Facebook page URL'
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/laravelbrive',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter profile URL'
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/laravelbrive',
                'type' => 'string',
                'group' => 'social',
                'description' => 'LinkedIn company page URL'
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/laravelbrive',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram profile URL'
            ],

            // System Settings
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable maintenance mode'
            ],
            [
                'key' => 'user_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Allow user registration'
            ],
            [
                'key' => 'default_language',
                'value' => 'en',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default application language'
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default timezone'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

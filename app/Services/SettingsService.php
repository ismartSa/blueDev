<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get all settings grouped by category
     */
    public static function all(): array
    {
        return Cache::remember('all_settings', 3600, function () {
            return Setting::all()->groupBy('group')->map(function ($settings) {
                return $settings->mapWithKeys(function ($setting) {
                    $value = match ($setting->type) {
                        'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                        'integer' => (int) $setting->value,
                        'json' => json_decode($setting->value, true),
                        default => $setting->value
                    };
                    
                    return [$setting->key => $value];
                });
            })->toArray();
        });
    }

    /**
     * Get branding settings
     */
    public static function branding(): array
    {
        return Setting::getByGroup('branding');
    }

    /**
     * Get contact settings
     */
    public static function contact(): array
    {
        return Setting::getByGroup('contact');
    }

    /**
     * Get social media settings
     */
    public static function social(): array
    {
        return Setting::getByGroup('social');
    }

    /**
     * Get system settings
     */
    public static function system(): array
    {
        return Setting::getByGroup('system');
    }

    /**
     * Get app name
     */
    public static function appName(): string
    {
        return Setting::get('app_name', config('app.name'));
    }

    /**
     * Get app logo
     */
    public static function appLogo(): string
    {
        return Setting::get('app_logo', '/images/logo.png');
    }

    /**
     * Get app tagline
     */
    public static function appTagline(): string
    {
        return Setting::get('app_tagline', 'Learn, Grow, Succeed');
    }

    /**
     * Get app description
     */
    public static function appDescription(): string
    {
        return Setting::get('app_description', 'A comprehensive learning management system');
    }

    /**
     * Get primary color
     */
    public static function primaryColor(): string
    {
        return Setting::get('primary_color', '#3B82F6');
    }

    /**
     * Get secondary color
     */
    public static function secondaryColor(): string
    {
        return Setting::get('secondary_color', '#10B981');
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return Setting::get('maintenance_mode', false);
    }

    /**
     * Check if user registration is enabled
     */
    public static function isRegistrationEnabled(): bool
    {
        return Setting::get('user_registration', true);
    }

    /**
     * Get contact email
     */
    public static function contactEmail(): string
    {
        return Setting::get('contact_email', 'info@example.com');
    }

    /**
     * Get contact phone
     */
    public static function contactPhone(): string
    {
        return Setting::get('contact_phone', '');
    }

    /**
     * Get contact address
     */
    public static function contactAddress(): string
    {
        return Setting::get('contact_address', '');
    }

    /**
     * Get social media links
     */
    public static function socialLinks(): array
    {
        return [
            'facebook' => Setting::get('social_facebook', ''),
            'twitter' => Setting::get('social_twitter', ''),
            'linkedin' => Setting::get('social_linkedin', ''),
            'instagram' => Setting::get('social_instagram', '')
        ];
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Setting::clearCache();
    }
}
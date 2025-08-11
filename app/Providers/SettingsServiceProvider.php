<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all Inertia views
        Inertia::share([
            'settings' => function () {
                return [
                    'app_name' => SettingsService::appName(),
                    'app_logo' => SettingsService::appLogo(),
                    'app_tagline' => SettingsService::appTagline(),
                    'app_description' => SettingsService::appDescription(),
                    'primary_color' => SettingsService::primaryColor(),
                    'secondary_color' => SettingsService::secondaryColor(),
                    'contact_email' => SettingsService::contactEmail(),
                    'contact_phone' => SettingsService::contactPhone(),
                    'contact_address' => SettingsService::contactAddress(),
                    'social_links' => SettingsService::socialLinks(),
                    'maintenance_mode' => SettingsService::isMaintenanceMode(),
                    'registration_enabled' => SettingsService::isRegistrationEnabled(),
                ];
            },
        ]);
    }
}
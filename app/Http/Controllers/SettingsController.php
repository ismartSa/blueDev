<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        return Inertia::render('Settings', [
            'settings' => $settings,
            'groups' => [
                'branding' => 'Branding & Appearance',
                'contact' => 'Contact Information',
                'social' => 'Social Media',
                'system' => 'System Settings'
            ]
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,boolean,integer,json,file'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($request->settings as $settingData) {
            $setting = Setting::where('key', $settingData['key'])->first();
            
            if (!$setting) {
                continue;
            }

            $value = $settingData['value'];

            // Handle file uploads
            if ($setting->type === 'file' && $request->hasFile("file_{$setting->key}")) {
                $file = $request->file("file_{$setting->key}");
                
                // Delete old file if exists
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }
                
                // Store new file
                $path = $file->store('settings', 'public');
                $value = "/storage/{$path}";
            }

            Setting::set(
                $setting->key,
                $value,
                $setting->type,
                $setting->group,
                $setting->description
            );
        }

        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Upload file for setting
     */
    public function uploadFile(Request $request, string $key)
    {
        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,svg,ico|max:2048'
        ]);

        $setting = Setting::where('key', $key)->first();
        
        if (!$setting || $setting->type !== 'file') {
            return response()->json(['error' => 'Invalid setting key'], 400);
        }

        // Delete old file
        if ($setting->value && Storage::disk('public')->exists(str_replace('/storage/', '', $setting->value))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $setting->value));
        }

        // Store new file
        $path = $request->file('file')->store('settings', 'public');
        $url = "/storage/{$path}";

        Setting::set($setting->key, $url, $setting->type, $setting->group, $setting->description);

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    /**
     * Get setting value by key
     */
    public function getSetting(string $key)
    {
        $value = Setting::get($key);
        
        return response()->json([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        // Clear all settings
        Setting::truncate();
        
        // Run seeder
        Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);
        
        Setting::clearCache();

        return back()->with('success', 'Settings reset to default values!');
    }
}
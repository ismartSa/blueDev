<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Display analytics dashboard
     */
    public function dashboard(): Response
    {
        $analytics = $this->analyticsService->getCachedAnalytics();
        
        return Inertia::render('Analytics/Dashboard', [
            'analytics' => $analytics,
            'lastUpdated' => now()->toISOString(),
        ]);
    }

    /**
     * Get analytics data as JSON
     */
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', 'daily');
        $report = $this->analyticsService->generateReport($period);
        
        return response()->json($report);
    }

    /**
     * Track custom event
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'event' => 'required|string|max:255',
            'data' => 'nullable|array',
        ]);

        $this->analyticsService->trackEvent(
            $request->input('event'),
            $request->input('data', [])
        );

        return response()->json(['success' => true]);
    }

    /**
     * Get course averages
     */
    public function courseAverages(): JsonResponse
    {
        $averages = $this->analyticsService->calculateCourseAverages();
        
        return response()->json($averages);
    }

    /**
     * Refresh analytics cache
     */
    public function refresh(): JsonResponse
    {
        $analytics = $this->analyticsService->calculateCourseAverages();
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
            'refreshed_at' => now()->toISOString(),
        ]);
    }

    /**
     * Health check endpoint for monitoring
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ]);
    }
}

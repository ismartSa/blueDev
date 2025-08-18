<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analyticsService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_calculate_course_averages()
    {
        // Create test courses
        Course::factory()->count(5)->create([
            'price' => 100,
            'duration' => 10
        ]);

        $result = $this->analyticsService->calculateCourseAverages();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_courses', $result);
        $this->assertArrayHasKey('average_price', $result);
        $this->assertArrayHasKey('average_duration', $result);
        $this->assertEquals(5, $result['total_courses']);
        $this->assertEquals(100, $result['average_price']);
        $this->assertEquals(10, $result['average_duration']);
    }

    /** @test */
    public function it_caches_analytics_data()
    {
        Course::factory()->count(3)->create();
        
        // First call should calculate and cache
        $result1 = $this->analyticsService->getCachedAnalytics();
        
        // Second call should return cached data
        $result2 = $this->analyticsService->getCachedAnalytics();
        
        $this->assertEquals($result1, $result2);
        $this->assertTrue(Cache::has('course_analytics'));
    }

    /** @test */
    public function it_can_track_custom_events()
    {
        $eventName = 'course_completed';
        $eventData = ['course_id' => 1, 'user_id' => $this->user->id];

        $this->analyticsService->trackEvent($eventName, $eventData);

        $this->assertDatabaseHas('analytics_events', [
            'event_name' => $eventName,
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_generate_analytics_report()
    {
        Course::factory()->count(2)->create();
        
        $report = $this->analyticsService->generateReport('daily');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertArrayHasKey('metrics', $report);
        $this->assertArrayHasKey('trends', $report);
        $this->assertEquals('daily', $report['period']);
    }

    /** @test */
    public function analytics_dashboard_returns_correct_view()
    {
        $response = $this->get(route('analytics.dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Analytics/Dashboard')
                 ->has('analytics')
                 ->has('lastUpdated')
        );
    }

    /** @test */
    public function analytics_data_endpoint_returns_json()
    {
        Course::factory()->count(3)->create();
        
        $response = $this->getJson(route('analytics.data'));
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'period',
                     'generated_at',
                     'metrics' => [
                         'total_courses',
                         'average_price',
                         'average_duration'
                     ],
                     'trends'
                 ]);
    }

    /** @test */
    public function it_can_track_events_via_api()
    {
        $eventData = [
            'event' => 'quiz_started',
            'data' => ['quiz_id' => 1, 'user_id' => $this->user->id]
        ];

        $response = $this->postJson(route('analytics.track'), $eventData);
        
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseHas('analytics_events', [
            'event_name' => 'quiz_started',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function track_event_validates_required_fields()
    {
        $response = $this->postJson(route('analytics.track'), []);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['event']);
    }

    /** @test */
    public function course_averages_endpoint_returns_calculations()
    {
        Course::factory()->count(4)->create([
            'price' => 150,
            'duration' => 8
        ]);
        
        $response = $this->getJson(route('analytics.course-averages'));
        
        $response->assertStatus(200)
                 ->assertJson([
                     'total_courses' => 4,
                     'average_price' => 150,
                     'average_duration' => 8
                 ]);
    }

    /** @test */
    public function refresh_endpoint_clears_cache_and_recalculates()
    {
        // Set initial cache
        Cache::put('course_analytics', ['total_courses' => 1], 3600);
        
        // Create new courses
        Course::factory()->count(3)->create();
        
        $response = $this->postJson(route('analytics.refresh'));
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['total_courses'],
                     'refreshed_at'
                 ]);
                 
        // Verify cache was updated
        $cachedData = Cache::get('course_analytics');
        $this->assertEquals(3, $cachedData['total_courses']);
    }

    /** @test */
    public function health_endpoint_returns_system_status()
    {
        $response = $this->getJson(route('analytics.health'));
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'timestamp',
                     'version',
                     'environment'
                 ])
                 ->assertJson(['status' => 'healthy']);
    }

    /** @test */
    public function analytics_handles_empty_database_gracefully()
    {
        // Ensure no courses exist
        Course::query()->delete();
        
        $result = $this->analyticsService->calculateCourseAverages();
        
        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total_courses']);
        $this->assertEquals(0, $result['average_price']);
        $this->assertEquals(0, $result['average_duration']);
    }

    /** @test */
    public function analytics_service_handles_exceptions_gracefully()
    {
        // Mock database failure
        DB::shouldReceive('table')->andThrow(new \Exception('Database error'));
        
        $result = $this->analyticsService->calculateCourseAverages();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function user_engagement_metrics_are_calculated()
    {
        // Create users with recent login
        User::factory()->count(5)->create([
            'last_login_at' => now()
        ]);
        
        $result = $this->analyticsService->calculateCourseAverages();
        
        $this->assertArrayHasKey('user_engagement', $result);
        $this->assertIsArray($result['user_engagement']);
        $this->assertArrayHasKey('active_users_today', $result['user_engagement']);
    }

    /** @test */
    public function completion_rate_is_calculated_correctly()
    {
        $course = Course::factory()->create();
        
        // Create enrollments with different completion status
        DB::table('course_user')->insert([
            ['course_id' => $course->id, 'user_id' => User::factory()->create()->id, 'completed' => true],
            ['course_id' => $course->id, 'user_id' => User::factory()->create()->id, 'completed' => false],
            ['course_id' => $course->id, 'user_id' => User::factory()->create()->id, 'completed' => true],
        ]);
        
        $result = $this->analyticsService->calculateCourseAverages();
        
        $this->assertArrayHasKey('completion_rate', $result);
        $this->assertEquals(66.67, round($result['completion_rate'], 2));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
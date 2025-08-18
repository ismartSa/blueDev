# Performance Monitoring and Error Tracking Setup Guide

This guide covers the complete setup and configuration of performance monitoring and error tracking for Laravel-Brive production deployment.

## Overview

The monitoring system provides:
- **Error Tracking**: Sentry integration for exception monitoring
- **Performance Monitoring**: Application performance metrics and slow query detection
- **System Monitoring**: Server metrics with Prometheus and Grafana
- **Log Monitoring**: Centralized logging with ELK stack
- **Health Checks**: Automated application health monitoring
- **Uptime Monitoring**: Continuous availability monitoring
- **Alerting**: Real-time notifications for issues

## Prerequisites

- Ubuntu/Debian server with root access
- Laravel-Brive application deployed
- Docker and Docker Compose (for monitoring stack)
- Email server configured (for alerts)
- Sufficient disk space for logs and metrics

## 1. Quick Setup

### Run Automated Setup Script

```bash
# Navigate to Laravel-Brive directory
cd /var/www/laravel-brive

# Run the monitoring setup script
sudo ./deployment/scripts/setup-monitoring.sh
```

The script will:
1. Install Sentry for error tracking
2. Set up application performance monitoring
3. Configure health check endpoints
4. Set up uptime monitoring
5. Optionally install system and log monitoring

## 2. Sentry Error Tracking Configuration

### Get Sentry DSN

1. Create account at [sentry.io](https://sentry.io)
2. Create new Laravel project
3. Copy the DSN from project settings

### Configure Environment Variables

Add to your `.env` file:

```env
# Sentry Configuration
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_PROFILES_SAMPLE_RATE=0.2
```

### Advanced Sentry Configuration

Edit `config/sentry.php`:

```php
<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'release' => env('SENTRY_RELEASE'),
    'environment' => env('APP_ENV', 'production'),
    
    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'sql_transactions' => true,
        'command_info' => true,
    ],
    
    'tracing' => [
        'spans' => [
            'sql_queries' => true,
            'redis' => true,
            'http_client_requests' => true,
        ],
    ],
    
    'send_default_pii' => false,
    
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),
];
```

### Custom Error Context

Add user context in your authentication:

```php
// In your LoginController or middleware
use Sentry\State\Scope;

\Sentry\configureScope(function (Scope $scope): void {
    $scope->setUser([
        'id' => auth()->id(),
        'email' => auth()->user()->email,
        'username' => auth()->user()->name,
    ]);
});
```

## 3. Performance Monitoring Configuration

### Laravel Telescope Setup

Telescope is automatically installed by the setup script. Configure it:

```php
// config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', true),
'domain' => env('TELESCOPE_DOMAIN', null),
'path' => env('TELESCOPE_PATH', 'telescope'),

'watchers' => [
    Watchers\CacheWatcher::class => env('TELESCOPE_CACHE_WATCHER', true),
    Watchers\CommandWatcher::class => env('TELESCOPE_COMMAND_WATCHER', true),
    Watchers\DumpWatcher::class => env('TELESCOPE_DUMP_WATCHER', true),
    Watchers\EventWatcher::class => env('TELESCOPE_EVENT_WATCHER', true),
    Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
    Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
    Watchers\LogWatcher::class => env('TELESCOPE_LOG_WATCHER', true),
    Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),
    Watchers\ModelWatcher::class => [
        'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
        'hydrations' => true,
    ],
    Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),
    Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100, // Log queries slower than 100ms
    ],
    Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),
    Watchers\RequestWatcher::class => [
        'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
        'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
    ],
    Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
    Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', true),
],
```

### Performance Middleware Configuration

The setup script creates a performance monitoring middleware. Configure thresholds:

```php
// In PerformanceMonitoring middleware
private const SLOW_REQUEST_THRESHOLD = 1000; // 1 second
private const HIGH_MEMORY_THRESHOLD = 50; // 50MB

// Log different severity levels
if ($executionTime > 5000) { // 5 seconds
    Log::error('Very slow request detected', $logData);
} elseif ($executionTime > 2000) { // 2 seconds
    Log::warning('Slow request detected', $logData);
} elseif ($executionTime > 1000) { // 1 second
    Log::info('Moderately slow request', $logData);
}
```

## 4. System Monitoring with Prometheus and Grafana

### Access Grafana Dashboard

After running the setup script:

1. Open http://localhost:3000
2. Login with admin/laravel-brive-admin
3. Import Laravel dashboard

### Custom Laravel Metrics Endpoint

Create metrics endpoint for Prometheus:

```php
// routes/web.php
Route::get('/metrics', function () {
    $metrics = [
        '# HELP laravel_users_total Total number of users',
        '# TYPE laravel_users_total counter',
        'laravel_users_total ' . \App\Models\User::count(),
        
        '# HELP laravel_courses_total Total number of courses',
        '# TYPE laravel_courses_total counter', 
        'laravel_courses_total ' . \App\Models\Course::count(),
        
        '# HELP laravel_active_sessions Active user sessions',
        '# TYPE laravel_active_sessions gauge',
        'laravel_active_sessions ' . \DB::table('sessions')->count(),
        
        '# HELP laravel_cache_hits_total Cache hits',
        '# TYPE laravel_cache_hits_total counter',
        'laravel_cache_hits_total ' . \Cache::get('cache_hits', 0),
        
        '# HELP laravel_queue_jobs_total Queued jobs',
        '# TYPE laravel_queue_jobs_total gauge',
        'laravel_queue_jobs_total ' . \DB::table('jobs')->count(),
    ];
    
    return response(implode("\n", $metrics))
        ->header('Content-Type', 'text/plain; version=0.0.4');
});
```

### Custom Grafana Dashboard

Create dashboard JSON:

```json
{
  "dashboard": {
    "title": "Laravel-Brive Monitoring",
    "panels": [
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(nginx_http_request_duration_seconds_sum[5m]) / rate(nginx_http_request_duration_seconds_count[5m])",
            "legendFormat": "Average Response Time"
          }
        ]
      },
      {
        "title": "Active Users",
        "type": "singlestat",
        "targets": [
          {
            "expr": "laravel_active_sessions",
            "legendFormat": "Active Sessions"
          }
        ]
      },
      {
        "title": "Database Queries",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(laravel_database_queries_total[5m])",
            "legendFormat": "Queries per second"
          }
        ]
      }
    ]
  }
}
```

## 5. Log Monitoring with ELK Stack

### Access Kibana Dashboard

After ELK installation:

1. Open http://localhost:5601
2. Create index pattern: `laravel-brive-*`
3. Set timestamp field: `@timestamp`

### Custom Log Formats

Configure Laravel logging in `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'sentry'],
        'ignore_exceptions' => false,
    ],
    
    'performance' => [
        'driver' => 'single',
        'path' => storage_path('logs/performance.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
    ],
    
    'security' => [
        'driver' => 'single',
        'path' => storage_path('logs/security.log'),
        'level' => 'warning',
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
    ],
],
```

### Structured Logging

Use structured logging in your application:

```php
// Log performance metrics
Log::channel('performance')->info('Request processed', [
    'url' => request()->fullUrl(),
    'method' => request()->method(),
    'user_id' => auth()->id(),
    'execution_time' => $executionTime,
    'memory_usage' => $memoryUsage,
    'query_count' => \DB::getQueryLog(),
]);

// Log security events
Log::channel('security')->warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'timestamp' => now(),
]);
```

## 6. Health Check Configuration

### Health Check Endpoints

The setup script creates health check endpoints:

- `/health` - Comprehensive health check
- `/ping` - Simple availability check

### Custom Health Checks

Add custom checks to `HealthCheckController`:

```php
/**
 * Check external API connectivity
 */
private function checkExternalAPIs(): array
{
    $apis = [
        'payment_gateway' => 'https://api.stripe.com/v1/charges',
        'email_service' => 'https://api.mailgun.net/v3/domains',
    ];
    
    $results = [];
    
    foreach ($apis as $name => $url) {
        try {
            $response = Http::timeout(5)->get($url);
            $results[$name] = [
                'status' => $response->successful() ? 'ok' : 'error',
                'response_time' => $response->handlerStats()['total_time'] ?? 0,
            ];
        } catch (\Exception $e) {
            $results[$name] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    return [
        'status' => collect($results)->every(fn($r) => $r['status'] === 'ok') ? 'ok' : 'error',
        'message' => 'External API connectivity check',
        'details' => $results,
    ];
}
```

## 7. Alerting Configuration

### Email Alerts

Configure mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=alerts@laravel-brive.com
MAIL_FROM_NAME="Laravel-Brive Alerts"
```

### Slack Notifications

Install Slack notification channel:

```bash
composer require laravel/slack-notification-channel
```

Create notification class:

```php
php artisan make:notification SystemAlert
```

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SystemAlert extends Notification
{
    use Queueable;
    
    public function __construct(
        private string $title,
        private string $message,
        private string $level = 'error'
    ) {}
    
    public function via($notifiable): array
    {
        return ['slack'];
    }
    
    public function toSlack($notifiable): SlackMessage
    {
        $color = match($this->level) {
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'good',
            default => 'good',
        };
        
        return (new SlackMessage)
            ->to('#alerts')
            ->content($this->title)
            ->attachment(function ($attachment) use ($color) {
                $attachment->title($this->title)
                          ->content($this->message)
                          ->color($color)
                          ->timestamp(now());
            });
    }
}
```

### Custom Alert Rules

Create alert service:

```php
<?php

namespace App\Services;

use App\Notifications\SystemAlert;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    public function checkAndAlert(): void
    {
        // Check response time
        $avgResponseTime = $this->getAverageResponseTime();
        if ($avgResponseTime > 2000) {
            $this->sendAlert(
                'High Response Time Detected',
                "Average response time is {$avgResponseTime}ms",
                'warning'
            );
        }
        
        // Check error rate
        $errorRate = $this->getErrorRate();
        if ($errorRate > 5) {
            $this->sendAlert(
                'High Error Rate Detected',
                "Error rate is {$errorRate}%",
                'error'
            );
        }
        
        // Check disk space
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage > 85) {
            $this->sendAlert(
                'Low Disk Space',
                "Disk usage is {$diskUsage}%",
                'warning'
            );
        }
    }
    
    private function sendAlert(string $title, string $message, string $level): void
    {
        Notification::route('slack', config('services.slack.webhook_url'))
                   ->notify(new SystemAlert($title, $message, $level));
    }
}
```

## 8. Monitoring Dashboard Setup

### Laravel Nova Integration

If using Laravel Nova, add monitoring metrics:

```php
// In NovaServiceProvider
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\Trend;

protected function cards()
{
    return [
        new Metrics\UsersPerDay,
        new Metrics\CoursesPerDay,
        new Metrics\AverageResponseTime,
        new Metrics\ErrorRate,
    ];
}
```

### Custom Admin Dashboard

Create monitoring dashboard:

```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/monitoring', [MonitoringController::class, 'dashboard']);
    Route::get('/admin/monitoring/metrics', [MonitoringController::class, 'metrics']);
});
```

## 9. Performance Optimization Monitoring

### Database Query Monitoring

Add query monitoring:

```php
// In AppServiceProvider boot method
if (app()->environment('production')) {
    \DB::listen(function ($query) {
        if ($query->time > 100) { // Log queries > 100ms
            \Log::channel('performance')->warning('Slow query detected', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        }
    });
}
```

### Cache Performance Monitoring

```php
// Create cache monitoring middleware
class CacheMonitoring
{
    public function handle($request, Closure $next)
    {
        $cacheHits = 0;
        $cacheMisses = 0;
        
        // Monitor cache operations
        \Cache::extend('monitored', function ($app) use (&$cacheHits, &$cacheMisses) {
            return new MonitoredCacheStore(
                \Cache::store('redis'),
                $cacheHits,
                $cacheMisses
            );
        });
        
        $response = $next($request);
        
        // Log cache statistics
        \Log::channel('performance')->info('Cache statistics', [
            'hits' => $cacheHits,
            'misses' => $cacheMisses,
            'hit_ratio' => $cacheHits / ($cacheHits + $cacheMisses),
        ]);
        
        return $response;
    }
}
```

## 10. Maintenance and Troubleshooting

### Log Rotation

Configure log rotation:

```bash
# /etc/logrotate.d/laravel-brive
/var/www/laravel-brive/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx > /dev/null 2>&1 || true
    endscript
}
```

### Monitoring Health Checks

```bash
# Check monitoring services
sudo systemctl status prometheus
sudo systemctl status grafana-server
sudo docker-compose -f /opt/monitoring/docker-compose.yml ps

# Check logs
tail -f /var/log/laravel-brive-monitoring-setup.log
tail -f /var/log/laravel-brive-uptime.log

# Test health endpoints
curl -s http://localhost/health | jq .
curl -s http://localhost/ping
```

### Performance Tuning

```bash
# Optimize Laravel for monitoring
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan telescope:prune --hours=48

# Monitor system resources
top -p $(pgrep -d',' php-fpm)
iotop -a
netstat -tulpn | grep :80
```

## 11. Security Monitoring

### Failed Login Monitoring

```php
// In LoginController
protected function sendFailedLoginResponse(Request $request)
{
    \Log::channel('security')->warning('Failed login attempt', [
        'email' => $request->email,
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'timestamp' => now(),
    ]);
    
    // Check for brute force attempts
    $attempts = \Cache::get('login_attempts_' . $request->ip(), 0);
    if ($attempts > 5) {
        \Log::channel('security')->error('Potential brute force attack', [
            'ip' => $request->ip(),
            'attempts' => $attempts,
        ]);
    }
    
    \Cache::put('login_attempts_' . $request->ip(), $attempts + 1, 3600);
    
    return parent::sendFailedLoginResponse($request);
}
```

### Suspicious Activity Detection

```php
// Middleware for suspicious activity
class SuspiciousActivityDetection
{
    public function handle($request, Closure $next)
    {
        // Check for SQL injection attempts
        $suspiciousPatterns = [
            '/union.*select/i',
            '/drop.*table/i',
            '/<script/i',
            '/javascript:/i',
        ];
        
        $input = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                \Log::channel('security')->error('Suspicious activity detected', [
                    'pattern' => $pattern,
                    'input' => $input,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                
                // Optionally block the request
                return response('Forbidden', 403);
            }
        }
        
        return $next($request);
    }
}
```

## Monitoring Summary

Your Laravel-Brive monitoring system now includes:

✅ **Error Tracking with Sentry**
✅ **Performance Monitoring with Telescope**
✅ **System Monitoring with Prometheus/Grafana**
✅ **Log Monitoring with ELK Stack**
✅ **Health Check Endpoints**
✅ **Uptime Monitoring**
✅ **Real-time Alerting**
✅ **Security Monitoring**
✅ **Performance Optimization Tracking**

### Key Monitoring URLs:

- Health Check: `http://your-domain/health`
- Simple Ping: `http://your-domain/ping`
- Telescope: `http://your-domain/telescope`
- Grafana: `http://localhost:3000`
- Kibana: `http://localhost:5601`
- Prometheus: `http://localhost:9090`

### Next Steps:

1. Configure Sentry DSN in `.env`
2. Set up email/Slack notifications
3. Customize alert thresholds
4. Create custom dashboards
5. Set up automated reports
6. Configure backup monitoring
7. Test all monitoring components

Your application is now equipped with enterprise-grade monitoring and alerting capabilities!
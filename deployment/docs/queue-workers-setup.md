# Laravel-Brive Queue Workers Setup Guide

This guide provides comprehensive instructions for setting up and managing queue workers in the Laravel-Brive production environment.

## Overview

The queue system handles background job processing for:
- Email notifications
- Data import/export operations
- File processing
- System maintenance tasks
- Report generation
- Image optimization
- Cache warming

## Quick Setup

### Automated Setup

Run the automated setup script:

```bash
sudo ./deployment/scripts/setup-queue-workers.sh
```

This script will:
- Install and configure Redis
- Set up Supervisor for process management
- Configure Laravel queue settings
- Create example queue jobs
- Set up monitoring and health checks
- Create a web-based dashboard

## Manual Setup

### 1. Redis Installation and Configuration

#### Install Redis
```bash
sudo apt-get update
sudo apt-get install -y redis-server
```

#### Configure Redis for Production
```bash
sudo cp /etc/redis/redis.conf /etc/redis/redis.conf.backup
```

Edit `/etc/redis/redis.conf`:
```ini
# Security
bind 127.0.0.1
requirepass YOUR_SECURE_PASSWORD
rename-command FLUSHDB ""
rename-command FLUSHALL ""

# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Performance
tcp-keepalive 300
timeout 0
```

#### Start Redis
```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

### 2. Laravel Queue Configuration

#### Update Environment Variables
Add to `.env`:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=YOUR_SECURE_PASSWORD
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2
REDIS_SESSION_DB=3

QUEUE_FAILED_DRIVER=database-uuids
QUEUE_RETRY_AFTER=90
QUEUE_MAX_TRIES=3
```

#### Create Database Tables
```bash
php artisan queue:failed-table
php artisan queue:batches-table
php artisan migrate
```

### 3. Supervisor Configuration

#### Install Supervisor
```bash
sudo apt-get install -y supervisor
```

#### Configure Queue Workers
Create `/etc/supervisor/conf.d/laravel-brive-worker.conf`:
```ini
[program:laravel-brive-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-brive/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
directory=/var/www/laravel-brive
autostart=true
autorestart=true
startretries=3
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/laravel-brive/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
killasgroup=true
priority=999
```

#### Configure High-Priority Workers
Create `/etc/supervisor/conf.d/laravel-brive-worker-high.conf`:
```ini
[program:laravel-brive-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-brive/artisan queue:work redis --queue=high --sleep=1 --tries=3 --max-time=1800 --timeout=30
directory=/var/www/laravel-brive
autostart=true
autorestart=true
startretries=3
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravel-brive/storage/logs/worker-high.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=1800
killasgroup=true
priority=998
```

#### Configure Scheduler Worker
Create `/etc/supervisor/conf.d/laravel-brive-scheduler.conf`:
```ini
[program:laravel-brive-scheduler]
process_name=%(program_name)s
command=php /var/www/laravel-brive/artisan schedule:work
directory=/var/www/laravel-brive
autostart=true
autorestart=true
startretries=3
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laravel-brive/storage/logs/scheduler.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
killasgroup=true
priority=997
```

#### Start Workers
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-brive-worker:*
sudo supervisorctl start laravel-brive-worker-high:*
sudo supervisorctl start laravel-brive-scheduler:*
```

## Queue Usage

### Creating Jobs

#### Generate a New Job
```bash
php artisan make:job ProcessUserData
```

#### Example Job Implementation
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $maxExceptions = 2;
    public $timeout = 120;
    public $retryUntil;
    
    public function __construct(
        private int $userId,
        private array $data
    ) {
        $this->retryUntil = now()->addMinutes(10);
        $this->onQueue('default');
    }
    
    public function handle(): void
    {
        Log::info('Processing user data', ['user_id' => $this->userId]);
        
        // Your processing logic here
        
        Log::info('User data processed successfully', ['user_id' => $this->userId]);
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('User data processing failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### Dispatching Jobs

#### Immediate Dispatch
```php
ProcessUserData::dispatch($userId, $data);
```

#### Delayed Dispatch
```php
ProcessUserData::dispatch($userId, $data)->delay(now()->addMinutes(10));
```

#### Queue-Specific Dispatch
```php
ProcessUserData::dispatch($userId, $data)->onQueue('high');
```

#### Batch Processing
```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new ProcessUserData($userId1, $data1),
    new ProcessUserData($userId2, $data2),
    new ProcessUserData($userId3, $data3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, Throwable $e) {
    // First batch job failure detected
})->finally(function (Batch $batch) {
    // The batch has finished executing
})->dispatch();
```

## Queue Management

### Monitoring Commands

#### Check Queue Status
```bash
php artisan queue:status
php artisan queue:status --queue=high
```

#### View Failed Jobs
```bash
php artisan queue:failed
```

#### Retry Failed Jobs
```bash
# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry 5

# Retry jobs from specific queue
php artisan queue:retry --queue=high
```

#### Clear Failed Jobs
```bash
# Clear all failed jobs
php artisan queue:flush

# Clear specific failed job
php artisan queue:forget 5
```

#### Cleanup Old Data
```bash
php artisan queue:cleanup --days=7
```

### Supervisor Management

#### Check Worker Status
```bash
sudo supervisorctl status
```

#### Restart Workers
```bash
# Restart all Laravel workers
sudo supervisorctl restart laravel-brive-worker:*

# Restart specific worker
sudo supervisorctl restart laravel-brive-worker:laravel-brive-worker_00
```

#### Stop/Start Workers
```bash
# Stop all workers
sudo supervisorctl stop laravel-brive-worker:*

# Start all workers
sudo supervisorctl start laravel-brive-worker:*
```

#### View Worker Logs
```bash
# View worker logs
sudo supervisorctl tail laravel-brive-worker:laravel-brive-worker_00

# Follow logs
sudo supervisorctl tail -f laravel-brive-worker:laravel-brive-worker_00
```

## Queue Dashboard

### Accessing the Dashboard

The queue monitoring dashboard is available at:
```
https://your-domain.com/admin/queue-dashboard
```

### Dashboard Features

- **Real-time Queue Statistics**: View pending, delayed, and reserved jobs
- **Failed Jobs Management**: Retry or clear failed jobs
- **Worker Status**: Monitor active workers and their status
- **System Metrics**: Redis memory usage, PHP memory, load average
- **Historical Data**: Track queue performance over time

### API Endpoints

```php
// Get queue statistics
GET /admin/queue-stats

// Retry failed jobs
POST /admin/queue-retry
{
    "job_ids": ["uuid1", "uuid2"] // Optional, retry all if empty
}

// Clear failed jobs
POST /admin/queue-clear
```

## Health Monitoring

### Automated Health Checks

The system includes automated health monitoring that checks:
- Worker process count
- Queue size thresholds
- Failed job counts
- Supervisor status

### Health Check Script

Location: `/usr/local/bin/queue-health-check.sh`

Runs every 5 minutes via cron and:
- Monitors queue workers
- Checks queue sizes
- Tracks failed jobs
- Sends alerts when thresholds are exceeded

### Log Files

```bash
# Worker logs
tail -f /var/www/laravel-brive/storage/logs/worker.log
tail -f /var/www/laravel-brive/storage/logs/worker-high.log
tail -f /var/www/laravel-brive/storage/logs/scheduler.log

# Health check logs
tail -f /var/log/laravel-brive-queue-health.log

# Setup logs
tail -f /var/log/laravel-brive-queue-setup.log
```

## Performance Optimization

### Worker Configuration

#### Optimal Worker Count
```bash
# Calculate based on CPU cores
CPU_CORES=$(nproc)
WORKER_COUNT=$((CPU_CORES * 2))
```

#### Memory Management
```bash
# Restart workers after processing 1000 jobs
php artisan queue:work --max-jobs=1000

# Restart workers after 1 hour
php artisan queue:work --max-time=3600
```

### Redis Optimization

#### Memory Configuration
```ini
# /etc/redis/redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru
```

#### Connection Pooling
```php
// config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
        'persistent' => true,
        'read_write_timeout' => 60,
    ],
],
```

### Job Optimization

#### Efficient Job Design
```php
class OptimizedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    // Use specific properties instead of models to reduce serialization
    public function __construct(
        private int $userId,
        private array $data
    ) {
        // Set appropriate queue and delay
        $this->onQueue('low');
        $this->delay(now()->addSeconds(30));
    }
    
    public function handle(): void
    {
        // Implement chunking for large datasets
        collect($this->data)->chunk(100)->each(function ($chunk) {
            // Process chunk
        });
    }
    
    // Implement shouldBeEncrypted for sensitive data
    public function shouldBeEncrypted(): bool
    {
        return true;
    }
}
```

## Troubleshooting

### Common Issues

#### Workers Not Processing Jobs

1. **Check Worker Status**:
   ```bash
   sudo supervisorctl status
   ps aux | grep "queue:work"
   ```

2. **Check Redis Connection**:
   ```bash
   redis-cli -a "your_password" ping
   ```

3. **Check Laravel Configuration**:
   ```bash
   php artisan config:cache
   php artisan queue:restart
   ```

#### High Memory Usage

1. **Restart Workers Regularly**:
   ```bash
   # Add to supervisor config
   command=php artisan queue:work --max-jobs=1000 --max-time=3600
   ```

2. **Monitor Memory Usage**:
   ```bash
   ps aux --sort=-%mem | grep queue:work
   ```

#### Failed Jobs Accumulating

1. **Investigate Failures**:
   ```bash
   php artisan queue:failed
   ```

2. **Check Error Logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Fix and Retry**:
   ```bash
   php artisan queue:retry all
   ```

### Performance Issues

#### Slow Job Processing

1. **Increase Worker Count**:
   ```ini
   # In supervisor config
   numprocs=8
   ```

2. **Use Priority Queues**:
   ```php
   ImportantJob::dispatch()->onQueue('high');
   ```

3. **Optimize Job Logic**:
   - Use database transactions
   - Implement proper error handling
   - Avoid N+1 queries
   - Use chunking for large datasets

#### Redis Memory Issues

1. **Increase Redis Memory**:
   ```ini
   maxmemory 1gb
   ```

2. **Configure Eviction Policy**:
   ```ini
   maxmemory-policy allkeys-lru
   ```

3. **Monitor Redis Usage**:
   ```bash
   redis-cli -a "your_password" info memory
   ```

## Security Considerations

### Redis Security

1. **Use Strong Password**:
   ```bash
   REDIS_PASSWORD=$(openssl rand -base64 32)
   ```

2. **Bind to Localhost Only**:
   ```ini
   bind 127.0.0.1
   ```

3. **Disable Dangerous Commands**:
   ```ini
   rename-command FLUSHDB ""
   rename-command FLUSHALL ""
   rename-command CONFIG "CONFIG_secret_string"
   ```

### Job Security

1. **Encrypt Sensitive Jobs**:
   ```php
   class SensitiveJob implements ShouldQueue, ShouldBeEncrypted
   {
       // Job implementation
   }
   ```

2. **Validate Job Data**:
   ```php
   public function handle(): void
   {
       $this->validateData();
       // Process job
   }
   
   private function validateData(): void
   {
       if (!$this->isValidData()) {
           throw new InvalidArgumentException('Invalid job data');
       }
   }
   ```

3. **Limit Job Execution Time**:
   ```php
   public $timeout = 300; // 5 minutes
   public $maxExceptions = 3;
   ```

## Maintenance

### Regular Tasks

#### Daily
- Monitor queue dashboard
- Check failed jobs
- Review worker logs

#### Weekly
- Clean up old failed jobs
- Review queue performance metrics
- Update worker configurations if needed

#### Monthly
- Analyze queue usage patterns
- Optimize job implementations
- Review and update monitoring thresholds

### Backup Considerations

#### Redis Data
```bash
# Backup Redis data
cp /var/lib/redis/dump.rdb /backup/redis-$(date +%Y%m%d).rdb
```

#### Queue Configuration
```bash
# Backup supervisor configs
tar -czf /backup/supervisor-configs-$(date +%Y%m%d).tar.gz /etc/supervisor/conf.d/laravel-brive-*
```

## Conclusion

This queue system provides:
- **Scalable Background Processing**: Handle thousands of jobs efficiently
- **Reliable Job Execution**: Automatic retries and failure handling
- **Comprehensive Monitoring**: Real-time dashboards and health checks
- **Production-Ready Configuration**: Optimized for performance and reliability
- **Easy Management**: Simple commands and web interface

For additional support or customization, refer to the Laravel Queue documentation or contact the development team.
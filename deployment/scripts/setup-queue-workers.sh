#!/bin/bash

# Laravel-Brive Queue Workers Setup Script
# This script sets up production-ready queue workers with Supervisor, Redis, and monitoring

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/laravel-brive"
LOG_FILE="/var/log/laravel-brive-queue-setup.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')
QUEUE_USER="www-data"
QUEUE_WORKERS=4
REDIS_PASSWORD="$(openssl rand -base64 32)"

# Functions
log() {
    echo -e "${GREEN}[$DATE]${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[$DATE] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$DATE] ERROR:${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

info() {
    echo -e "${BLUE}[$DATE] INFO:${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root (use sudo)"
fi

# Check if Laravel app directory exists
if [[ ! -d "$APP_DIR" ]]; then
    error "Laravel application directory not found: $APP_DIR"
fi

log "Starting Laravel-Brive queue workers setup..."

# 1. Install and Configure Redis
install_redis() {
    log "Installing and configuring Redis..."
    
    # Install Redis
    apt-get update
    apt-get install -y redis-server
    
    # Backup original Redis config
    cp /etc/redis/redis.conf /etc/redis/redis.conf.backup
    
    # Configure Redis for production
    cat > /etc/redis/redis.conf << EOF
# Redis configuration for Laravel-Brive
bind 127.0.0.1
port 6379
tcp-backlog 511
timeout 0
tcp-keepalive 300

# Security
requirepass $REDIS_PASSWORD
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command EVAL ""
rename-command DEBUG ""
rename-command CONFIG "CONFIG_b840fc02d524045429941cc15f59e41cb7be6c52"

# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
syslog-enabled yes
syslog-ident redis

# Performance
databases 16
latency-monitor-threshold 100
slowlog-log-slower-than 10000
slowlog-max-len 128

# Network
tcp-keepalive 300
timeout 0
EOF
    
    # Set Redis password in Laravel .env
    if grep -q "REDIS_PASSWORD" "$APP_DIR/.env"; then
        sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASSWORD/" "$APP_DIR/.env"
    else
        echo "REDIS_PASSWORD=$REDIS_PASSWORD" >> "$APP_DIR/.env"
    fi
    
    # Start and enable Redis
    systemctl restart redis-server
    systemctl enable redis-server
    
    # Test Redis connection
    if redis-cli -a "$REDIS_PASSWORD" ping | grep -q PONG; then
        log "Redis installation and configuration completed successfully"
    else
        error "Redis configuration failed"
    fi
}

# 2. Install and Configure Supervisor
install_supervisor() {
    log "Installing and configuring Supervisor..."
    
    # Install Supervisor
    apt-get install -y supervisor
    
    # Create Supervisor configuration for Laravel queue workers
    cat > /etc/supervisor/conf.d/laravel-brive-worker.conf << EOF
[program:laravel-brive-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
directory=$APP_DIR
autostart=true
autorestart=true
startretries=3
user=$QUEUE_USER
numprocs=$QUEUE_WORKERS
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
killasgroup=true
priority=999
EOF
    
    # Create high-priority queue worker for critical jobs
    cat > /etc/supervisor/conf.d/laravel-brive-worker-high.conf << EOF
[program:laravel-brive-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work redis --queue=high --sleep=1 --tries=3 --max-time=1800 --timeout=30
directory=$APP_DIR
autostart=true
autorestart=true
startretries=3
user=$QUEUE_USER
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker-high.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=1800
killasgroup=true
priority=998
EOF
    
    # Create scheduler worker
    cat > /etc/supervisor/conf.d/laravel-brive-scheduler.conf << EOF
[program:laravel-brive-scheduler]
process_name=%(program_name)s
command=php $APP_DIR/artisan schedule:work
directory=$APP_DIR
autostart=true
autorestart=true
startretries=3
user=$QUEUE_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/scheduler.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
killasgroup=true
priority=997
EOF
    
    # Create Horizon worker (if using Laravel Horizon)
    if grep -q "horizon" "$APP_DIR/composer.json"; then
        cat > /etc/supervisor/conf.d/laravel-brive-horizon.conf << EOF
[program:laravel-brive-horizon]
process_name=%(program_name)s
command=php $APP_DIR/artisan horizon
directory=$APP_DIR
autostart=true
autorestart=true
startretries=3
user=$QUEUE_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/horizon.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
killasgroup=true
priority=996
EOF
    fi
    
    # Update Supervisor configuration
    supervisorctl reread
    supervisorctl update
    
    # Start all Laravel workers
    supervisorctl start laravel-brive-worker:*
    supervisorctl start laravel-brive-worker-high:*
    supervisorctl start laravel-brive-scheduler:*
    
    if grep -q "horizon" "$APP_DIR/composer.json"; then
        supervisorctl start laravel-brive-horizon:*
    fi
    
    log "Supervisor configuration completed"
}

# 3. Configure Laravel Queue Settings
configure_laravel_queue() {
    log "Configuring Laravel queue settings..."
    
    cd "$APP_DIR"
    
    # Update queue configuration in .env
    if grep -q "QUEUE_CONNECTION" .env; then
        sed -i 's/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/' .env
    else
        echo "QUEUE_CONNECTION=redis" >> .env
    fi
    
    # Configure Redis connection for queues
    cat >> .env << EOF

# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2
REDIS_SESSION_DB=3

# Queue Settings
QUEUE_FAILED_DRIVER=database-uuids
QUEUE_RETRY_AFTER=90
QUEUE_MAX_TRIES=3
EOF
    
    # Create queue configuration file
    cat > config/queue.php << 'EOF'
<?php

return [
    'default' => env('QUEUE_CONNECTION', 'sync'),
    
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => env('QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],
        
        'high' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'high',
            'retry_after' => 60,
            'block_for' => null,
            'after_commit' => false,
        ],
        
        'low' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'low',
            'retry_after' => 120,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],
    
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
EOF
    
    # Create failed jobs table if it doesn't exist
    if ! sudo -u www-data php artisan migrate:status | grep -q "failed_jobs"; then
        sudo -u www-data php artisan queue:failed-table
        sudo -u www-data php artisan migrate
    fi
    
    # Create batches table for job batching
    if ! sudo -u www-data php artisan migrate:status | grep -q "job_batches"; then
        sudo -u www-data php artisan queue:batches-table
        sudo -u www-data php artisan migrate
    fi
    
    log "Laravel queue configuration completed"
}

# 4. Create Queue Job Examples
create_queue_jobs() {
    log "Creating example queue jobs..."
    
    cd "$APP_DIR"
    
    # Create email notification job
    sudo -u www-data php artisan make:job SendEmailNotification
    
    cat > app/Jobs/SendEmailNotification.php << 'EOF'
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $maxExceptions = 2;
    public $timeout = 120;
    public $retryUntil;
    
    public function __construct(
        private string $email,
        private string $subject,
        private string $message,
        private array $data = []
    ) {
        $this->retryUntil = now()->addMinutes(10);
        $this->onQueue('default');
    }
    
    public function handle(): void
    {
        try {
            Log::info('Sending email notification', [
                'email' => $this->email,
                'subject' => $this->subject,
            ]);
            
            // Send email logic here
            Mail::raw($this->message, function ($mail) {
                $mail->to($this->email)
                     ->subject($this->subject);
            });
            
            Log::info('Email notification sent successfully', [
                'email' => $this->email,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            
            throw $e;
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('Email notification job failed permanently', [
            'email' => $this->email,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}
EOF
    
    # Create data processing job
    sudo -u www-data php artisan make:job ProcessDataImport
    
    cat > app/Jobs/ProcessDataImport.php << 'EOF'
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDataImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 2;
    public $maxExceptions = 1;
    public $timeout = 600; // 10 minutes
    public $retryUntil;
    
    public function __construct(
        private string $filePath,
        private int $userId,
        private array $options = []
    ) {
        $this->retryUntil = now()->addHour();
        $this->onQueue('low'); // Use low priority queue for heavy processing
    }
    
    public function handle(): void
    {
        try {
            Log::info('Starting data import processing', [
                'file' => $this->filePath,
                'user_id' => $this->userId,
            ]);
            
            if (!Storage::exists($this->filePath)) {
                throw new \Exception("Import file not found: {$this->filePath}");
            }
            
            $data = Storage::get($this->filePath);
            $rows = array_map('str_getcsv', explode("\n", $data));
            
            $processed = 0;
            $errors = 0;
            
            foreach ($rows as $index => $row) {
                try {
                    // Process each row
                    $this->processRow($row, $index);
                    $processed++;
                    
                    // Update progress every 100 rows
                    if ($processed % 100 === 0) {
                        Log::info("Import progress: {$processed} rows processed");
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    Log::warning('Error processing row', [
                        'row' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Data import completed', [
                'file' => $this->filePath,
                'processed' => $processed,
                'errors' => $errors,
            ]);
            
            // Clean up temporary file
            Storage::delete($this->filePath);
            
        } catch (\Exception $e) {
            Log::error('Data import failed', [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            
            throw $e;
        }
    }
    
    private function processRow(array $row, int $index): void
    {
        // Implement your row processing logic here
        // This is just an example
        if (empty($row) || count($row) < 3) {
            throw new \Exception("Invalid row data at index {$index}");
        }
        
        // Simulate processing time
        usleep(10000); // 10ms
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('Data import job failed permanently', [
            'file' => $this->filePath,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
        
        // Notify user of failure
        SendEmailNotification::dispatch(
            'admin@laravel-brive.com',
            'Data Import Failed',
            "Data import failed for file: {$this->filePath}. Error: {$exception->getMessage()}"
        )->onQueue('high');
    }
}
EOF
    
    # Create system maintenance job
    sudo -u www-data php artisan make:job SystemMaintenance
    
    cat > app/Jobs/SystemMaintenance.php << 'EOF'
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SystemMaintenance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 1;
    public $timeout = 1800; // 30 minutes
    
    public function __construct(
        private array $tasks = ['cache', 'logs', 'temp']
    ) {
        $this->onQueue('low');
    }
    
    public function handle(): void
    {
        Log::info('Starting system maintenance', [
            'tasks' => $this->tasks,
        ]);
        
        foreach ($this->tasks as $task) {
            try {
                $this->runMaintenanceTask($task);
                Log::info("Maintenance task completed: {$task}");
            } catch (\Exception $e) {
                Log::error("Maintenance task failed: {$task}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info('System maintenance completed');
    }
    
    private function runMaintenanceTask(string $task): void
    {
        switch ($task) {
            case 'cache':
                Artisan::call('cache:clear');
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                break;
                
            case 'logs':
                // Clean old log files
                $logFiles = Storage::disk('local')->files('logs');
                foreach ($logFiles as $file) {
                    if (Storage::disk('local')->lastModified($file) < now()->subDays(30)->timestamp) {
                        Storage::disk('local')->delete($file);
                    }
                }
                break;
                
            case 'temp':
                // Clean temporary files
                $tempFiles = Storage::disk('local')->files('temp');
                foreach ($tempFiles as $file) {
                    if (Storage::disk('local')->lastModified($file) < now()->subHours(24)->timestamp) {
                        Storage::disk('local')->delete($file);
                    }
                }
                break;
                
            case 'failed_jobs':
                // Clean old failed jobs
                Artisan::call('queue:prune-failed', ['--hours' => 168]); // 7 days
                break;
                
            default:
                throw new \Exception("Unknown maintenance task: {$task}");
        }
    }
}
EOF
    
    log "Queue job examples created"
}

# 5. Create Queue Monitoring Commands
create_monitoring_commands() {
    log "Creating queue monitoring commands..."
    
    cd "$APP_DIR"
    
    # Create queue status command
    sudo -u www-data php artisan make:command QueueStatus
    
    cat > app/Console/Commands/QueueStatus.php << 'EOF'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class QueueStatus extends Command
{
    protected $signature = 'queue:status {--queue=default}';
    protected $description = 'Display queue status and statistics';
    
    public function handle()
    {
        $queue = $this->option('queue');
        
        $this->info("Queue Status Report - " . now()->format('Y-m-d H:i:s'));
        $this->line(str_repeat('=', 50));
        
        // Redis queue information
        try {
            $redis = Redis::connection();
            $queueLength = $redis->llen("queues:{$queue}");
            $delayedJobs = $redis->zcard("queues:{$queue}:delayed");
            $reservedJobs = $redis->zcard("queues:{$queue}:reserved");
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Queue Name', $queue],
                    ['Pending Jobs', $queueLength],
                    ['Delayed Jobs', $delayedJobs],
                    ['Reserved Jobs', $reservedJobs],
                ]
            );
            
        } catch (\Exception $e) {
            $this->error("Failed to connect to Redis: " . $e->getMessage());
        }
        
        // Failed jobs information
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $recentFailures = DB::table('failed_jobs')
                ->where('failed_at', '>', now()->subHour())
                ->count();
            
            $this->line('');
            $this->table(
                ['Failed Jobs Metric', 'Value'],
                [
                    ['Total Failed Jobs', $failedJobs],
                    ['Failed in Last Hour', $recentFailures],
                ]
            );
            
        } catch (\Exception $e) {
            $this->error("Failed to query failed jobs: " . $e->getMessage());
        }
        
        // Worker process information
        $this->line('');
        $this->info('Active Queue Workers:');
        
        $workers = shell_exec('ps aux | grep "queue:work" | grep -v grep');
        if ($workers) {
            $this->line($workers);
        } else {
            $this->warn('No active queue workers found!');
        }
        
        return 0;
    }
}
EOF
    
    # Create queue cleanup command
    sudo -u www-data php artisan make:command QueueCleanup
    
    cat > app/Console/Commands/QueueCleanup.php << 'EOF'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class QueueCleanup extends Command
{
    protected $signature = 'queue:cleanup {--days=7 : Number of days to keep failed jobs}';
    protected $description = 'Clean up old failed jobs and queue data';
    
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Cleaning up queue data older than {$days} days...");
        
        // Clean failed jobs
        $deletedFailedJobs = DB::table('failed_jobs')
            ->where('failed_at', '<', $cutoffDate)
            ->delete();
        
        $this->info("Deleted {$deletedFailedJobs} old failed jobs");
        
        // Clean completed job batches
        if (DB::getSchemaBuilder()->hasTable('job_batches')) {
            $deletedBatches = DB::table('job_batches')
                ->where('created_at', '<', $cutoffDate)
                ->where('finished_at', '!=', null)
                ->delete();
            
            $this->info("Deleted {$deletedBatches} old job batches");
        }
        
        // Clear Redis queue statistics (optional)
        if ($this->confirm('Clear Redis queue statistics?', false)) {
            try {
                $redis = Redis::connection();
                $keys = $redis->keys('queues:*:stats:*');
                if (!empty($keys)) {
                    $redis->del($keys);
                    $this->info('Cleared Redis queue statistics');
                }
            } catch (\Exception $e) {
                $this->error('Failed to clear Redis statistics: ' . $e->getMessage());
            }
        }
        
        $this->info('Queue cleanup completed!');
        
        return 0;
    }
}
EOF
    
    log "Queue monitoring commands created"
}

# 6. Setup Queue Monitoring Dashboard
setup_queue_dashboard() {
    log "Setting up queue monitoring dashboard..."
    
    cd "$APP_DIR"
    
    # Create queue dashboard controller
    sudo -u www-data php artisan make:controller QueueDashboardController
    
    cat > app/Http/Controllers/QueueDashboardController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QueueDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/QueueDashboard', [
            'stats' => $this->getQueueStats(),
        ]);
    }
    
    public function stats(): JsonResponse
    {
        return response()->json($this->getQueueStats());
    }
    
    public function retryFailed(Request $request): JsonResponse
    {
        $request->validate([
            'job_ids' => 'array',
            'job_ids.*' => 'string',
        ]);
        
        $jobIds = $request->input('job_ids', []);
        
        if (empty($jobIds)) {
            // Retry all failed jobs
            \Artisan::call('queue:retry', ['id' => 'all']);
            $message = 'All failed jobs have been queued for retry';
        } else {
            // Retry specific jobs
            foreach ($jobIds as $jobId) {
                \Artisan::call('queue:retry', ['id' => $jobId]);
            }
            $message = count($jobIds) . ' failed jobs have been queued for retry';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
    
    public function clearFailed(): JsonResponse
    {
        \Artisan::call('queue:flush');
        
        return response()->json([
            'success' => true,
            'message' => 'All failed jobs have been cleared',
        ]);
    }
    
    private function getQueueStats(): array
    {
        $stats = [
            'queues' => [],
            'failed_jobs' => [],
            'workers' => [],
            'system' => [],
        ];
        
        // Get queue information
        try {
            $redis = Redis::connection();
            $queues = ['default', 'high', 'low'];
            
            foreach ($queues as $queue) {
                $stats['queues'][$queue] = [
                    'pending' => $redis->llen("queues:{$queue}"),
                    'delayed' => $redis->zcard("queues:{$queue}:delayed"),
                    'reserved' => $redis->zcard("queues:{$queue}:reserved"),
                ];
            }
        } catch (\Exception $e) {
            $stats['queues']['error'] = $e->getMessage();
        }
        
        // Get failed jobs
        try {
            $failedJobs = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->uuid ?? $job->id,
                        'queue' => $job->queue,
                        'payload' => json_decode($job->payload, true),
                        'exception' => $job->exception,
                        'failed_at' => $job->failed_at,
                    ];
                });
            
            $stats['failed_jobs'] = [
                'total' => DB::table('failed_jobs')->count(),
                'recent' => DB::table('failed_jobs')
                    ->where('failed_at', '>', now()->subHour())
                    ->count(),
                'jobs' => $failedJobs,
            ];
        } catch (\Exception $e) {
            $stats['failed_jobs']['error'] = $e->getMessage();
        }
        
        // Get worker information
        $workers = shell_exec('ps aux | grep "queue:work" | grep -v grep | wc -l');
        $stats['workers'] = [
            'active' => (int) trim($workers),
            'supervisor_status' => $this->getSupervisorStatus(),
        ];
        
        // System information
        $stats['system'] = [
            'redis_memory' => $this->getRedisMemoryUsage(),
            'php_memory' => memory_get_usage(true),
            'load_average' => sys_getloadavg(),
        ];
        
        return $stats;
    }
    
    private function getSupervisorStatus(): array
    {
        $output = shell_exec('supervisorctl status | grep laravel-brive');
        $status = [];
        
        if ($output) {
            $lines = explode("\n", trim($output));
            foreach ($lines as $line) {
                if (preg_match('/^([^\s]+)\s+([^\s]+)/', $line, $matches)) {
                    $status[$matches[1]] = $matches[2];
                }
            }
        }
        
        return $status;
    }
    
    private function getRedisMemoryUsage(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info('memory');
            
            return [
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'used_memory_peak_human' => $info['used_memory_peak_human'] ?? '0B',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
EOF
    
    # Add routes for queue dashboard
    if ! grep -q "queue-dashboard" routes/web.php; then
        echo "" >> routes/web.php
        echo "// Queue Dashboard routes" >> routes/web.php
        echo "Route::middleware(['auth', 'admin'])->group(function () {" >> routes/web.php
        echo "    Route::get('/admin/queue-dashboard', [App\\Http\\Controllers\\QueueDashboardController::class, 'index'])->name('queue.dashboard');" >> routes/web.php
        echo "    Route::get('/admin/queue-stats', [App\\Http\\Controllers\\QueueDashboardController::class, 'stats'])->name('queue.stats');" >> routes/web.php
        echo "    Route::post('/admin/queue-retry', [App\\Http\\Controllers\\QueueDashboardController::class, 'retryFailed'])->name('queue.retry');" >> routes/web.php
        echo "    Route::post('/admin/queue-clear', [App\\Http\\Controllers\\QueueDashboardController::class, 'clearFailed'])->name('queue.clear');" >> routes/web.php
        echo "});" >> routes/web.php
    fi
    
    log "Queue monitoring dashboard setup completed"
}

# 7. Create Queue Health Check Script
create_health_check() {
    log "Creating queue health check script..."
    
    cat > /usr/local/bin/queue-health-check.sh << 'EOF'
#!/bin/bash

# Laravel-Brive Queue Health Check
APP_DIR="/var/www/laravel-brive"
LOG_FILE="/var/log/laravel-brive-queue-health.log"
ALERT_EMAIL="admin@laravel-brive.com"
MAX_QUEUE_SIZE=1000
MAX_FAILED_JOBS=50

# Function to send alert
send_alert() {
    local message="$1"
    echo "$(date): $message" >> "$LOG_FILE"
    
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "Laravel-Brive Queue Alert" "$ALERT_EMAIL"
    fi
}

# Check queue workers
worker_count=$(ps aux | grep "queue:work" | grep -v grep | wc -l)
if [[ $worker_count -eq 0 ]]; then
    send_alert "CRITICAL: No queue workers are running!"
fi

# Check queue size
queue_size=$(redis-cli -a "$(grep REDIS_PASSWORD $APP_DIR/.env | cut -d'=' -f2)" llen queues:default 2>/dev/null || echo "0")
if [[ $queue_size -gt $MAX_QUEUE_SIZE ]]; then
    send_alert "WARNING: Queue size is high: $queue_size jobs pending"
fi

# Check failed jobs
failed_jobs=$(mysql -u "$(grep DB_USERNAME $APP_DIR/.env | cut -d'=' -f2)" -p"$(grep DB_PASSWORD $APP_DIR/.env | cut -d'=' -f2)" "$(grep DB_DATABASE $APP_DIR/.env | cut -d'=' -f2)" -e "SELECT COUNT(*) FROM failed_jobs" -s -N 2>/dev/null || echo "0")
if [[ $failed_jobs -gt $MAX_FAILED_JOBS ]]; then
    send_alert "WARNING: High number of failed jobs: $failed_jobs"
fi

# Check Supervisor status
supervisor_status=$(supervisorctl status | grep laravel-brive | grep -v RUNNING | wc -l)
if [[ $supervisor_status -gt 0 ]]; then
    send_alert "WARNING: Some queue workers are not running properly"
fi

# Log healthy status
echo "$(date): Queue health check passed - Workers: $worker_count, Queue: $queue_size, Failed: $failed_jobs" >> "$LOG_FILE"
EOF
    
    chmod +x /usr/local/bin/queue-health-check.sh
    
    # Add to crontab (check every 5 minutes)
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/queue-health-check.sh") | crontab -
    
    log "Queue health check script created and scheduled"
}

# Main execution
log "=== Laravel-Brive Queue Workers Setup Started ==="

# Check for required tools
if ! command -v redis-cli &> /dev/null; then
    install_redis
fi

if ! command -v supervisorctl &> /dev/null; then
    install_supervisor
fi

# Setup queue system
configure_laravel_queue
create_queue_jobs
create_monitoring_commands
setup_queue_dashboard
create_health_check

# Final steps
log "Clearing Laravel caches..."
cd "$APP_DIR"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Test queue system
log "Testing queue system..."
sudo -u www-data php artisan queue:work --once --timeout=10 &
sleep 2

log "=== Queue Workers Setup Completed ==="
log "Next steps:"
log "1. Configure Redis password in .env file"
log "2. Test queue jobs: php artisan tinker -> dispatch(new App\\Jobs\\SendEmailNotification('test@example.com', 'Test', 'Test message'))"
log "3. Monitor queues: php artisan queue:status"
log "4. Access queue dashboard at /admin/queue-dashboard"
log "5. Check Supervisor status: supervisorctl status"
log "6. Monitor queue health logs: tail -f /var/log/laravel-brive-queue-health.log"

log "Queue workers setup completed successfully!"
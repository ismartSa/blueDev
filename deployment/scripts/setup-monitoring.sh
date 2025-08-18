#!/bin/bash

# Laravel-Brive Performance Monitoring Setup Script
# This script sets up comprehensive monitoring including Sentry, performance tracking, and system monitoring

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/laravel-brive"
LOG_FILE="/var/log/laravel-brive-monitoring-setup.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

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

log "Starting Laravel-Brive monitoring setup..."

# 1. Install Sentry for Error Tracking
install_sentry() {
    log "Installing Sentry for error tracking..."
    
    cd "$APP_DIR"
    
    # Install Sentry SDK
    sudo -u www-data composer require sentry/sentry-laravel
    
    # Publish Sentry config
    sudo -u www-data php artisan vendor:publish --provider="Sentry\\Laravel\\ServiceProvider"
    
    # Add Sentry to exception handler if not already added
    if ! grep -q "Sentry" app/Exceptions/Handler.php; then
        log "Configuring Sentry exception handler..."
        
        # Backup original handler
        cp app/Exceptions/Handler.php app/Exceptions/Handler.php.backup
        
        # Add Sentry integration
        cat > app/Exceptions/Handler.php << 'EOF'
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Sentry\Laravel\Integration;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }
}
EOF
    fi
    
    log "Sentry installation completed. Please configure SENTRY_LARAVEL_DSN in your .env file"
}

# 2. Install and Configure New Relic (optional)
install_newrelic() {
    log "Installing New Relic monitoring..."
    
    # Add New Relic repository
    curl -s https://download.newrelic.com/548C16BF.gpg | apt-key add -
    echo "deb http://apt.newrelic.com/debian/ newrelic non-free" > /etc/apt/sources.list.d/newrelic.list
    
    # Update package list
    apt-get update
    
    # Install New Relic PHP agent
    apt-get install -y newrelic-php5
    
    # Configure New Relic
    newrelic-install install
    
    # Restart web server
    systemctl restart nginx
    systemctl restart php8.1-fpm
    
    log "New Relic installation completed. Please configure with your license key"
}

# 3. Setup Application Performance Monitoring
setup_apm() {
    log "Setting up Application Performance Monitoring..."
    
    cd "$APP_DIR"
    
    # Install Laravel Debugbar for development insights
    sudo -u www-data composer require barryvdh/laravel-debugbar --dev
    
    # Install Laravel Telescope for application insights
    if ! grep -q "telescope" composer.json; then
        sudo -u www-data composer require laravel/telescope
        sudo -u www-data php artisan telescope:install
        sudo -u www-data php artisan migrate
    fi
    
    # Create performance monitoring middleware
    sudo -u www-data php artisan make:middleware PerformanceMonitoring
    
    cat > app/Http/Middleware/PerformanceMonitoring.php << 'EOF'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        $memoryUsage = round(($endMemory - $startMemory) / 1024 / 1024, 2); // Convert to MB
        
        // Log slow requests (> 1 second)
        if ($executionTime > 1000) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_usage' => $memoryUsage . 'MB',
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }
        
        // Add performance headers
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $memoryUsage . 'MB');
        
        return $response;
    }
}
EOF
    
    # Register middleware in Kernel.php
    if ! grep -q "PerformanceMonitoring" app/Http/Kernel.php; then
        sed -i "/protected \$middleware = \[/a\        \\App\\Http\\Middleware\\PerformanceMonitoring::class," app/Http/Kernel.php
    fi
    
    log "Application Performance Monitoring setup completed"
}

# 4. Setup System Monitoring with Prometheus and Grafana
setup_system_monitoring() {
    log "Setting up system monitoring with Prometheus and Grafana..."
    
    # Install Docker if not present
    if ! command -v docker &> /dev/null; then
        log "Installing Docker..."
        curl -fsSL https://get.docker.com -o get-docker.sh
        sh get-docker.sh
        usermod -aG docker www-data
    fi
    
    # Install Docker Compose if not present
    if ! command -v docker-compose &> /dev/null; then
        log "Installing Docker Compose..."
        curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
    fi
    
    # Create monitoring directory
    mkdir -p /opt/monitoring
    cd /opt/monitoring
    
    # Create docker-compose.yml for monitoring stack
    cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/etc/prometheus/console_libraries'
      - '--web.console.templates=/etc/prometheus/consoles'
      - '--storage.tsdb.retention.time=200h'
      - '--web.enable-lifecycle'
    restart: unless-stopped

  grafana:
    image: grafana/grafana:latest
    container_name: grafana
    ports:
      - "3000:3000"
    volumes:
      - grafana_data:/var/lib/grafana
      - ./grafana/provisioning:/etc/grafana/provisioning
    environment:
      - GF_SECURITY_ADMIN_USER=admin
      - GF_SECURITY_ADMIN_PASSWORD=laravel-brive-admin
      - GF_USERS_ALLOW_SIGN_UP=false
    restart: unless-stopped

  node_exporter:
    image: prom/node-exporter:latest
    container_name: node_exporter
    ports:
      - "9100:9100"
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.rootfs=/rootfs'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'
    restart: unless-stopped

  nginx_exporter:
    image: nginx/nginx-prometheus-exporter:latest
    container_name: nginx_exporter
    ports:
      - "9113:9113"
    command:
      - '-nginx.scrape-uri=http://host.docker.internal/nginx_status'
    restart: unless-stopped

volumes:
  prometheus_data:
  grafana_data:
EOF
    
    # Create Prometheus configuration
    cat > prometheus.yml << 'EOF'
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  # - "first_rules.yml"
  # - "second_rules.yml"

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'node'
    static_configs:
      - targets: ['node_exporter:9100']

  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx_exporter:9113']

  - job_name: 'laravel-brive'
    static_configs:
      - targets: ['host.docker.internal:80']
    metrics_path: '/metrics'
    scrape_interval: 30s
EOF
    
    # Create Grafana provisioning directories
    mkdir -p grafana/provisioning/{dashboards,datasources}
    
    # Create Grafana datasource configuration
    cat > grafana/provisioning/datasources/prometheus.yml << 'EOF'
apiVersion: 1

datasources:
  - name: Prometheus
    type: prometheus
    access: proxy
    url: http://prometheus:9090
    isDefault: true
EOF
    
    # Create Grafana dashboard configuration
    cat > grafana/provisioning/dashboards/dashboard.yml << 'EOF'
apiVersion: 1

providers:
  - name: 'default'
    orgId: 1
    folder: ''
    type: file
    disableDeletion: false
    updateIntervalSeconds: 10
    allowUiUpdates: true
    options:
      path: /etc/grafana/provisioning/dashboards
EOF
    
    # Start monitoring stack
    docker-compose up -d
    
    log "System monitoring setup completed. Grafana available at http://localhost:3000 (admin/laravel-brive-admin)"
}

# 5. Setup Log Monitoring with ELK Stack (Elasticsearch, Logstash, Kibana)
setup_log_monitoring() {
    log "Setting up log monitoring with ELK stack..."
    
    # Create ELK directory
    mkdir -p /opt/elk
    cd /opt/elk
    
    # Create docker-compose.yml for ELK stack
    cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.8.0
    container_name: elasticsearch
    environment:
      - discovery.type=single-node
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
      - xpack.security.enabled=false
    ports:
      - "9200:9200"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data
    restart: unless-stopped

  logstash:
    image: docker.elastic.co/logstash/logstash:8.8.0
    container_name: logstash
    volumes:
      - ./logstash/config:/usr/share/logstash/pipeline
      - /var/log:/var/log:ro
    ports:
      - "5044:5044"
    environment:
      - "LS_JAVA_OPTS=-Xmx256m -Xms256m"
    depends_on:
      - elasticsearch
    restart: unless-stopped

  kibana:
    image: docker.elastic.co/kibana/kibana:8.8.0
    container_name: kibana
    ports:
      - "5601:5601"
    environment:
      - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
    depends_on:
      - elasticsearch
    restart: unless-stopped

  filebeat:
    image: docker.elastic.co/beats/filebeat:8.8.0
    container_name: filebeat
    user: root
    volumes:
      - ./filebeat/filebeat.yml:/usr/share/filebeat/filebeat.yml:ro
      - /var/log:/var/log:ro
      - /var/lib/docker/containers:/var/lib/docker/containers:ro
      - /var/run/docker.sock:/var/run/docker.sock:ro
    depends_on:
      - elasticsearch
    restart: unless-stopped

volumes:
  elasticsearch_data:
EOF
    
    # Create Logstash configuration
    mkdir -p logstash/config
    cat > logstash/config/logstash.conf << 'EOF'
input {
  beats {
    port => 5044
  }
}

filter {
  if [fields][log_type] == "laravel" {
    grok {
      match => { "message" => "\[%{TIMESTAMP_ISO8601:timestamp}\] %{DATA:environment}\.%{DATA:level}: %{GREEDYDATA:message}" }
    }
    
    date {
      match => [ "timestamp", "yyyy-MM-dd HH:mm:ss" ]
    }
  }
  
  if [fields][log_type] == "nginx" {
    grok {
      match => { "message" => "%{NGINXACCESS}" }
    }
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "laravel-brive-%{+YYYY.MM.dd}"
  }
}
EOF
    
    # Create Filebeat configuration
    mkdir -p filebeat
    cat > filebeat/filebeat.yml << 'EOF'
filebeat.inputs:
- type: log
  enabled: true
  paths:
    - /var/log/laravel-brive/*.log
  fields:
    log_type: laravel
  fields_under_root: true
  multiline.pattern: '^\['
  multiline.negate: true
  multiline.match: after

- type: log
  enabled: true
  paths:
    - /var/log/nginx/access.log
  fields:
    log_type: nginx
  fields_under_root: true

- type: log
  enabled: true
  paths:
    - /var/log/nginx/error.log
  fields:
    log_type: nginx_error
  fields_under_root: true

output.logstash:
  hosts: ["logstash:5044"]

processors:
  - add_host_metadata:
      when.not.contains.tags: forwarded
EOF
    
    # Start ELK stack
    docker-compose up -d
    
    log "Log monitoring setup completed. Kibana available at http://localhost:5601"
}

# 6. Setup Health Check Monitoring
setup_health_checks() {
    log "Setting up health check monitoring..."
    
    cd "$APP_DIR"
    
    # Create health check controller
    sudo -u www-data php artisan make:controller HealthCheckController
    
    cat > app/Http/Controllers/HealthCheckController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Perform comprehensive health check
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'memory' => $this->checkMemory(),
            'disk' => $this->checkDisk(),
        ];
        
        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        return response()->json([
            'status' => $overall ? 'ok' : 'error',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ], $overall ? 200 : 503);
    }
    
    /**
     * Simple health check endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'message' => 'Laravel-Brive is running',
        ]);
    }
    
    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $count = DB::table('users')->count();
            
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'details' => ['user_count' => $count],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check cache functionality
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';
            
            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            if ($retrieved === $value) {
                return [
                    'status' => 'ok',
                    'message' => 'Cache is working properly',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Cache read/write failed',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check storage accessibility
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $content = 'Health check test';
            
            Storage::put($testFile, $content);
            $retrieved = Storage::get($testFile);
            Storage::delete($testFile);
            
            if ($retrieved === $content) {
                return [
                    'status' => 'ok',
                    'message' => 'Storage is accessible',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Storage read/write failed',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check queue status
     */
    private function checkQueue(): array
    {
        try {
            // Check if queue workers are running
            $output = shell_exec('ps aux | grep "queue:work" | grep -v grep | wc -l');
            $workerCount = (int) trim($output);
            
            return [
                'status' => $workerCount > 0 ? 'ok' : 'warning',
                'message' => $workerCount > 0 ? 'Queue workers are running' : 'No queue workers detected',
                'details' => ['worker_count' => $workerCount],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        $usagePercentage = ($memoryUsage / $memoryLimitBytes) * 100;
        
        return [
            'status' => $usagePercentage < 80 ? 'ok' : ($usagePercentage < 90 ? 'warning' : 'error'),
            'message' => sprintf('Memory usage: %.2f%%', $usagePercentage),
            'details' => [
                'used' => $this->formatBytes($memoryUsage),
                'limit' => $memoryLimit,
                'percentage' => round($usagePercentage, 2),
            ],
        ];
    }
    
    /**
     * Check disk space
     */
    private function checkDisk(): array
    {
        $path = storage_path();
        $totalBytes = disk_total_space($path);
        $freeBytes = disk_free_space($path);
        $usedBytes = $totalBytes - $freeBytes;
        $usagePercentage = ($usedBytes / $totalBytes) * 100;
        
        return [
            'status' => $usagePercentage < 80 ? 'ok' : ($usagePercentage < 90 ? 'warning' : 'error'),
            'message' => sprintf('Disk usage: %.2f%%', $usagePercentage),
            'details' => [
                'used' => $this->formatBytes($usedBytes),
                'free' => $this->formatBytes($freeBytes),
                'total' => $this->formatBytes($totalBytes),
                'percentage' => round($usagePercentage, 2),
            ],
        ];
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
EOF
    
    # Add health check routes
    if ! grep -q "health" routes/web.php; then
        echo "" >> routes/web.php
        echo "// Health check routes" >> routes/web.php
        echo "Route::get('/health', [App\\Http\\Controllers\\HealthCheckController::class, 'check']);" >> routes/web.php
        echo "Route::get('/ping', [App\\Http\\Controllers\\HealthCheckController::class, 'ping']);" >> routes/web.php
    fi
    
    log "Health check monitoring setup completed"
}

# 7. Setup Uptime Monitoring Script
setup_uptime_monitoring() {
    log "Setting up uptime monitoring..."
    
    cat > /usr/local/bin/uptime-monitor.sh << 'EOF'
#!/bin/bash

# Laravel-Brive Uptime Monitor
APP_URL="http://localhost/ping"
LOG_FILE="/var/log/laravel-brive-uptime.log"
ALERT_EMAIL="admin@laravel-brive.com"
MAX_RESPONSE_TIME=5000  # 5 seconds in milliseconds

# Function to send alert
send_alert() {
    local message="$1"
    echo "$(date): $message" >> "$LOG_FILE"
    
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "Laravel-Brive Alert" "$ALERT_EMAIL"
    fi
}

# Check application health
response=$(curl -s -w "%{http_code}:%{time_total}" -o /dev/null "$APP_URL" --max-time 10)
http_code=$(echo "$response" | cut -d: -f1)
response_time=$(echo "$response" | cut -d: -f2)
response_time_ms=$(echo "$response_time * 1000" | bc -l | cut -d. -f1)

if [[ "$http_code" != "200" ]]; then
    send_alert "ALERT: Laravel-Brive is down! HTTP Status: $http_code"
elif [[ "$response_time_ms" -gt "$MAX_RESPONSE_TIME" ]]; then
    send_alert "WARNING: Laravel-Brive is slow! Response time: ${response_time_ms}ms"
else
    echo "$(date): OK - HTTP $http_code, ${response_time_ms}ms" >> "$LOG_FILE"
fi
EOF
    
    chmod +x /usr/local/bin/uptime-monitor.sh
    
    # Add to crontab (check every 5 minutes)
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/uptime-monitor.sh") | crontab -
    
    log "Uptime monitoring setup completed"
}

# Main execution
log "=== Laravel-Brive Monitoring Setup Started ==="

# Check for required tools
if ! command -v composer &> /dev/null; then
    error "Composer is required but not installed"
fi

if ! command -v php &> /dev/null; then
    error "PHP is required but not installed"
fi

# Install monitoring components
install_sentry
setup_apm
setup_health_checks
setup_uptime_monitoring

# Optional components (ask user)
read -p "Install New Relic? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    install_newrelic
fi

read -p "Install system monitoring (Prometheus/Grafana)? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    setup_system_monitoring
fi

read -p "Install log monitoring (ELK stack)? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    setup_log_monitoring
fi

# Final steps
log "Clearing Laravel caches..."
cd "$APP_DIR"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

log "=== Monitoring Setup Completed ==="
log "Next steps:"
log "1. Configure SENTRY_LARAVEL_DSN in your .env file"
log "2. Set up email notifications for alerts"
log "3. Configure New Relic license key (if installed)"
log "4. Access Grafana at http://localhost:3000 (if installed)"
log "5. Access Kibana at http://localhost:5601 (if installed)"
log "6. Test health checks at http://your-domain/health"
log "7. Monitor uptime logs at /var/log/laravel-brive-uptime.log"

log "Monitoring setup completed successfully!"
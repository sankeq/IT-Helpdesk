<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Run diagnostics across critical subsystems and return JSON health report.
     * HTTP 200 = healthy, HTTP 503 = one or more subsystems degraded/unhealthy.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn (array $check) => $check['status'] === 'up');

        $payload = [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'app_name' => config('app.name', 'IKI IT Helpdesk'),
            'environment' => config('app.env', 'production'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp_utc' => now()->toISOString(),
            'timestamp_wita' => now()->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s T'),
            'checks' => $checks,
        ];

        return response()->json($payload, $allHealthy ? 200 : 503);
    }

    /**
     * Check Database connectivity and round-trip query execution.
     *
     * @return array{status: string, latency_ms?: float, error?: string}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'latency_ms' => $latency,
                'connection' => config('database.default'),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Cache read, write, and deletion.
     *
     * @return array{status: string, latency_ms?: float, driver?: string, error?: string}
     */
    private function checkCache(): array
    {
        $start = microtime(true);
        $key = 'health_check_probe_'.uniqid();

        try {
            Cache::put($key, 'probe_ok', 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved !== 'probe_ok') {
                return [
                    'status' => 'down',
                    'error' => 'Cache read-back verification failed',
                    'driver' => config('cache.default'),
                ];
            }

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'latency_ms' => $latency,
                'driver' => config('cache.default'),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check Private Storage disk write, read, and deletion.
     *
     * @return array{status: string, latency_ms?: float, disk?: string, error?: string}
     */
    private function checkStorage(): array
    {
        $start = microtime(true);
        $disk = config('filesystems.default', 'local');
        $testFile = 'health_checks/probe_'.uniqid().'.tmp';

        try {
            Storage::disk($disk)->put($testFile, 'probe_content');
            $content = Storage::disk($disk)->get($testFile);
            Storage::disk($disk)->delete($testFile);

            if ($content !== 'probe_content') {
                return [
                    'status' => 'down',
                    'error' => 'Disk storage read-back verification failed',
                    'disk' => $disk,
                ];
            }

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'latency_ms' => $latency,
                'disk' => $disk,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'down',
                'error' => $e->getMessage(),
                'disk' => $disk,
            ];
        }
    }
}

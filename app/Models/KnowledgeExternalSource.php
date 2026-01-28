<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KnowledgeExternalSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'name_en',
        'description',
        'base_url',
        'api_endpoint',
        'api_config',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($source) {
            if (empty($source->uuid)) {
                $source->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->name_en) {
            return $this->name_en;
        }
        return $this->name;
    }

    // ─────────────────────────────────────────────────────────────
    // API METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Fetch data from the external API
     */
    public function fetch(array $params = []): ?array
    {
        if (!$this->is_active || !$this->api_endpoint) {
            return null;
        }

        try {
            $config = $this->api_config ?? [];
            $headers = $config['headers'] ?? [];
            $authType = $config['auth_type'] ?? null;
            
            $request = Http::withHeaders($headers);
            
            // Handle authentication
            if ($authType === 'bearer' && isset($config['token'])) {
                $request = $request->withToken($config['token']);
            } elseif ($authType === 'basic' && isset($config['username'], $config['password'])) {
                $request = $request->withBasicAuth($config['username'], $config['password']);
            }
            
            $url = rtrim($this->base_url, '/') . '/' . ltrim($this->api_endpoint, '/');
            
            $response = $request->get($url, $params);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('External source fetch failed', [
                'source' => $this->name,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Search the external source
     */
    public function search(string $query): ?array
    {
        $config = $this->api_config ?? [];
        $searchParam = $config['search_param'] ?? 'q';
        
        return $this->fetch([$searchParam => $query]);
    }

    /**
     * Test the connection to the external source
     */
    public function testConnection(): array
    {
        if (!$this->api_endpoint) {
            return [
                'success' => false,
                'message' => 'No API endpoint configured',
            ];
        }

        try {
            $startTime = microtime(true);
            $result = $this->fetch();
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => $result !== null,
                'response_time_ms' => $responseTime,
                'message' => $result !== null ? 'Connection successful' : 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

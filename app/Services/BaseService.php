<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base service class implementing DRY principle
 * Provides common CRUD operations and caching functionality
 */
abstract class BaseService
{
    protected Model $model;
    protected string $cachePrefix;
    protected int $cacheTtl = 3600; // 1 hour default
    protected array $relationships = [];
    protected array $searchFields = [];
    protected array $sortFields = [];

    public function __construct()
    {
        $this->model = $this->getModelInstance();
        $this->cachePrefix = strtolower(class_basename($this->model));
    }

    /**
     * Get model instance - must be implemented by child classes
     */
    abstract protected function getModelInstance(): Model;

    /**
     * Get all records with optional relationships
     */
    public function getAll(array $relationships = []): Collection
    {
        $cacheKey = $this->getCacheKey('all', $relationships);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($relationships) {
            $query = $this->model->newQuery();
            
            if (!empty($relationships)) {
                $query->with($relationships);
            } elseif (!empty($this->relationships)) {
                $query->with($this->relationships);
            }
            
            return $query->get();
        });
    }

    /**
     * Get paginated records with filters
     */
    public function getPaginated(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        
        // Apply relationships
        if (!empty($this->relationships)) {
            $query->with($this->relationships);
        }
        
        // Apply filters using trait if available
        if (method_exists($this->model, 'scopeFilterAndPaginate')) {
            return $query->filterAndPaginate($request, $perPage);
        }
        
        // Fallback manual filtering
        $this->applyFilters($query, $request);
        
        return $query->paginate($perPage);
    }

    /**
     * Find record by ID with caching
     */
    public function findById(int $id, array $relationships = []): ?Model
    {
        $cacheKey = $this->getCacheKey('find', [$id, $relationships]);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $relationships) {
            $query = $this->model->newQuery();
            
            if (!empty($relationships)) {
                $query->with($relationships);
            } elseif (!empty($this->relationships)) {
                $query->with($this->relationships);
            }
            
            return $query->find($id);
        });
    }

    /**
     * Find record by slug with caching
     */
    public function findBySlug(string $slug, array $relationships = []): ?Model
    {
        if (!method_exists($this->model, 'scopeBySlug')) {
            return null;
        }
        
        $cacheKey = $this->getCacheKey('slug', [$slug, $relationships]);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($slug, $relationships) {
            $query = $this->model->newQuery();
            
            if (!empty($relationships)) {
                $query->with($relationships);
            } elseif (!empty($this->relationships)) {
                $query->with($this->relationships);
            }
            
            return $query->bySlug($slug)->first();
        });
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        try {
            DB::beginTransaction();
            
            $record = $this->model->create($this->prepareData($data));
            
            $this->afterCreate($record, $data);
            
            DB::commit();
            
            $this->clearCache();
            
            return $record->fresh($this->relationships);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating ' . class_basename($this->model) . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing record
     */
    public function update(int $id, array $data): ?Model
    {
        try {
            DB::beginTransaction();
            
            $record = $this->model->find($id);
            
            if (!$record) {
                return null;
            }
            
            $record->update($this->prepareData($data));
            
            $this->afterUpdate($record, $data);
            
            DB::commit();
            
            $this->clearCache();
            
            return $record->fresh($this->relationships);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating ' . class_basename($this->model) . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        try {
            DB::beginTransaction();
            
            $record = $this->model->find($id);
            
            if (!$record) {
                return false;
            }
            
            $this->beforeDelete($record);
            
            $deleted = $record->delete();
            
            DB::commit();
            
            $this->clearCache();
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting ' . class_basename($this->model) . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk delete records
     */
    public function bulkDelete(array $ids): int
    {
        try {
            DB::beginTransaction();
            
            $records = $this->model->whereIn('id', $ids)->get();
            
            foreach ($records as $record) {
                $this->beforeDelete($record);
            }
            
            $deleted = $this->model->whereIn('id', $ids)->delete();
            
            DB::commit();
            
            $this->clearCache();
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk deleting ' . class_basename($this->model) . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get active records
     */
    public function getActive(array $relationships = []): Collection
    {
        $cacheKey = $this->getCacheKey('active', $relationships);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($relationships) {
            $query = $this->model->newQuery();
            
            if (method_exists($this->model, 'scopeActive')) {
                $query->active();
            }
            
            if (!empty($relationships)) {
                $query->with($relationships);
            } elseif (!empty($this->relationships)) {
                $query->with($this->relationships);
            }
            
            return $query->get();
        });
    }

    /**
     * Search records
     */
    public function search(string $term, int $limit = 10): Collection
    {
        $query = $this->model->newQuery();
        
        if (method_exists($this->model, 'scopeSearch')) {
            $query->search($term);
        } else {
            $this->applyManualSearch($query, $term);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $cacheKey = $this->getCacheKey('stats');
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $stats = [
                'total' => $this->model->count(),
                'created_today' => $this->model->whereDate('created_at', today())->count(),
                'created_this_week' => $this->model->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'created_this_month' => $this->model->whereMonth('created_at', now()->month)->count()
            ];
            
            if (method_exists($this->model, 'scopeActive')) {
                $stats['active'] = $this->model->active()->count();
                $stats['inactive'] = $stats['total'] - $stats['active'];
            }
            
            return array_merge($stats, $this->getCustomStats());
        });
    }

    /**
     * Apply manual filters
     */
    protected function applyFilters($query, Request $request): void
    {
        // Search
        if ($request->filled('search')) {
            $this->applyManualSearch($query, $request->get('search'));
        }
        
        // Sorting
        if ($request->filled('field') && $request->filled('order')) {
            $field = $request->get('field');
            $order = $request->get('order', 'asc');
            
            if (in_array($field, $this->sortFields)) {
                $query->orderBy($field, $order);
            }
        }
        
        // Date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }
    }

    /**
     * Apply manual search
     */
    protected function applyManualSearch($query, string $term): void
    {
        if (empty($this->searchFields)) {
            return;
        }
        
        $query->where(function ($q) use ($term) {
            foreach ($this->searchFields as $field) {
                $q->orWhere($field, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Prepare data before saving
     */
    protected function prepareData(array $data): array
    {
        return $data;
    }

    /**
     * Hook after creating record
     */
    protected function afterCreate(Model $record, array $data): void
    {
        // Override in child classes
    }

    /**
     * Hook after updating record
     */
    protected function afterUpdate(Model $record, array $data): void
    {
        // Override in child classes
    }

    /**
     * Hook before deleting record
     */
    protected function beforeDelete(Model $record): void
    {
        // Override in child classes
    }

    /**
     * Get custom statistics
     */
    protected function getCustomStats(): array
    {
        return [];
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(string $operation, array $params = []): string
    {
        $key = $this->cachePrefix . ':' . $operation;
        
        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Clear all cache for this model
     */
    protected function clearCache(): void
    {
        $pattern = $this->cachePrefix . ':*';
        
        // This is a simplified cache clearing - in production, use Redis SCAN
        Cache::flush();
    }

    /**
     * Set cache TTL
     */
    public function setCacheTtl(int $ttl): self
    {
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * Set relationships to load
     */
    public function setRelationships(array $relationships): self
    {
        $this->relationships = $relationships;
        return $this;
    }
}
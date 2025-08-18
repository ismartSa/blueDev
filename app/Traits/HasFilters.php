<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for adding common filtering functionality to models
 * Implements DRY principle for query filtering
 */
trait HasFilters
{
    /**
     * Apply search filter to query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $searchFields = $this->getSearchableFields();
        
        return $query->where(function ($q) use ($search, $searchFields) {
            foreach ($searchFields as $field) {
                if (str_contains($field, '.')) {
                    // Handle relationship fields
                    $this->applyRelationshipSearch($q, $field, $search);
                } else {
                    // Handle direct model fields
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply sorting to query
     */
    public function scopeSort(Builder $query, ?string $field, ?string $direction = 'asc'): Builder
    {
        if (empty($field)) {
            return $query;
        }

        $sortableFields = $this->getSortableFields();
        
        if (!in_array($field, $sortableFields)) {
            return $query;
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if (str_contains($field, '.')) {
            // Handle relationship sorting
            return $this->applyRelationshipSort($query, $field, $direction);
        }

        return $query->orderBy($field, $direction);
    }

    /**
     * Apply status filter
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        $statusField = $this->getStatusField();
        
        if ($statusField && $this->hasStatusValues($status)) {
            return $query->where($statusField, $status);
        }

        return $query;
    }

    /**
     * Apply date range filter
     */
    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate, string $field = 'created_at'): Builder
    {
        if ($startDate) {
            $query->whereDate($field, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($field, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Apply active/inactive filter
     */
    public function scopeActive(Builder $query, ?bool $active = true): Builder
    {
        $activeField = $this->getActiveField();
        
        if ($activeField) {
            return $query->where($activeField, $active);
        }

        return $query;
    }

    /**
     * Apply pagination with filters
     */
    public function scopeFilterAndPaginate(Builder $query, Request $request, int $perPage = 15)
    {
        // Apply search
        $query->search($request->get('search'));
        
        // Apply sorting
        $query->sort(
            $request->get('field', 'created_at'),
            $request->get('order', 'desc')
        );
        
        // Apply status filter
        $query->status($request->get('status'));
        
        // Apply date range
        $query->dateRange(
            $request->get('start_date'),
            $request->get('end_date')
        );
        
        // Apply active filter
        if ($request->has('active')) {
            $query->active($request->boolean('active'));
        }
        
        // Apply course filter (for Quiz model)
        if ($request->filled('course_id') && $query->getModel()->getTable() === 'quizzes') {
            $query->where('course_id', $request->get('course_id'));
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Apply relationship search
     */
    protected function applyRelationshipSearch(Builder $query, string $field, string $search): void
    {
        [$relation, $column] = explode('.', $field, 2);
        
        $query->orWhereHas($relation, function ($q) use ($column, $search) {
            $q->where($column, 'like', "%{$search}%");
        });
    }

    /**
     * Apply relationship sorting
     */
    protected function applyRelationshipSort(Builder $query, string $field, string $direction): Builder
    {
        [$relation, $column] = explode('.', $field, 2);
        
        return $query->with($relation)->orderBy(
            $this->getRelationshipSortSubquery($relation, $column),
            $direction
        );
    }

    /**
     * Get relationship sort subquery
     */
    protected function getRelationshipSortSubquery(string $relation, string $column)
    {
        $relationInstance = $this->{$relation}();
        $relatedTable = $relationInstance->getRelated()->getTable();
        $foreignKey = $relationInstance->getForeignKeyName();
        $localKey = $relationInstance->getLocalKeyName();
        
        return $relationInstance->getRelated()
            ->select($column)
            ->whereColumn("{$relatedTable}.{$relationInstance->getOwnerKeyName()}", "{$this->getTable()}.{$localKey}")
            ->limit(1);
    }

    /**
     * Get searchable fields for the model
     * Override this method in your model
     */
    protected function getSearchableFields(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : ['title', 'name'];
    }

    /**
     * Get sortable fields for the model
     * Override this method in your model
     */
    protected function getSortableFields(): array
    {
        return property_exists($this, 'sortable') ? $this->sortable : [
            'id', 'created_at', 'updated_at', 'title', 'name'
        ];
    }

    /**
     * Get status field name
     * Override this method in your model
     */
    protected function getStatusField(): ?string
    {
        return property_exists($this, 'statusField') ? $this->statusField : 'status';
    }

    /**
     * Get active field name
     * Override this method in your model
     */
    protected function getActiveField(): ?string
    {
        return property_exists($this, 'activeField') ? $this->activeField : 'is_active';
    }

    /**
     * Check if status value is valid
     */
    protected function hasStatusValues(string $status): bool
    {
        if (!property_exists($this, 'statusValues')) {
            return true;
        }
        
        return in_array($status, $this->statusValues);
    }

    /**
     * Get filter summary for debugging
     */
    public function getFilterSummary(Request $request): array
    {
        return [
            'search' => $request->get('search'),
            'field' => $request->get('field'),
            'order' => $request->get('order'),
            'status' => $request->get('status'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'active' => $request->get('active'),
            'searchable_fields' => $this->getSearchableFields(),
            'sortable_fields' => $this->getSortableFields()
        ];
    }
}
<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait for common model attributes and behaviors
 * Implements DRY principle for common model functionality
 */
trait HasCommonAttributes
{
    /**
     * Boot the trait
     */
    protected static function bootHasCommonAttributes(): void
    {
        static::creating(function (Model $model) {
            // Auto-generate UUID if the model uses UUID
            if ($model->hasUuidField() && empty($model->{$model->getUuidField()})) {
                $model->{$model->getUuidField()} = Str::uuid();
            }

            // Auto-generate slug if the model uses slug
            if ($model->hasSlugField() && empty($model->{$model->getSlugField()})) {
                $model->{$model->getSlugField()} = $model->generateSlug();
            }

            // Set default status if applicable
            if ($model->hasStatusField() && empty($model->{$model->getStatusField()})) {
                $model->{$model->getStatusField()} = $model->getDefaultStatus();
            }

            // Set default active state if applicable
            if ($model->hasActiveField() && is_null($model->{$model->getActiveField()})) {
                $model->{$model->getActiveField()} = $model->getDefaultActiveState();
            }
        });

        static::updating(function (Model $model) {
            // Update slug if title/name changed
            if ($model->hasSlugField() && $model->shouldUpdateSlug()) {
                $model->{$model->getSlugField()} = $model->generateSlug();
            }
        });
    }

    /**
     * Generate unique slug for the model
     */
    public function generateSlug(): string
    {
        $sourceField = $this->getSlugSourceField();
        $slugField = $this->getSlugField();
        $baseSlug = Str::slug($this->{$sourceField});
        
        if (empty($baseSlug)) {
            $baseSlug = Str::slug(class_basename($this) . '-' . time());
        }

        $slug = $baseSlug;
        $counter = 1;

        // Ensure uniqueness
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where($this->getSlugField(), $slug);
        
        // Exclude current model when updating
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }

    /**
     * Scope to find by slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where($this->getSlugField(), $slug);
    }

    /**
     * Scope to find by UUID
     */
    public function scopeByUuid($query, string $uuid)
    {
        return $query->where($this->getUuidField(), $uuid);
    }

    /**
     * Get route key name (use slug if available)
     */
    public function getRouteKeyName(): string
    {
        return $this->hasSlugField() ? $this->getSlugField() : parent::getRouteKeyName();
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at?->format('M d, Y') ?? '';
    }

    /**
     * Get formatted updated date
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at?->format('M d, Y') ?? '';
    }

    /**
     * Get human readable created date
     */
    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    /**
     * Get human readable updated date
     */
    public function getUpdatedAtHumanAttribute(): string
    {
        return $this->updated_at?->diffForHumans() ?? '';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->hasStatusField()) {
            return 'badge-secondary';
        }

        $statusClasses = $this->getStatusClasses();
        $status = $this->{$this->getStatusField()};
        
        return $statusClasses[$status] ?? 'badge-secondary';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->hasStatusField()) {
            return 'N/A';
        }

        $statusLabels = $this->getStatusLabels();
        $status = $this->{$this->getStatusField()};
        
        return $statusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Check if model has UUID field
     */
    protected function hasUuidField(): bool
    {
        return property_exists($this, 'uuidField') || in_array('uuid', $this->getFillable());
    }

    /**
     * Get UUID field name
     */
    protected function getUuidField(): string
    {
        return property_exists($this, 'uuidField') ? $this->uuidField : 'uuid';
    }

    /**
     * Check if model has slug field
     */
    protected function hasSlugField(): bool
    {
        return property_exists($this, 'slugField') || in_array('slug', $this->getFillable());
    }

    /**
     * Get slug field name
     */
    protected function getSlugField(): string
    {
        return property_exists($this, 'slugField') ? $this->slugField : 'slug';
    }

    /**
     * Get slug source field name
     */
    protected function getSlugSourceField(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 
            (in_array('title', $this->getFillable()) ? 'title' : 'name');
    }

    /**
     * Check if slug should be updated
     */
    protected function shouldUpdateSlug(): bool
    {
        $sourceField = $this->getSlugSourceField();
        return $this->isDirty($sourceField) && 
               (property_exists($this, 'updateSlugOnChange') ? $this->updateSlugOnChange : false);
    }

    /**
     * Check if model has status field
     */
    protected function hasStatusField(): bool
    {
        return property_exists($this, 'statusField') || in_array('status', $this->getFillable());
    }

    /**
     * Get status field name
     */
    protected function getStatusField(): string
    {
        return property_exists($this, 'statusField') ? $this->statusField : 'status';
    }

    /**
     * Check if model has active field
     */
    protected function hasActiveField(): bool
    {
        return property_exists($this, 'activeField') || in_array('is_active', $this->getFillable());
    }

    /**
     * Get active field name
     */
    protected function getActiveField(): string
    {
        return property_exists($this, 'activeField') ? $this->activeField : 'is_active';
    }

    /**
     * Get default status
     */
    protected function getDefaultStatus(): string
    {
        return property_exists($this, 'defaultStatus') ? $this->defaultStatus : 'active';
    }

    /**
     * Get default active state
     */
    protected function getDefaultActiveState(): bool
    {
        return property_exists($this, 'defaultActiveState') ? $this->defaultActiveState : true;
    }

    /**
     * Get status classes for badges
     */
    protected function getStatusClasses(): array
    {
        return property_exists($this, 'statusClasses') ? $this->statusClasses : [
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'pending' => 'badge-warning',
            'draft' => 'badge-info',
            'published' => 'badge-success',
            'archived' => 'badge-dark'
        ];
    }

    /**
     * Get status labels
     */
    protected function getStatusLabels(): array
    {
        return property_exists($this, 'statusLabels') ? $this->statusLabels : [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived'
        ];
    }

    /**
     * Get model summary for API responses
     */
    public function getSummary(): array
    {
        $summary = [
            'id' => $this->getKey(),
            'created_at' => $this->formatted_created_at,
            'updated_at' => $this->formatted_updated_at
        ];

        if ($this->hasUuidField()) {
            $summary['uuid'] = $this->{$this->getUuidField()};
        }

        if ($this->hasSlugField()) {
            $summary['slug'] = $this->{$this->getSlugField()};
        }

        if ($this->hasStatusField()) {
            $summary['status'] = $this->{$this->getStatusField()};
            $summary['status_label'] = $this->status_label;
        }

        if ($this->hasActiveField()) {
            $summary['is_active'] = $this->{$this->getActiveField()};
        }

        return $summary;
    }
}
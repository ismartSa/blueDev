<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class BaseController extends Controller
{
    /**
     * View prefix for Inertia components
     */
    protected string $viewPrefix = '';
    /**
     * Apply common filters to query builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Search filter
        if (!empty($filters['search'])) {
            $searchFields = $this->getSearchFields();
            $query->where(function ($q) use ($filters, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$filters['search']}%");
                }
            });
        }

        // Order filter
        if (!empty($filters['field']) && !empty($filters['order'])) {
            $query->orderBy($filters['field'], $filters['order']);
        }

        return $query;
    }

    /**
     * Get paginated results with filters
     */
    protected function getPaginatedResults(Builder $query, Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = $request->input('perPage', 10);
        return $query->paginate($perPage);
    }

    /**
     * Get filters from request
     */
    protected function getFilters(Request $request): array
    {
        return $request->only(['search', 'field', 'order', 'perPage']);
    }

    /**
     * Render Inertia response with common data
     */
    protected function renderIndex(string $component, array $data = []): Response
    {
        return Inertia::render($component, array_merge([
            'filters' => request()->only(['search', 'field', 'order', 'perPage'])
        ], $data));
    }

    /**
     * Render Inertia response for specific view
     */
    protected function inertiaResponse(string $view, array $data = []): Response
    {
        $component = !empty($this->viewPrefix) ? $this->viewPrefix . '/' . $view : $view;
        return Inertia::render($component, $data);
    }

    /**
     * Handle successful operations with redirect
     */
    protected function successResponse(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    /**
     * Handle validation and store operations
     */
    protected function validateAndStore(Request $request, array $rules, $model, string $successRoute, string $successMessage)
    {
        $validated = $request->validate($rules);
        $instance = $model::create($validated);
        return $this->successResponse($successRoute, $successMessage, ['id' => $instance->id]);
    }

    /**
     * Handle validation and update operations
     */
    protected function validateAndUpdate(Request $request, array $rules, $instance, string $successRoute, string $successMessage)
    {
        $validated = $request->validate($rules);
        $instance->update($validated);
        return $this->successResponse($successRoute, $successMessage, ['id' => $instance->id]);
    }

    /**
     * Get search fields for the model (override in child controllers)
     */
    protected function getSearchFields(): array
    {
        return ['title', 'description'];
    }

    /**
     * Get validation rules (override in child controllers)
     */
    protected function getValidationRules(): array
    {
        return [];
    }
}
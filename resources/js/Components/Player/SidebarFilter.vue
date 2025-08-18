<template>
    <div class="bg-white/95 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 mb-4 shadow-sm">
        <!-- Filter Header -->
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filters
            </h3>
            <button 
                @click="$emit('reset-filters')"
                class="text-xs text-slate-500 hover:text-indigo-600 font-medium transition-colors"
                title="Reset all filters"
            >
                Reset
            </button>
        </div>
        
        <!-- Quick Filter Buttons -->
        <div class="grid grid-cols-2 gap-2 mb-3">
            <button 
                @click="$emit('toggle-current-only')"
                :class="[
                    'px-3 py-2 rounded-lg text-xs font-medium transition-all duration-200',
                    showOnlyCurrentSection 
                        ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' 
                        : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'
                ]"
            >
                Current Only
            </button>
            <button 
                @click="$emit('toggle-empty-sections')"
                :class="[
                    'px-3 py-2 rounded-lg text-xs font-medium transition-all duration-200',
                    hideEmptySections 
                        ? 'bg-red-100 text-red-700 border border-red-200' 
                        : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'
                ]"
            >
                Hide Empty
            </button>
        </div>
        
        <!-- Status Filters -->
        <div class="space-y-2 mb-3">
            <div class="text-xs font-medium text-slate-600 mb-2">Show by Status:</div>
            <div class="flex flex-wrap gap-2">
                <button 
                    @click="$emit('toggle-completed')"
                    :class="[
                        'px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 flex items-center gap-1',
                        showCompletedSections 
                            ? 'bg-green-100 text-green-700 border border-green-200' 
                            : 'bg-slate-100 text-slate-500 border border-slate-200'
                    ]"
                >
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    Completed
                </button>
                <button 
                    @click="$emit('toggle-in-progress')"
                    :class="[
                        'px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 flex items-center gap-1',
                        showInProgressSections 
                            ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                            : 'bg-slate-100 text-slate-500 border border-slate-200'
                    ]"
                >
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    In Progress
                </button>
                <button 
                    @click="$emit('toggle-not-started')"
                    :class="[
                        'px-2 py-1 rounded-md text-xs font-medium transition-all duration-200 flex items-center gap-1',
                        showNotStartedSections 
                            ? 'bg-slate-100 text-slate-700 border border-slate-200' 
                            : 'bg-slate-100 text-slate-500 border border-slate-200'
                    ]"
                >
                    <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                    Not Started
                </button>
            </div>
        </div>
        
        <!-- Progress Threshold -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-xs font-medium text-slate-600">Min Progress:</label>
                <span class="text-xs text-slate-500">{{ minProgressThreshold }}%</span>
            </div>
            <input 
                type="range" 
                :value="minProgressThreshold"
                @input="$emit('set-progress-threshold', parseInt($event.target.value))"
                min="0" 
                max="100" 
                step="10"
                class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer slider"
            >
            <div class="flex justify-between text-xs text-slate-400">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
            </div>
        </div>
        
        <!-- Active Filters Summary -->
        <div v-if="filterSummary.length > 0" class="mt-3 pt-3 border-t border-slate-200/60">
            <div class="text-xs font-medium text-slate-600 mb-2">Active Filters:</div>
            <div class="flex flex-wrap gap-1">
                <span 
                    v-for="filter in filterSummary" 
                    :key="filter"
                    class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs rounded-md border border-indigo-200"
                >
                    {{ filter }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    showCompletedSections: Boolean,
    showInProgressSections: Boolean,
    showNotStartedSections: Boolean,
    hideEmptySections: Boolean,
    showOnlyCurrentSection: Boolean,
    minProgressThreshold: Number,
    filterSummary: Array
})

defineEmits([
    'toggle-completed',
    'toggle-in-progress', 
    'toggle-not-started',
    'toggle-empty-sections',
    'toggle-current-only',
    'set-progress-threshold',
    'reset-filters'
])
</script>

<style scoped>
/* Custom slider styling */
.slider::-webkit-slider-thumb {
    appearance: none;
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.slider::-webkit-slider-thumb:hover {
    background: #4338ca;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.slider::-moz-range-thumb {
    height: 16px;
    width: 16px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.slider::-moz-range-thumb:hover {
    background: #4338ca;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
</style>
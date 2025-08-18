<template>
  <div :class="gridClass">
    <div
      v-for="(stat, index) in stats"
      :key="stat.key || index"
      :class="getCardClass(stat)"
      @click="stat.clickable && handleCardClick(stat)"
    >
      <!-- Card Header -->
      <div class="stat-header">
        <div class="stat-icon-wrapper" v-if="stat.icon">
          <component
            v-if="stat.iconComponent"
            :is="stat.iconComponent"
            :class="getIconClass(stat)"
          />
          <i v-else :class="[stat.icon, getIconClass(stat)]"></i>
        </div>
        
        <div class="stat-actions" v-if="stat.actions">
          <button
            v-for="action in stat.actions"
            :key="action.name"
            @click.stop="handleAction(action, stat)"
            :class="['action-btn', action.class]"
            :title="action.title"
          >
            <i :class="action.icon"></i>
          </button>
        </div>
      </div>

      <!-- Card Content -->
      <div class="stat-content">
        <div class="stat-value-section">
          <div class="stat-value">
            <span v-if="stat.prefix" class="stat-prefix">{{ stat.prefix }}</span>
            <span class="stat-number">{{ formatValue(stat.value, stat) }}</span>
            <span v-if="stat.suffix" class="stat-suffix">{{ stat.suffix }}</span>
          </div>
          
          <div v-if="stat.change !== undefined" class="stat-change">
            <span :class="getChangeClass(stat.change)">
              <i :class="getChangeIcon(stat.change)"></i>
              {{ formatChange(stat.change) }}
            </span>
            <span v-if="stat.changeLabel" class="change-label">
              {{ stat.changeLabel }}
            </span>
          </div>
        </div>

        <div class="stat-info">
          <h3 class="stat-title">{{ stat.title }}</h3>
          <p v-if="stat.description" class="stat-description">
            {{ stat.description }}
          </p>
        </div>

        <!-- Progress Bar -->
        <div v-if="stat.progress !== undefined" class="stat-progress">
          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: `${Math.min(100, Math.max(0, stat.progress))}%` }"
              :class="getProgressClass(stat)"
            ></div>
          </div>
          <div class="progress-text">
            <span>{{ stat.progressLabel || `${stat.progress}%` }}</span>
            <span v-if="stat.progressTarget" class="progress-target">
              / {{ formatValue(stat.progressTarget, stat) }}
            </span>
          </div>
        </div>

        <!-- Chart/Sparkline -->
        <div v-if="stat.chart" class="stat-chart">
          <component
            :is="stat.chart.component"
            :data="stat.chart.data"
            :options="stat.chart.options"
            v-bind="stat.chart.props"
          />
        </div>

        <!-- Custom Content Slot -->
        <div v-if="$slots[`stat-${stat.key}`]" class="stat-custom">
          <slot :name="`stat-${stat.key}`" :stat="stat" />
        </div>
      </div>

      <!-- Card Footer -->
      <div v-if="stat.footer || stat.link" class="stat-footer">
        <div v-if="stat.footer" class="footer-content">
          {{ stat.footer }}
        </div>
        
        <a
          v-if="stat.link"
          :href="stat.link.url"
          :class="['stat-link', stat.link.class]"
          @click="stat.link.preventDefault && $event.preventDefault()"
        >
          {{ stat.link.text }}
          <i class="fas fa-arrow-right ml-1"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  stats: { type: Array, required: true },
  columns: { type: Number, default: 4 },
  gap: { type: String, default: '6' },
  loading: { type: Boolean, default: false },
  animated: { type: Boolean, default: true },
  responsive: { type: Boolean, default: true },
  cardClass: { type: String, default: '' },
  gridClass: { type: String, default: '' }
})

// Emits
const emit = defineEmits(['card-click', 'action-click'])

// Computed properties
const gridClass = computed(() => {
  const baseClass = 'stats-grid'
  const columnsClass = `grid-cols-1 sm:grid-cols-2 lg:grid-cols-${Math.min(props.columns, 4)}`
  const gapClass = `gap-${props.gap}`
  const customClass = props.gridClass
  
  return [baseClass, columnsClass, gapClass, customClass].filter(Boolean).join(' ')
})

// Methods
const getCardClass = (stat) => {
  const baseClass = 'stat-card'
  const themeClass = stat.theme ? `stat-${stat.theme}` : 'stat-default'
  const clickableClass = stat.clickable ? 'stat-clickable' : ''
  const loadingClass = props.loading ? 'stat-loading' : ''
  const animatedClass = props.animated ? 'stat-animated' : ''
  const customClass = props.cardClass || stat.cardClass || ''
  
  return [baseClass, themeClass, clickableClass, loadingClass, animatedClass, customClass]
    .filter(Boolean).join(' ')
}

const getIconClass = (stat) => {
  const baseClass = 'stat-icon'
  const sizeClass = stat.iconSize ? `text-${stat.iconSize}` : 'text-2xl'
  const colorClass = stat.iconColor || ''
  
  return [baseClass, sizeClass, colorClass].filter(Boolean).join(' ')
}

const getChangeClass = (change) => {
  const baseClass = 'change-indicator'
  
  if (change > 0) return `${baseClass} change-positive`
  if (change < 0) return `${baseClass} change-negative`
  return `${baseClass} change-neutral`
}

const getChangeIcon = (change) => {
  if (change > 0) return 'fas fa-arrow-up'
  if (change < 0) return 'fas fa-arrow-down'
  return 'fas fa-minus'
}

const getProgressClass = (stat) => {
  const baseClass = 'progress-fill'
  const themeClass = stat.progressTheme ? `progress-${stat.progressTheme}` : 'progress-primary'
  
  return [baseClass, themeClass].join(' ')
}

const formatValue = (value, stat) => {
  if (value === null || value === undefined) return '—'
  
  // Custom formatter
  if (stat.formatter && typeof stat.formatter === 'function') {
    return stat.formatter(value)
  }
  
  // Built-in formatters
  switch (stat.format) {
    case 'currency':
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: stat.currency || 'USD',
        minimumFractionDigits: stat.decimals || 0
      }).format(value)
      
    case 'percentage':
      return `${(value * 100).toFixed(stat.decimals || 1)}%`
      
    case 'number':
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: stat.decimals || 0,
        maximumFractionDigits: stat.decimals || 0
      }).format(value)
      
    case 'compact':
      return new Intl.NumberFormat('en-US', {
        notation: 'compact',
        compactDisplay: 'short'
      }).format(value)
      
    case 'duration':
      return formatDuration(value)
      
    case 'bytes':
      return formatBytes(value)
      
    default:
      return value.toString()
  }
}

const formatChange = (change) => {
  if (change === null || change === undefined) return ''
  
  const absChange = Math.abs(change)
  
  if (absChange >= 1) {
    return `${absChange.toFixed(1)}%`
  }
  
  return `${(absChange * 100).toFixed(1)}%`
}

const formatDuration = (seconds) => {
  if (seconds < 60) return `${seconds}s`
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`
  return `${Math.floor(seconds / 86400)}d`
}

const formatBytes = (bytes) => {
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  if (bytes === 0) return '0 B'
  
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`
}

const handleCardClick = (stat) => {
  emit('card-click', stat)
}

const handleAction = (action, stat) => {
  emit('action-click', { action, stat })
}

// Slots
const $slots = defineSlots()
</script>

<style scoped>
.stats-grid {
  @apply grid;
}

.stat-card {
  @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6 transition-all duration-200;
}

.stat-card:hover {
  @apply shadow-md;
}

.stat-clickable {
  @apply cursor-pointer;
}

.stat-clickable:hover {
  @apply shadow-lg transform -translate-y-1;
}

.stat-loading {
  @apply opacity-75 pointer-events-none;
}

.stat-animated {
  @apply transition-all duration-300 ease-in-out;
}

/* Theme Variants */
.stat-primary {
  @apply border-blue-200 bg-blue-50;
}

.stat-success {
  @apply border-green-200 bg-green-50;
}

.stat-warning {
  @apply border-yellow-200 bg-yellow-50;
}

.stat-danger {
  @apply border-red-200 bg-red-50;
}

.stat-info {
  @apply border-cyan-200 bg-cyan-50;
}

/* Card Structure */
.stat-header {
  @apply flex justify-between items-start mb-4;
}

.stat-icon-wrapper {
  @apply flex-shrink-0;
}

.stat-icon {
  @apply text-gray-600;
}

.stat-primary .stat-icon {
  @apply text-blue-600;
}

.stat-success .stat-icon {
  @apply text-green-600;
}

.stat-warning .stat-icon {
  @apply text-yellow-600;
}

.stat-danger .stat-icon {
  @apply text-red-600;
}

.stat-info .stat-icon {
  @apply text-cyan-600;
}

.stat-actions {
  @apply flex space-x-2;
}

.action-btn {
  @apply p-1 rounded hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors;
}

.stat-content {
  @apply space-y-4;
}

.stat-value-section {
  @apply flex items-center justify-between;
}

.stat-value {
  @apply flex items-baseline;
}

.stat-prefix,
.stat-suffix {
  @apply text-sm text-gray-500;
}

.stat-number {
  @apply text-3xl font-bold text-gray-900 mx-1;
}

.stat-change {
  @apply flex flex-col items-end text-sm;
}

.change-indicator {
  @apply flex items-center font-medium;
}

.change-positive {
  @apply text-green-600;
}

.change-negative {
  @apply text-red-600;
}

.change-neutral {
  @apply text-gray-500;
}

.change-label {
  @apply text-xs text-gray-500 mt-1;
}

.stat-info {
  @apply space-y-1;
}

.stat-title {
  @apply text-sm font-medium text-gray-900;
}

.stat-description {
  @apply text-xs text-gray-500;
}

.stat-progress {
  @apply space-y-2;
}

.progress-bar {
  @apply w-full bg-gray-200 rounded-full h-2;
}

.progress-fill {
  @apply h-2 rounded-full transition-all duration-500 ease-out;
}

.progress-primary {
  @apply bg-blue-600;
}

.progress-success {
  @apply bg-green-600;
}

.progress-warning {
  @apply bg-yellow-600;
}

.progress-danger {
  @apply bg-red-600;
}

.progress-text {
  @apply flex justify-between text-xs text-gray-600;
}

.progress-target {
  @apply text-gray-400;
}

.stat-chart {
  @apply h-16;
}

.stat-custom {
  @apply mt-4;
}

.stat-footer {
  @apply flex justify-between items-center mt-4 pt-4 border-t border-gray-100;
}

.footer-content {
  @apply text-xs text-gray-500;
}

.stat-link {
  @apply text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center transition-colors;
}

/* Responsive Design */
@media (max-width: 640px) {
  .stat-card {
    @apply p-4;
  }
  
  .stat-number {
    @apply text-2xl;
  }
  
  .stat-value-section {
    @apply flex-col items-start space-y-2;
  }
}

/* Loading Animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.stat-loading .stat-number,
.stat-loading .stat-title {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Hover Effects */
.stat-animated:hover .stat-icon {
  @apply transform scale-110;
}

.stat-animated:hover .stat-number {
  @apply transform scale-105;
}
</style>
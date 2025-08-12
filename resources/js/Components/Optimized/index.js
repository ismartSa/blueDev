// Optimized Components Export
// This file provides centralized exports for all optimized components
// following the DRY principle and improving import organization

// Data Display Components
export { default as DataTable } from './DataTable.vue'
export { default as OptimizedTable } from './OptimizedTable.vue'
export { default as StatsGrid } from './StatsGrid.vue'

// Form Components
export { default as SmartForm } from './SmartForm.vue'
export { default as OptimizedForm } from './OptimizedForm.vue'

// Notification Components
export { default as NotificationCenter } from './NotificationCenter.vue'

// Component Groups for easier bulk imports
export const TableComponents = {
  DataTable,
  OptimizedTable
}

export const FormComponents = {
  SmartForm,
  OptimizedForm
}

export const DisplayComponents = {
  DataTable,
  OptimizedTable,
  StatsGrid,
  NotificationCenter
}

// Default export for convenience
export default {
  DataTable,
  OptimizedTable,
  StatsGrid,
  SmartForm,
  OptimizedForm,
  NotificationCenter
}
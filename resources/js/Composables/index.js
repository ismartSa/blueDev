// Composables Export
// This file provides centralized exports for all composables
// following the DRY principle and improving import organization

// API and Data Management
export { useApi, useCrud } from './useApi.js'

// Form Management
export { useForm, useMultiStepForm } from './useForm.js'

// Table and List Management
export { useTable, useList } from './useTable.js'

// Notification Management
export {
  useNotifications,
  useToast,
  useGlobalNotifications,
  useApiNotifications
} from './useNotifications.js'

// Composable Groups for easier bulk imports
export const DataComposables = {
  useApi,
  useCrud,
  useTable,
  useList
}

export const UIComposables = {
  useForm,
  useMultiStepForm,
  useNotifications,
  useToast,
  useGlobalNotifications,
  useApiNotifications
}

// Default export for convenience
export default {
  useApi,
  useCrud,
  useForm,
  useMultiStepForm,
  useTable,
  useList,
  useNotifications,
  useToast,
  useGlobalNotifications,
  useApiNotifications
}
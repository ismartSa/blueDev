// Settings utility functions and constants

// Common styling classes
export const COMMON_CLASSES = {
  input: 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors duration-200 text-sm',
  container: 'relative',
  label: 'text-sm font-medium text-gray-700 dark:text-gray-300',
  description: 'text-xs text-gray-500 dark:text-gray-400'
}

// Format label utility
export const formatLabel = (key) => {
  const customLabels = {
    maintenance_mode: 'Maintenance Mode',
    user_registration: 'Allow User Registration',
    email_verification: 'Email Verification Required',
    two_factor_auth: 'Two-Factor Authentication',
    app_name: 'Application Name',
    app_tagline: 'Application Tagline',
    app_description: 'Application Description',
    primary_color: 'Primary Color',
    secondary_color: 'Secondary Color',
    contact_email: 'Contact Email',
    contact_phone: 'Contact Phone',
    contact_address: 'Contact Address',
    session_lifetime: 'Session Lifetime',
    max_file_size: 'Maximum File Size',
    pagination_limit: 'Pagination Limit',
    cache_ttl: 'Cache TTL'
  }
  
  return customLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Placeholder configurations
export const PLACEHOLDERS = {
  app_name: 'Enter your application name',
  app_tagline: 'A brief description of your app',
  app_description: 'Detailed description of your application',
  primary_color: '#3B82F6',
  secondary_color: '#10B981',
  contact_email: 'contact@example.com',
  contact_phone: '+1 (555) 123-4567',
  contact_address: '123 Main St, City, State 12345',
  session_lifetime: '120',
  max_file_size: '10',
  pagination_limit: '20',
  cache_ttl: '3600'
}

// Integer input configurations
export const INTEGER_CONFIG = {
  mins: {
    session_lifetime: 1,
    max_file_size: 1,
    pagination_limit: 5,
    cache_ttl: 60
  },
  maxs: {
    session_lifetime: 43200, // 30 days in minutes
    max_file_size: 100, // 100MB
    pagination_limit: 100,
    cache_ttl: 86400 // 24 hours in seconds
  },
  steps: {
    session_lifetime: 15,
    max_file_size: 1,
    pagination_limit: 5,
    cache_ttl: 60
  },
  units: {
    session_lifetime: 'min',
    max_file_size: 'MB',
    pagination_limit: 'items',
    cache_ttl: 'sec'
  }
}

// File input configurations
export const FILE_CONFIG = {
  acceptedTypes: {
    logo: 'image/*,.svg',
    favicon: 'image/*,.ico',
    banner: 'image/*',
    avatar: 'image/*'
  },
  descriptions: {
    logo: 'PNG, JPG, SVG up to 10MB',
    favicon: 'ICO, PNG up to 10MB',
    banner: 'PNG, JPG up to 10MB',
    avatar: 'PNG, JPG up to 10MB'
  }
}

// Utility functions
export const isImage = (url) => {
  return url && /\.(jpg|jpeg|png|gif|svg|webp)$/i.test(url)
}

export const getPlaceholder = (key) => {
  return PLACEHOLDERS[key] || `Enter ${key.replace(/_/g, ' ')}`
}

export const getIntegerConfig = (key, type) => {
  return INTEGER_CONFIG[type]?.[key] || (type === 'mins' ? 0 : type === 'maxs' ? 999999 : 1)
}

export const getFileConfig = (key, type) => {
  return FILE_CONFIG[type]?.[key] || (type === 'acceptedTypes' ? 'image/*,.ico' : 'Images up to 10MB')
}

// Validation helpers
export const validateFile = (file, key) => {
  const acceptedTypes = getFileConfig(key, 'acceptedTypes').split(',')
  const isValidType = acceptedTypes.some(type => {
    if (type.includes('*')) {
      return file.type.startsWith(type.replace('*', ''))
    }
    return file.type === type || file.name.toLowerCase().endsWith(type.replace('.', ''))
  })
  
  if (!isValidType) {
    return { valid: false, error: `Please select a valid file type: ${getFileConfig(key, 'descriptions')}` }
  }
  
  if (file.size > 10 * 1024 * 1024) {
    return { valid: false, error: 'File size must be less than 10MB' }
  }
  
  return { valid: true }
}
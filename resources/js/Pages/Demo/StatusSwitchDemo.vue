<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-4">
              <ToggleIcon class="w-8 h-8 text-gray-600 dark:text-gray-400" />
              <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Status Switch Demo</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Interactive toggle switches with multiple variants</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Basic Switches -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Switches</h2>
            <div class="space-y-4">
              <StatusSwitch 
                v-model="switches.basic" 
                label="Basic Switch" 
                description="Simple on/off toggle"
              />
              
              <StatusSwitch 
                v-model="switches.withStatus" 
                label="With Status Text" 
                description="Shows current state as text"
                show-status
              />
              
              <StatusSwitch 
                v-model="switches.withIcons" 
                label="With Icons" 
                description="Visual indicators for state"
                show-icons
              />
              
              <StatusSwitch 
                v-model="switches.disabled" 
                label="Disabled Switch" 
                description="Cannot be toggled"
                disabled
              />
            </div>
          </div>

          <!-- Size Variants -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Size Variants</h2>
            <div class="space-y-4">
              <StatusSwitch 
                v-model="switches.small" 
                label="Small Switch" 
                size="sm"
                show-status
              />
              
              <StatusSwitch 
                v-model="switches.medium" 
                label="Medium Switch (Default)" 
                size="md"
                show-status
              />
              
              <StatusSwitch 
                v-model="switches.large" 
                label="Large Switch" 
                size="lg"
                show-status
              />
            </div>
          </div>

          <!-- Color Variants -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Color Variants</h2>
            <div class="space-y-4">
              <StatusSwitch 
                v-model="switches.primary" 
                label="Primary (Blue)" 
                variant="primary"
                show-status
                show-icons
              />
              
              <StatusSwitch 
                v-model="switches.success" 
                label="Success (Green)" 
                variant="success"
                show-status
                show-icons
              />
              
              <StatusSwitch 
                v-model="switches.warning" 
                label="Warning (Yellow)" 
                variant="warning"
                show-status
                show-icons
              />
              
              <StatusSwitch 
                v-model="switches.danger" 
                label="Danger (Red)" 
                variant="danger"
                show-status
                show-icons
              />
            </div>
          </div>

          <!-- Real-world Examples -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Real-world Examples</h2>
            <div class="space-y-4">
              <StatusSwitch 
                v-model="settings.notifications" 
                label="Email Notifications" 
                description="Receive email updates about your account"
                variant="primary"
                show-status
                on-text="Enabled"
                off-text="Disabled"
                @change="handleNotificationChange"
              />
              
              <StatusSwitch 
                v-model="settings.darkMode" 
                label="Dark Mode" 
                description="Switch between light and dark themes"
                variant="primary"
                show-icons
                :on-icon="MoonIcon"
                :off-icon="SunIcon"
                @change="handleDarkModeChange"
              />
              
              <StatusSwitch 
                v-model="settings.maintenance" 
                label="Maintenance Mode" 
                description="Put the application in maintenance mode"
                variant="warning"
                show-status
                on-text="Active"
                off-text="Inactive"
                required
                @change="handleMaintenanceChange"
              />
              
              <StatusSwitch 
                v-model="settings.publicAccess" 
                label="Public Access" 
                description="Allow public access to this resource"
                variant="success"
                show-status
                show-icons
                on-text="Public"
                off-text="Private"
                @change="handlePublicAccessChange"
              />
            </div>
          </div>
        </div>

        <!-- State Display -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current State</h2>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <pre class="text-sm text-gray-600 dark:text-gray-300">{{ JSON.stringify({ switches, settings }, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusSwitch from '@/Components/UI/StatusSwitch.vue'
import { 
  Cog6ToothIcon as ToggleIcon, 
  MoonIcon, 
  SunIcon 
} from '@heroicons/vue/24/outline'

// Demo switches state
const switches = reactive({
  basic: false,
  withStatus: true,
  withIcons: false,
  disabled: true,
  small: false,
  medium: true,
  large: false,
  primary: true,
  success: false,
  warning: true,
  danger: false
})

// Settings state (real-world examples)
const settings = reactive({
  notifications: true,
  darkMode: false,
  maintenance: false,
  publicAccess: true
})

// Event handlers
const handleNotificationChange = (value) => {
  console.log('Notifications:', value ? 'enabled' : 'disabled')
  // Here you would typically make an API call to update the setting
}

const handleDarkModeChange = (value) => {
  console.log('Dark mode:', value ? 'enabled' : 'disabled')
  // Toggle dark mode class on document
  document.documentElement.classList.toggle('dark', value)
}

const handleMaintenanceChange = (value) => {
  console.log('Maintenance mode:', value ? 'active' : 'inactive')
  // Here you would typically make an API call to toggle maintenance mode
}

const handlePublicAccessChange = (value) => {
  console.log('Public access:', value ? 'enabled' : 'disabled')
  // Here you would typically make an API call to update access settings
}
</script>
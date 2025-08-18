<template>
  <div class="space-y-6">
    <!-- Simple Usage -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Simple Usage</h3>
      <StatusSwitch 
        v-model="isEnabled" 
        label="Enable Feature" 
        description="Toggle this feature on or off"
      />
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Current state: <span class="font-medium">{{ isEnabled ? 'Enabled' : 'Disabled' }}</span>
      </p>
    </div>

    <!-- Settings Form Integration -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Settings Form Integration</h3>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <StatusSwitch 
          v-model="settings.notifications" 
          label="Email Notifications" 
          description="Receive email updates about your account"
          variant="primary"
          show-status
          on-text="Enabled"
          off-text="Disabled"
        />
        
        <StatusSwitch 
          v-model="settings.autoSave" 
          label="Auto Save" 
          description="Automatically save your work"
          variant="success"
          show-icons
        />
        
        <StatusSwitch 
          v-model="settings.maintenance" 
          label="Maintenance Mode" 
          description="Put the system in maintenance mode"
          variant="warning"
          show-status
          required
        />
        
        <div class="flex justify-end pt-4">
          <button 
            type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Save Settings
          </button>
        </div>
      </form>
    </div>

    <!-- Dynamic List with Switches -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Dynamic List</h3>
      <div class="space-y-3">
        <div 
          v-for="(permission, index) in permissions" 
          :key="permission.id"
          class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
        >
          <div>
            <h4 class="font-medium text-gray-900 dark:text-white">{{ permission.name }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ permission.description }}</p>
          </div>
          <StatusSwitch 
            v-model="permission.enabled" 
            :variant="permission.enabled ? 'success' : 'danger'"
            size="sm"
            show-status
            on-text="Granted"
            off-text="Denied"
            @change="updatePermission(permission.id, $event)"
          />
        </div>
      </div>
    </div>

    <!-- Code Example -->
    <div class="bg-gray-900 rounded-lg p-4">
      <h3 class="text-lg font-medium text-white mb-3">Code Example</h3>
      <pre class="text-sm text-gray-300 overflow-x-auto"><code>&lt;!-- Basic Usage --&gt;
&lt;StatusSwitch 
  v-model="isEnabled" 
  label="Enable Feature" 
  description="Toggle this feature on or off"
/&gt;

&lt;!-- With all options --&gt;
&lt;StatusSwitch 
  v-model="settings.notifications" 
  label="Email Notifications" 
  description="Receive email updates"
  variant="primary"
  size="md"
  show-status
  show-icons
  on-text="Enabled"
  off-text="Disabled"
  required
  @change="handleChange"
/&gt;</code></pre>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import StatusSwitch from './StatusSwitch.vue'

// Simple toggle state
const isEnabled = ref(false)

// Settings form state
const settings = reactive({
  notifications: true,
  autoSave: false,
  maintenance: false
})

// Dynamic permissions list
const permissions = reactive([
  {
    id: 1,
    name: 'Read Access',
    description: 'Can view content',
    enabled: true
  },
  {
    id: 2,
    name: 'Write Access',
    description: 'Can create and edit content',
    enabled: false
  },
  {
    id: 3,
    name: 'Delete Access',
    description: 'Can delete content',
    enabled: false
  },
  {
    id: 4,
    name: 'Admin Access',
    description: 'Full administrative privileges',
    enabled: false
  }
])

// Methods
const saveSettings = () => {
  console.log('Saving settings:', settings)
  // Here you would typically make an API call
  alert('Settings saved successfully!')
}

const updatePermission = (id, enabled) => {
  console.log(`Permission ${id} ${enabled ? 'granted' : 'denied'}`)
  // Here you would typically make an API call to update the permission
}
</script>
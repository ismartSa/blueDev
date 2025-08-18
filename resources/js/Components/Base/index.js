// Base Components Export
// This file provides centralized exports for all base components
// following the DRY principle and improving import organization

// Core UI Components
export { default as Icon } from './Icon.vue'
export { default as Button } from './Button.vue'
export { default as Input } from './Input.vue'
export { default as Card } from './Card.vue'
export { default as Badge } from './Badge.vue'

// Modal Components
export { default as BaseModal } from './BaseModal.vue'

// Dropdown Components
export { default as Dropdown } from './Dropdown.vue'
export { default as DropdownItem } from './DropdownItem.vue'

// Stats Components
export { default as StatsCard } from './StatsCard.vue'

// Video Components
export { default as VideoPlayer } from './VideoPlayer.vue'

// Component Groups for easier bulk imports
export const FormComponents = {
  Input,
  Button
}

export const DropdownComponents = {
  Dropdown,
  DropdownItem
}

export const UIComponents = {
  Icon,
  Button,
  Input,
  Card,
  Badge,
  BaseModal,
  StatsCard
}

// Default export for convenience
export default {
  Icon,
  Button,
  Input,
  Card,
  Badge,
  BaseModal,
  Dropdown,
  DropdownItem,
  StatsCard,
  VideoPlayer
}
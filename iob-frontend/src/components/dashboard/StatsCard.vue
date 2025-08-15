<template>
  <div class="card">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <component :is="iconComponent" :class="iconClasses" />
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
          <dd class="text-lg font-medium text-gray-900">{{ value }}</dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  DocumentTextIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  color: {
    type: String,
    default: 'blue'
  }
})

const iconComponent = computed(() => {
  const icons = {
    DocumentTextIcon,
    CurrencyDollarIcon,
    ClockIcon,
    CheckCircleIcon
  }
  return icons[props.icon] || DocumentTextIcon
})

const iconClasses = computed(() => {
  const colorClasses = {
    blue: 'text-blue-400',
    green: 'text-green-400',
    yellow: 'text-yellow-400',
    red: 'text-red-400'
  }
  return `h-8 w-8 ${colorClasses[props.color] || colorClasses.blue}`
})
</script>

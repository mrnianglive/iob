<template>
  <div class="overflow-hidden">
    <div v-if="operations.length === 0" class="text-center py-8 text-gray-500">
      Aucune opération récente
    </div>
    <div v-else class="flow-root">
      <ul role="list" class="-my-5 divide-y divide-gray-200">
        <li v-for="operation in operations" :key="operation.id" class="py-4">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
              <div :class="getStatusColor(operation.status)" class="h-8 w-8 rounded-full flex items-center justify-center">
                <component :is="getStatusIcon(operation.status)" class="h-5 w-5 text-white" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ operation.reference }}
              </p>
              <p class="text-sm text-gray-500">
                {{ formatCurrency(operation.amount) }} - {{ operation.type }}
              </p>
            </div>
            <div class="flex-shrink-0 text-sm text-gray-500">
              {{ formatDate(operation.createdAt) }}
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { 
  CheckCircleIcon, 
  ClockIcon, 
  XCircleIcon,
  DocumentTextIcon 
} from '@heroicons/vue/24/solid'

const props = defineProps({
  operations: {
    type: Array,
    default: () => []
  }
})

const getStatusColor = (status) => {
  const colors = {
    'completed': 'bg-green-500',
    'pending': 'bg-yellow-500',
    'cancelled': 'bg-red-500',
    'processing': 'bg-blue-500'
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusIcon = (status) => {
  const icons = {
    'completed': CheckCircleIcon,
    'pending': ClockIcon,
    'cancelled': XCircleIcon,
    'processing': DocumentTextIcon
  }
  return icons[status] || DocumentTextIcon
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}
</script>

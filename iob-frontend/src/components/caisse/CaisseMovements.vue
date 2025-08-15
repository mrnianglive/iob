<template>
  <div class="overflow-hidden">
    <div v-if="movements.length === 0" class="text-center py-8 text-gray-500">
      Aucun mouvement récent
    </div>
    <div v-else class="flow-root">
      <ul role="list" class="-my-5 divide-y divide-gray-200">
        <li v-for="movement in movements" :key="movement.id" class="py-4">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
              <div :class="getMovementColor(movement.type)" class="h-8 w-8 rounded-full flex items-center justify-center">
                <component :is="getMovementIcon(movement.type)" class="h-5 w-5 text-white" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ movement.description }}
              </p>
              <p class="text-sm text-gray-500">
                {{ movement.reference }}
              </p>
            </div>
            <div class="flex-shrink-0 text-right">
              <p :class="getAmountColor(movement.type)" class="text-sm font-medium">
                {{ formatAmount(movement.amount, movement.type) }}
              </p>
              <p class="text-sm text-gray-500">
                {{ formatDate(movement.createdAt) }}
              </p>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { 
  ArrowUpIcon, 
  ArrowDownIcon, 
  ArrowRightIcon 
} from '@heroicons/vue/24/solid'

const props = defineProps({
  movements: {
    type: Array,
    default: () => []
  }
})

const getMovementColor = (type) => {
  const colors = {
    'deposit': 'bg-green-500',
    'withdrawal': 'bg-red-500',
    'transfer_in': 'bg-blue-500',
    'transfer_out': 'bg-orange-500'
  }
  return colors[type] || 'bg-gray-500'
}

const getMovementIcon = (type) => {
  const icons = {
    'deposit': ArrowUpIcon,
    'withdrawal': ArrowDownIcon,
    'transfer_in': ArrowRightIcon,
    'transfer_out': ArrowRightIcon
  }
  return icons[type] || ArrowRightIcon
}

const getAmountColor = (type) => {
  return type === 'deposit' || type === 'transfer_in' 
    ? 'text-green-600' 
    : 'text-red-600'
}

const formatAmount = (amount, type) => {
  const prefix = (type === 'deposit' || type === 'transfer_in') ? '+' : '-'
  return `${prefix}${new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(Math.abs(amount))}`
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

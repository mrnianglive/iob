<template>
  <div class="overflow-hidden">
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>
    
    <div v-else-if="data.length === 0" class="text-center py-8 text-gray-500">
      Aucune donnée disponible
    </div>
    
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Période
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Volume
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Opérations
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Taux réussite
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Évolution
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="row in data" :key="row.period" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatPeriod(row.period) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatCurrency(row.volume) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ row.operations }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span :class="getSuccessRateColor(row.successRate)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ row.successRate }}%
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <div class="flex items-center">
                <component :is="getTrendIcon(row.trend)" :class="getTrendColor(row.trend)" class="h-4 w-4 mr-1" />
                <span :class="getTrendColor(row.trend)">{{ Math.abs(row.trendValue) }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { 
  ArrowUpIcon, 
  ArrowDownIcon, 
  MinusIcon 
} from '@heroicons/vue/24/solid'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  loading: Boolean
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const formatPeriod = (period) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(period))
}

const getSuccessRateColor = (rate) => {
  if (rate >= 95) return 'bg-green-100 text-green-800'
  if (rate >= 85) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getTrendIcon = (trend) => {
  if (trend === 'up') return ArrowUpIcon
  if (trend === 'down') return ArrowDownIcon
  return MinusIcon
}

const getTrendColor = (trend) => {
  if (trend === 'up') return 'text-green-600'
  if (trend === 'down') return 'text-red-600'
  return 'text-gray-600'
}
</script>

<template>
  <div>
    <span v-if="format === 'currency'" class="font-medium">
      {{ formatCurrency(value) }}
    </span>
    <span v-else-if="format === 'date'" class="text-gray-600">
      {{ formatDate(value) }}
    </span>
    <span v-else-if="format === 'badge'" :class="getBadgeClasses(value)">
      {{ getBadgeText(value) }}
    </span>
    <span v-else>{{ value }}</span>
  </div>
</template>

<script setup>
const props = defineProps({
  value: [String, Number, Date],
  format: String
})

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
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const getBadgeClasses = (status) => {
  const classes = {
    'pending': 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800',
    'completed': 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800',
    'cancelled': 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800',
    'processing': 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800'
  }
  return classes[status] || 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800'
}

const getBadgeText = (status) => {
  const texts = {
    'pending': 'En attente',
    'completed': 'Terminé',
    'cancelled': 'Annulé',
    'processing': 'En cours'
  }
  return texts[status] || status
}
</script>

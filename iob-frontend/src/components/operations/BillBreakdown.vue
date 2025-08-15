<template>
  <div class="card">
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('operations.billBreakdown') }}</h3>
    
    <div class="space-y-3">
      <div v-for="denomination in denominations" :key="denomination.value" class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <span class="text-sm font-medium text-gray-700 w-16">{{ denomination.label }}</span>
          <input
            v-model.number="breakdown[denomination.value]"
            type="number"
            min="0"
            class="form-input w-20 text-center"
            @input="updateBreakdown"
          />
        </div>
        <span class="text-sm text-gray-600">
          {{ formatCurrency(denomination.value * (breakdown[denomination.value] || 0)) }}
        </span>
      </div>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex justify-between items-center">
        <span class="text-sm font-medium text-gray-900">Total calculé:</span>
        <span class="text-lg font-bold text-gray-900">{{ formatCurrency(calculatedTotal) }}</span>
      </div>
      <div v-if="totalAmount && calculatedTotal !== totalAmount" class="mt-2">
        <div class="flex justify-between items-center text-sm">
          <span class="text-gray-600">Montant attendu:</span>
          <span class="text-gray-900">{{ formatCurrency(totalAmount) }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
          <span class="text-red-600">Différence:</span>
          <span class="text-red-600 font-medium">{{ formatCurrency(Math.abs(calculatedTotal - totalAmount)) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  totalAmount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:modelValue'])

const denominations = [
  { value: 500, label: '500€' },
  { value: 200, label: '200€' },
  { value: 100, label: '100€' },
  { value: 50, label: '50€' },
  { value: 20, label: '20€' },
  { value: 10, label: '10€' },
  { value: 5, label: '5€' },
  { value: 2, label: '2€' },
  { value: 1, label: '1€' },
  { value: 0.5, label: '0.50€' },
  { value: 0.2, label: '0.20€' },
  { value: 0.1, label: '0.10€' },
  { value: 0.05, label: '0.05€' }
]

const breakdown = ref({ ...props.modelValue })

const calculatedTotal = computed(() => {
  return denominations.reduce((total, denom) => {
    return total + (denom.value * (breakdown.value[denom.value] || 0))
  }, 0)
})

const updateBreakdown = () => {
  emit('update:modelValue', { ...breakdown.value })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

watch(() => props.modelValue, (newValue) => {
  breakdown.value = { ...newValue }
}, { deep: true })
</script>

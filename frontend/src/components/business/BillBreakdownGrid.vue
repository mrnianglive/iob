<template>
  <div class="bill-breakdown-grid">
    <h3 class="text-lg font-semibold mb-4">Détail du Billetage</h3>
    
    <!-- Bills Section -->
    <div class="mb-6">
      <h4 class="text-md font-medium mb-3 text-gray-700">Billets</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="bill in bills"
          :key="bill.key"
          class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex-1">
            <label :for="`bill-${bill.key}`" class="block text-sm font-medium text-gray-700">
              {{ formatCurrency(bill.value) }}
            </label>
          </div>
          <div class="w-24">
            <input
              :id="`bill-${bill.key}`"
              v-model.number="breakdown[bill.key]"
              type="number"
              min="0"
              class="input-field text-right"
              @input="updateTotal"
            />
          </div>
          <div class="w-32 text-right text-sm text-gray-600">
            = {{ formatCurrency(bill.value * (breakdown[bill.key] || 0)) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Coins Section -->
    <div class="mb-6">
      <h4 class="text-md font-medium mb-3 text-gray-700">Pièces</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div
          v-for="coin in coins"
          :key="coin.key"
          class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex-1">
            <label :for="`coin-${coin.key}`" class="block text-sm font-medium text-gray-700">
              {{ formatCurrency(coin.value) }}
            </label>
          </div>
          <div class="w-20">
            <input
              :id="`coin-${coin.key}`"
              v-model.number="breakdown[coin.key]"
              type="number"
              min="0"
              class="input-field text-right"
              @input="updateTotal"
            />
          </div>
          <div class="w-24 text-right text-sm text-gray-600">
            = {{ formatCurrency(coin.value * (breakdown[coin.key] || 0)) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Total Section -->
    <div class="mt-6 p-4 bg-primary-50 rounded-lg">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-600">Total Billetage</p>
          <p class="text-2xl font-bold text-primary-900">{{ formatCurrency(totalBilletage) }}</p>
        </div>
        <div v-if="expectedAmount" class="text-right">
          <p class="text-sm text-gray-600">Montant Attendu</p>
          <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(expectedAmount) }}</p>
        </div>
      </div>
      
      <!-- Validation Status -->
      <div v-if="expectedAmount" class="mt-3">
        <div v-if="isValid" class="flex items-center text-success-600">
          <CheckCircleIcon class="h-5 w-5 mr-2" />
          <span class="text-sm font-medium">Billetage correct</span>
        </div>
        <div v-else class="flex items-center text-danger-600">
          <ExclamationCircleIcon class="h-5 w-5 mr-2" />
          <span class="text-sm font-medium">
            Différence: {{ formatCurrency(Math.abs(totalBilletage - expectedAmount)) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-4 flex space-x-3">
      <button
        @click="clearAll"
        class="btn-outline"
      >
        <XMarkIcon class="h-4 w-4 mr-2" />
        Effacer tout
      </button>
      <button
        v-if="expectedAmount"
        @click="autoCalculate"
        class="btn-secondary"
      >
        <CalculatorIcon class="h-4 w-4 mr-2" />
        Calcul automatique
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  XMarkIcon,
  CalculatorIcon,
} from '@heroicons/vue/24/outline';

interface BillBreakdown {
  bills10000?: number;
  bills5000?: number;
  bills2000?: number;
  bills1000?: number;
  bills500?: number;
  bills250?: number;
  bills200?: number;
  bills100?: number;
  coins50?: number;
  coins25?: number;
  coins10?: number;
  coins5?: number;
  coins1?: number;
}

interface Props {
  modelValue?: BillBreakdown;
  expectedAmount?: number;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => ({}),
  expectedAmount: 0,
});

const emit = defineEmits<{
  'update:modelValue': [value: BillBreakdown];
  'total-changed': [total: number];
}>();

// Bills configuration
const bills = [
  { key: 'bills10000', value: 10000 },
  { key: 'bills5000', value: 5000 },
  { key: 'bills2000', value: 2000 },
  { key: 'bills1000', value: 1000 },
  { key: 'bills500', value: 500 },
  { key: 'bills250', value: 250 },
  { key: 'bills200', value: 200 },
  { key: 'bills100', value: 100 },
];

// Coins configuration
const coins = [
  { key: 'coins50', value: 50 },
  { key: 'coins25', value: 25 },
  { key: 'coins10', value: 10 },
  { key: 'coins5', value: 5 },
  { key: 'coins1', value: 1 },
];

// Local breakdown state
const breakdown = ref<BillBreakdown>({ ...props.modelValue });

// Computed total
const totalBilletage = computed(() => {
  let total = 0;
  
  bills.forEach(bill => {
    total += bill.value * (breakdown.value[bill.key as keyof BillBreakdown] || 0);
  });
  
  coins.forEach(coin => {
    total += coin.value * (breakdown.value[coin.key as keyof BillBreakdown] || 0);
  });
  
  return total;
});

// Validation status
const isValid = computed(() => {
  if (!props.expectedAmount) return true;
  return Math.abs(totalBilletage.value - props.expectedAmount) < 1;
});

// Methods
function updateTotal() {
  emit('update:modelValue', { ...breakdown.value });
  emit('total-changed', totalBilletage.value);
}

function clearAll() {
  breakdown.value = {};
  updateTotal();
}

function autoCalculate() {
  if (!props.expectedAmount) return;
  
  let remaining = props.expectedAmount;
  const newBreakdown: BillBreakdown = {};
  
  // Calculate bills
  bills.forEach(bill => {
    const count = Math.floor(remaining / bill.value);
    if (count > 0) {
      newBreakdown[bill.key as keyof BillBreakdown] = count;
      remaining -= count * bill.value;
    }
  });
  
  // Calculate coins
  coins.forEach(coin => {
    const count = Math.floor(remaining / coin.value);
    if (count > 0) {
      newBreakdown[coin.key as keyof BillBreakdown] = count;
      remaining -= count * coin.value;
    }
  });
  
  breakdown.value = newBreakdown;
  updateTotal();
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  breakdown.value = { ...newValue };
}, { deep: true });
</script>

<style scoped>
.bill-breakdown-grid {
  @apply bg-white rounded-lg p-6;
}
</style>
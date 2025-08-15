<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('caisse.title') }}</h1>
        </div>

        <!-- Balance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">{{ $t('caisse.balance') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(balance.current) }}</p>
              </div>
              <CurrencyDollarIcon class="h-8 w-8 text-green-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Entrées du jour</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(balance.todayIn) }}</p>
              </div>
              <ArrowUpIcon class="h-8 w-8 text-blue-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Sorties du jour</p>
                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(balance.todayOut) }}</p>
              </div>
              <ArrowDownIcon class="h-8 w-8 text-red-400" />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Quick Actions -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Actions rapides</h3>
            <div class="grid grid-cols-2 gap-4">
              <button
                @click="openTransferModal"
                class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors"
              >
                <ArrowRightIcon class="h-8 w-8 text-gray-400 mb-2" />
                <span class="text-sm font-medium text-gray-900">{{ $t('caisse.transfer') }}</span>
              </button>
              
              <button
                @click="openDepositModal"
                class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors"
              >
                <PlusIcon class="h-8 w-8 text-gray-400 mb-2" />
                <span class="text-sm font-medium text-gray-900">{{ $t('caisse.deposit') }}</span>
              </button>
            </div>
          </div>

          <!-- Recent Movements -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('caisse.movements') }}</h3>
            <CaisseMovements :movements="movements" />
          </div>
        </div>
      </div>
    </main>

    <!-- Transfer Modal -->
    <TransferModal
      v-if="showTransferModal"
      @close="showTransferModal = false"
      @transfer="handleTransfer"
    />

    <!-- Deposit Modal -->
    <DepositModal
      v-if="showDepositModal"
      @close="showDepositModal = false"
      @deposit="handleDeposit"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { 
  CurrencyDollarIcon, 
  ArrowUpIcon, 
  ArrowDownIcon,
  ArrowRightIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import CaisseMovements from '../../components/caisse/CaisseMovements.vue'
import TransferModal from '../../components/caisse/TransferModal.vue'
import DepositModal from '../../components/caisse/DepositModal.vue'
import axios from 'axios'

const balance = ref({
  current: 0,
  todayIn: 0,
  todayOut: 0
})

const movements = ref([])
const showTransferModal = ref(false)
const showDepositModal = ref(false)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const fetchCaisseData = async () => {
  try {
    const [balanceResponse, movementsResponse] = await Promise.all([
      axios.get('/partner-api/caisse/balance'),
      axios.get('/partner-api/caisse/movements')
    ])
    
    balance.value = balanceResponse.data
    movements.value = movementsResponse.data
  } catch (error) {
    console.error('Erreur lors du chargement des données de caisse:', error)
  }
}

const openTransferModal = () => {
  showTransferModal.value = true
}

const openDepositModal = () => {
  showDepositModal.value = true
}

const handleTransfer = async (transferData) => {
  try {
    await axios.post('/partner-api/caisse/transfer', transferData)
    showTransferModal.value = false
    fetchCaisseData()
  } catch (error) {
    console.error('Erreur lors du transfert:', error)
  }
}

const handleDeposit = async (depositData) => {
  try {
    await axios.post('/partner-api/caisse/deposit', depositData)
    showDepositModal.value = false
    fetchCaisseData()
  } catch (error) {
    console.error('Erreur lors du dépôt:', error)
  }
}

onMounted(() => {
  fetchCaisseData()
})
</script>

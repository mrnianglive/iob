<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-4">
            <li>
              <router-link to="/caisse" class="text-gray-400 hover:text-gray-500">
                <ChevronLeftIcon class="h-5 w-5" />
                {{ $t('caisse.backToOverview') }}
              </router-link>
            </li>
          </ol>
        </nav>
        <div class="mt-4">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('caisse.details') }}</h1>
          <p class="mt-2 text-sm text-gray-600">{{ $t('caisse.detailsDescription') }}</p>
        </div>
      </div>

      <!-- Balance Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyEuroIcon class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('caisse.currentBalance') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(balance.current) }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowUpIcon class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('caisse.todayIn') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(balance.todayIn) }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowDownIcon class="h-8 w-8 text-red-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('caisse.todayOut') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(balance.todayOut) }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('caisse.quickActions') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            @click="showTransferModal = true"
            class="flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <ArrowRightIcon class="h-5 w-5 mr-2" />
            {{ $t('caisse.transfer') }}
          </button>
          <button
            @click="showDepositModal = true"
            class="flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            <PlusIcon class="h-5 w-5 mr-2" />
            {{ $t('caisse.deposit') }}
          </button>
        </div>
      </div>

      <!-- Movements Table -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">{{ $t('caisse.recentMovements') }}</h2>
            <div class="flex space-x-3">
              <select
                v-model="selectedPeriod"
                @change="fetchMovements"
                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="today">{{ $t('caisse.today') }}</option>
                <option value="week">{{ $t('caisse.thisWeek') }}</option>
                <option value="month">{{ $t('caisse.thisMonth') }}</option>
              </select>
              <button
                @click="exportMovements"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                {{ $t('common.export') }}
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('caisse.date') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('caisse.type') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('caisse.description') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('caisse.amount') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('caisse.balance') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="movement in movements" :key="movement.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(movement.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <component :is="getMovementIcon(movement.type)" :class="getMovementIconClass(movement.type)" class="h-5 w-5 mr-2" />
                    <span class="text-sm text-gray-900">{{ $t(`caisse.movementType.${movement.type}`) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ movement.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getAmountClass(movement.amount)">
                  {{ formatCurrency(movement.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(movement.balance) }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="movements.length === 0" class="text-center py-12">
            <CurrencyEuroIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('caisse.noMovements') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('caisse.noMovementsDescription') }}</p>
          </div>
        </div>
      </div>

      <!-- Transfer Modal -->
      <TransferModal
        :show="showTransferModal"
        @close="showTransferModal = false"
        @success="handleTransferSuccess"
      />

      <!-- Deposit Modal -->
      <DepositModal
        :show="showDepositModal"
        @close="showDepositModal = false"
        @success="handleDepositSuccess"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import {
  ChevronLeftIcon,
  CurrencyEuroIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ArrowRightIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  BanknotesIcon,
  ArrowsRightLeftIcon
} from '@heroicons/vue/24/outline'
import TransferModal from '@/components/caisse/TransferModal.vue'
import DepositModal from '@/components/caisse/DepositModal.vue'

export default {
  name: 'CaisseDetailsView',
  components: {
    ChevronLeftIcon,
    CurrencyEuroIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowRightIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    BanknotesIcon,
    ArrowsRightLeftIcon,
    TransferModal,
    DepositModal
  },
  setup() {
    const { t } = useI18n()
    
    const balance = ref({
      current: 0,
      todayIn: 0,
      todayOut: 0
    })
    const movements = ref([])
    const selectedPeriod = ref('today')
    const showTransferModal = ref(false)
    const showDepositModal = ref(false)
    const loading = ref(true)

    const fetchBalance = async () => {
      try {
        const response = await axios.get('/partner-api/caisse/balance')
        balance.value = response.data
      } catch (error) {
        console.error('Error fetching balance:', error)
      }
    }

    const fetchMovements = async () => {
      try {
        const response = await axios.get('/partner-api/caisse/movements', {
          params: { period: selectedPeriod.value }
        })
        movements.value = response.data.movements || []
      } catch (error) {
        console.error('Error fetching movements:', error)
      }
    }

    const handleTransferSuccess = () => {
      showTransferModal.value = false
      fetchBalance()
      fetchMovements()
    }

    const handleDepositSuccess = () => {
      showDepositModal.value = false
      fetchBalance()
      fetchMovements()
    }

    const exportMovements = () => {
      // TODO: Implement export functionality
      console.log('Export movements for period:', selectedPeriod.value)
    }

    const getMovementIcon = (type) => {
      const icons = {
        deposit: PlusIcon,
        withdrawal: ArrowDownIcon,
        transfer_in: ArrowRightIcon,
        transfer_out: ArrowRightIcon
      }
      return icons[type] || BanknotesIcon
    }

    const getMovementIconClass = (type) => {
      const classes = {
        deposit: 'text-green-600',
        withdrawal: 'text-red-600',
        transfer_in: 'text-blue-600',
        transfer_out: 'text-orange-600'
      }
      return classes[type] || 'text-gray-600'
    }

    const getAmountClass = (amount) => {
      return amount >= 0 ? 'text-green-600' : 'text-red-600'
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(amount)
    }

    const formatDateTime = (date) => {
      return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(date))
    }

    onMounted(async () => {
      loading.value = true
      await Promise.all([fetchBalance(), fetchMovements()])
      loading.value = false
    })

    return {
      balance,
      movements,
      selectedPeriod,
      showTransferModal,
      showDepositModal,
      loading,
      fetchMovements,
      handleTransferSuccess,
      handleDepositSuccess,
      exportMovements,
      getMovementIcon,
      getMovementIconClass,
      getAmountClass,
      formatCurrency,
      formatDateTime
    }
  }
}
</script>

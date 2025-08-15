<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('remittance.title') }}</h1>
          <button
            @click="showCreateModal = true"
            class="btn-primary"
          >
            {{ $t('remittance.create') }}
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Remises en cours</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.pending }}</p>
              </div>
              <ClockIcon class="h-8 w-8 text-blue-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Montant total</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.totalAmount) }}</p>
              </div>
              <CurrencyDollarIcon class="h-8 w-8 text-green-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Remises du jour</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats.today }}</p>
              </div>
              <DocumentTextIcon class="h-8 w-8 text-purple-400" />
            </div>
          </div>
        </div>

        <!-- Remittances Table -->
        <div class="card">
          <DataTable
            :columns="columns"
            :data="remittances"
            :loading="isLoading"
            @row-click="viewRemittance"
          />
        </div>
      </div>
    </main>

    <!-- Create Remittance Modal -->
    <CreateRemittanceModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="handleRemittanceCreated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ClockIcon, 
  CurrencyDollarIcon, 
  DocumentTextIcon 
} from '@heroicons/vue/24/outline'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import DataTable from '../../components/ui/DataTable.vue'
import CreateRemittanceModal from '../../components/remittance/CreateRemittanceModal.vue'
import axios from 'axios'

const router = useRouter()

const remittances = ref([])
const stats = ref({
  pending: 0,
  totalAmount: 0,
  today: 0
})
const isLoading = ref(false)
const showCreateModal = ref(false)

const columns = [
  { key: 'reference', label: 'Référence', sortable: true },
  { key: 'sourceAgency', label: 'Agence source', sortable: true },
  { key: 'destinationAgency', label: 'Agence destination', sortable: true },
  { key: 'amount', label: 'Montant', sortable: true, format: 'currency' },
  { key: 'status', label: 'Statut', sortable: true, format: 'badge' },
  { key: 'createdAt', label: 'Date', sortable: true, format: 'date' }
]

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const fetchRemittances = async () => {
  try {
    isLoading.value = true
    const [remittancesResponse, statsResponse] = await Promise.all([
      axios.get('/partner-api/remittance'),
      axios.get('/partner-api/remittance/stats')
    ])
    
    remittances.value = remittancesResponse.data.remittances
    stats.value = statsResponse.data
  } catch (error) {
    console.error('Erreur lors du chargement des remises:', error)
  } finally {
    isLoading.value = false
  }
}

const viewRemittance = (remittance) => {
  router.push(`/remittance/${remittance.id}`)
}

const handleRemittanceCreated = () => {
  showCreateModal.value = false
  fetchRemittances()
}

onMounted(() => {
  fetchRemittances()
})
</script>

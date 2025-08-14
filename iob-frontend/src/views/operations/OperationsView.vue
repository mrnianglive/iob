<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('operations.title') }}</h1>
          <router-link
            to="/operations/create"
            class="btn-primary"
          >
            {{ $t('operations.create') }}
          </router-link>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('operations.type') }}</label>
              <select v-model="filters.type" class="form-input">
                <option value="">Tous les types</option>
                <option value="deposit">Dépôt</option>
                <option value="withdrawal">Retrait</option>
                <option value="transfer">Transfert</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('operations.status') }}</label>
              <select v-model="filters.status" class="form-input">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="completed">Terminé</option>
                <option value="cancelled">Annulé</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
              <input v-model="filters.startDate" type="date" class="form-input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
              <input v-model="filters.endDate" type="date" class="form-input" />
            </div>
          </div>
        </div>

        <!-- Operations Table -->
        <div class="card">
          <DataTable
            :columns="columns"
            :data="operations"
            :loading="isLoading"
            @row-click="viewOperation"
          />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import DataTable from '../../components/ui/DataTable.vue'
import axios from 'axios'

const router = useRouter()

const operations = ref([])
const isLoading = ref(false)
const filters = ref({
  type: '',
  status: '',
  startDate: '',
  endDate: ''
})

const columns = [
  { key: 'reference', label: 'Référence', sortable: true },
  { key: 'type', label: 'Type', sortable: true },
  { key: 'amount', label: 'Montant', sortable: true, format: 'currency' },
  { key: 'status', label: 'Statut', sortable: true, format: 'badge' },
  { key: 'createdAt', label: 'Date', sortable: true, format: 'date' }
]

const fetchOperations = async () => {
  try {
    isLoading.value = true
    const params = new URLSearchParams()
    
    Object.entries(filters.value).forEach(([key, value]) => {
      if (value) params.append(key, value)
    })
    
    const response = await axios.get(`/partner-api/operations?${params}`)
    operations.value = response.data.operations
  } catch (error) {
    console.error('Erreur lors du chargement des opérations:', error)
  } finally {
    isLoading.value = false
  }
}

const viewOperation = (operation) => {
  router.push(`/operations/${operation.id}`)
}

watch(filters, fetchOperations, { deep: true })

onMounted(() => {
  fetchOperations()
})
</script>

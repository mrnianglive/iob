<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('analytics.title') }}</h1>
          <div class="flex space-x-3">
            <select v-model="selectedPeriod" class="form-input">
              <option value="7d">7 derniers jours</option>
              <option value="30d">30 derniers jours</option>
              <option value="90d">90 derniers jours</option>
              <option value="1y">1 an</option>
            </select>
            <button @click="exportData" class="btn-secondary">
              {{ $t('analytics.export') }}
            </button>
          </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Volume total</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(kpis.totalVolume) }}</p>
              </div>
              <TrendingUpIcon class="h-8 w-8 text-blue-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Nombre d'opérations</p>
                <p class="text-2xl font-bold text-green-600">{{ kpis.totalOperations }}</p>
              </div>
              <DocumentTextIcon class="h-8 w-8 text-green-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Montant moyen</p>
                <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(kpis.averageAmount) }}</p>
              </div>
              <CalculatorIcon class="h-8 w-8 text-purple-400" />
            </div>
          </div>
          
          <div class="card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Taux de réussite</p>
                <p class="text-2xl font-bold text-emerald-600">{{ kpis.successRate }}%</p>
              </div>
              <CheckCircleIcon class="h-8 w-8 text-emerald-400" />
            </div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Volume Chart -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution du volume</h3>
            <VolumeChart :data="volumeChartData" />
          </div>

          <!-- Operations by Type -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par type</h3>
            <PieChart :data="typeChartData" />
          </div>
        </div>

        <!-- Detailed Reports -->
        <div class="card">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ $t('analytics.reports') }}</h3>
            <button @click="generateReport" class="btn-primary">
              Générer rapport
            </button>
          </div>
          <AnalyticsTable :data="reportData" :loading="isLoading" />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { 
  TrendingUpIcon, 
  DocumentTextIcon, 
  CalculatorIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import VolumeChart from '../../components/analytics/VolumeChart.vue'
import PieChart from '../../components/analytics/PieChart.vue'
import AnalyticsTable from '../../components/analytics/AnalyticsTable.vue'
import axios from 'axios'

const selectedPeriod = ref('30d')
const isLoading = ref(false)

const kpis = ref({
  totalVolume: 0,
  totalOperations: 0,
  averageAmount: 0,
  successRate: 0
})

const volumeChartData = ref({
  labels: [],
  datasets: []
})

const typeChartData = ref({
  labels: [],
  datasets: []
})

const reportData = ref([])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const fetchAnalyticsData = async () => {
  try {
    isLoading.value = true
    
    const [kpisResponse, volumeResponse, typeResponse, reportResponse] = await Promise.all([
      axios.get(`/partner-api/analytics/kpis?period=${selectedPeriod.value}`),
      axios.get(`/partner-api/analytics/volume-chart?period=${selectedPeriod.value}`),
      axios.get(`/partner-api/analytics/type-chart?period=${selectedPeriod.value}`),
      axios.get(`/partner-api/analytics/report?period=${selectedPeriod.value}`)
    ])
    
    kpis.value = kpisResponse.data
    volumeChartData.value = volumeResponse.data
    typeChartData.value = typeResponse.data
    reportData.value = reportResponse.data
  } catch (error) {
    console.error('Erreur lors du chargement des analytics:', error)
  } finally {
    isLoading.value = false
  }
}

const exportData = async () => {
  try {
    const response = await axios.get(`/partner-api/analytics/export?period=${selectedPeriod.value}`, {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `analytics-${selectedPeriod.value}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Erreur lors de l\'export:', error)
  }
}

const generateReport = async () => {
  try {
    const response = await axios.post('/partner-api/analytics/generate-report', {
      period: selectedPeriod.value
    })
    
    // Handle report generation
    console.log('Rapport généré:', response.data)
  } catch (error) {
    console.error('Erreur lors de la génération du rapport:', error)
  }
}

watch(selectedPeriod, fetchAnalyticsData)

onMounted(() => {
  fetchAnalyticsData()
})
</script>

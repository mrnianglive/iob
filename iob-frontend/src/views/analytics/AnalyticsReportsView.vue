<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('analytics.reports') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $t('analytics.reportsDescription') }}</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="generateReport"
              :disabled="generating"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
            >
              <DocumentChartBarIcon class="h-4 w-4 mr-2" />
              {{ generating ? $t('analytics.generating') : $t('analytics.generateReport') }}
            </button>
            <button
              @click="exportData"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
              {{ $t('common.export') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('analytics.filters') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('analytics.period') }}</label>
            <select
              v-model="selectedPeriod"
              @change="fetchData"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="7d">{{ $t('analytics.last7Days') }}</option>
              <option value="30d">{{ $t('analytics.last30Days') }}</option>
              <option value="90d">{{ $t('analytics.last90Days') }}</option>
              <option value="1y">{{ $t('analytics.lastYear') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('analytics.agency') }}</label>
            <select
              v-model="selectedAgency"
              @change="fetchData"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">{{ $t('analytics.allAgencies') }}</option>
              <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                {{ agency.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('analytics.product') }}</label>
            <select
              v-model="selectedProduct"
              @change="fetchData"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">{{ $t('analytics.allProducts') }}</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('analytics.reportType') }}</label>
            <select
              v-model="selectedReportType"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="summary">{{ $t('analytics.summary') }}</option>
              <option value="detailed">{{ $t('analytics.detailed') }}</option>
              <option value="comparative">{{ $t('analytics.comparative') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- KPIs Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyEuroIcon class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('analytics.totalVolume') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(kpis.totalVolume) }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <DocumentTextIcon class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('analytics.totalOperations') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ kpis.totalOperations.toLocaleString() }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CalculatorIcon class="h-8 w-8 text-purple-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('analytics.averageAmount') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(kpis.averageAmount) }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('analytics.successRate') }}</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ kpis.successRate }}%</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Volume Chart -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('analytics.volumeEvolution') }}</h3>
          <VolumeChart :data="volumeChartData" />
        </div>

        <!-- Type Distribution Chart -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('analytics.typeDistribution') }}</h3>
          <PieChart :data="typeChartData" />
        </div>
      </div>

      <!-- Detailed Report Table -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">{{ $t('analytics.detailedReport') }}</h2>
        </div>
        <AnalyticsTable :data="reportData" :loading="loading" />
      </div>

      <!-- Generated Reports History -->
      <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">{{ $t('analytics.reportsHistory') }}</h2>
            <button
              @click="refreshHistory"
              class="text-sm text-blue-600 hover:text-blue-500"
            >
              {{ $t('common.refresh') }}
            </button>
          </div>
        </div>
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('analytics.reportName') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('analytics.period') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('analytics.generatedAt') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('analytics.status') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="report in reportsHistory" :key="report.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ report.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ report.period }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(report.generatedAt) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getReportStatusClass(report.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ $t(`analytics.reportStatus.${report.status}`) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="report.status === 'completed'"
                    @click="downloadReport(report)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    {{ $t('common.download') }}
                  </button>
                  <button
                    @click="deleteReport(report.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ $t('common.delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="reportsHistory.length === 0" class="text-center py-12">
            <DocumentChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('analytics.noReports') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('analytics.noReportsDescription') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import {
  DocumentChartBarIcon,
  DocumentArrowDownIcon,
  CurrencyEuroIcon,
  DocumentTextIcon,
  CalculatorIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import VolumeChart from '@/components/analytics/VolumeChart.vue'
import PieChart from '@/components/analytics/PieChart.vue'
import AnalyticsTable from '@/components/analytics/AnalyticsTable.vue'

export default {
  name: 'AnalyticsReportsView',
  components: {
    DocumentChartBarIcon,
    DocumentArrowDownIcon,
    CurrencyEuroIcon,
    DocumentTextIcon,
    CalculatorIcon,
    CheckCircleIcon,
    VolumeChart,
    PieChart,
    AnalyticsTable
  },
  setup() {
    const { t } = useI18n()
    
    const selectedPeriod = ref('30d')
    const selectedAgency = ref('')
    const selectedProduct = ref('')
    const selectedReportType = ref('summary')
    const loading = ref(false)
    const generating = ref(false)
    
    const kpis = ref({
      totalVolume: 0,
      totalOperations: 0,
      averageAmount: 0,
      successRate: 0
    })
    
    const volumeChartData = ref({})
    const typeChartData = ref({})
    const reportData = ref([])
    const agencies = ref([])
    const products = ref([])
    const reportsHistory = ref([])

    const fetchData = async () => {
      loading.value = true
      try {
        const [kpisResponse, volumeResponse, typeResponse, reportResponse] = await Promise.all([
          axios.get('/partner-api/analytics/kpis', {
            params: {
              period: selectedPeriod.value,
              agencyId: selectedAgency.value || undefined,
              productId: selectedProduct.value || undefined
            }
          }),
          axios.get('/partner-api/analytics/volume-chart', {
            params: { period: selectedPeriod.value }
          }),
          axios.get('/partner-api/analytics/type-chart', {
            params: { period: selectedPeriod.value }
          }),
          axios.get('/partner-api/analytics/report', {
            params: { period: selectedPeriod.value }
          })
        ])

        kpis.value = kpisResponse.data
        volumeChartData.value = volumeResponse.data
        typeChartData.value = typeResponse.data
        reportData.value = reportResponse.data
      } catch (error) {
        console.error('Error fetching analytics data:', error)
      } finally {
        loading.value = false
      }
    }

    const fetchAgencies = async () => {
      try {
        const response = await axios.get('/partner-api/agencies')
        agencies.value = response.data.agencies || []
      } catch (error) {
        console.error('Error fetching agencies:', error)
      }
    }

    const fetchProducts = async () => {
      try {
        const response = await axios.get('/partner-api/products')
        products.value = response.data.products || []
      } catch (error) {
        console.error('Error fetching products:', error)
      }
    }

    const fetchReportsHistory = async () => {
      try {
        const response = await axios.get('/partner-api/analytics/reports-history')
        reportsHistory.value = response.data.reports || []
      } catch (error) {
        console.error('Error fetching reports history:', error)
      }
    }

    const generateReport = async () => {
      generating.value = true
      try {
        await axios.post('/partner-api/analytics/generate-report', {
          period: selectedPeriod.value,
          agencyId: selectedAgency.value || undefined,
          productId: selectedProduct.value || undefined,
          reportType: selectedReportType.value
        })
        
        // Refresh reports history
        await fetchReportsHistory()
      } catch (error) {
        console.error('Error generating report:', error)
      } finally {
        generating.value = false
      }
    }

    const exportData = async () => {
      try {
        const response = await axios.get('/partner-api/analytics/export', {
          params: { period: selectedPeriod.value },
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
        console.error('Error exporting data:', error)
      }
    }

    const downloadReport = (report) => {
      // TODO: Implement report download
      console.log('Download report:', report.id)
    }

    const deleteReport = async (reportId) => {
      try {
        await axios.delete(`/partner-api/analytics/reports/${reportId}`)
        await fetchReportsHistory()
      } catch (error) {
        console.error('Error deleting report:', error)
      }
    }

    const refreshHistory = () => {
      fetchReportsHistory()
    }

    const getReportStatusClass = (status) => {
      const classes = {
        processing: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(amount)
    }

    const formatDate = (date) => {
      return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(date))
    }

    onMounted(async () => {
      await Promise.all([
        fetchData(),
        fetchAgencies(),
        fetchProducts(),
        fetchReportsHistory()
      ])
    })

    return {
      selectedPeriod,
      selectedAgency,
      selectedProduct,
      selectedReportType,
      loading,
      generating,
      kpis,
      volumeChartData,
      typeChartData,
      reportData,
      agencies,
      products,
      reportsHistory,
      fetchData,
      generateReport,
      exportData,
      downloadReport,
      deleteReport,
      refreshHistory,
      getReportStatusClass,
      formatCurrency,
      formatDate
    }
  }
}
</script>

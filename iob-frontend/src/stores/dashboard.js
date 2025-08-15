import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref({
    todayOperations: 0,
    totalAmount: 0,
    pendingOperations: 0,
    completedOperations: 0
  })
  
  const recentOperations = ref([])
  const chartData = ref({
    labels: [],
    datasets: []
  })
  
  const isLoading = ref(false)
  const error = ref(null)

  const fetchDashboardData = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      const [statsResponse, operationsResponse, chartResponse] = await Promise.all([
        axios.get('/partner-api/dashboard/stats'),
        axios.get('/partner-api/dashboard/recent-operations'),
        axios.get('/partner-api/dashboard/chart-data')
      ])
      
      stats.value = statsResponse.data
      recentOperations.value = operationsResponse.data
      chartData.value = chartResponse.data
      
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement du dashboard'
    } finally {
      isLoading.value = false
    }
  }

  const refreshStats = async () => {
    try {
      const response = await axios.get('/partner-api/dashboard/stats')
      stats.value = response.data
    } catch (err) {
      console.error('Erreur lors du rafraîchissement des stats:', err)
    }
  }

  return {
    stats,
    recentOperations,
    chartData,
    isLoading,
    error,
    fetchDashboardData,
    refreshStats
  }
})

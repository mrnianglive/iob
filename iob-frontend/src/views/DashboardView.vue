<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('dashboard.title') }}</h1>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <StatsCard
            :title="$t('dashboard.todayOperations')"
            :value="dashboardStore.stats.todayOperations"
            icon="DocumentTextIcon"
            color="blue"
          />
          <StatsCard
            :title="$t('dashboard.totalAmount')"
            :value="formatCurrency(dashboardStore.stats.totalAmount)"
            icon="CurrencyDollarIcon"
            color="green"
          />
          <StatsCard
            :title="$t('dashboard.pendingOperations')"
            :value="dashboardStore.stats.pendingOperations"
            icon="ClockIcon"
            color="yellow"
          />
          <StatsCard
            :title="$t('dashboard.completedOperations')"
            :value="dashboardStore.stats.completedOperations"
            icon="CheckCircleIcon"
            color="green"
          />
        </div>

        <!-- Charts and Recent Operations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Chart -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des opérations</h3>
            <RealtimeChart :data="dashboardStore.chartData" />
          </div>

          <!-- Recent Operations -->
          <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('dashboard.recentOperations') }}</h3>
            <RecentOperations :operations="dashboardStore.recentOperations" />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useDashboardStore } from '../stores/dashboard'
import AppNavbar from '../components/layout/AppNavbar.vue'
import StatsCard from '../components/dashboard/StatsCard.vue'
import RealtimeChart from '../components/dashboard/RealtimeChart.vue'
import RecentOperations from '../components/dashboard/RecentOperations.vue'

const dashboardStore = useDashboardStore()

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

onMounted(() => {
  dashboardStore.fetchDashboardData()
})
</script>

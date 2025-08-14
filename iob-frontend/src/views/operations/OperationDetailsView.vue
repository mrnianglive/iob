<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-4">
            <li>
              <router-link to="/operations" class="text-gray-400 hover:text-gray-500">
                <ChevronLeftIcon class="h-5 w-5" />
                {{ $t('operations.backToList') }}
              </router-link>
            </li>
          </ol>
        </nav>
        <div class="mt-4 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('operations.details') }}</h1>
            <p class="mt-2 text-sm text-gray-600" v-if="operation">
              {{ $t('operations.reference') }}: {{ operation.reference }}
            </p>
          </div>
          <div class="flex space-x-3" v-if="operation">
            <button
              v-if="operation.status === 'pending' && canApprove"
              @click="approveOperation"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
            >
              <CheckIcon class="h-4 w-4 mr-2" />
              {{ $t('operations.approve') }}
            </button>
            <button
              v-if="operation.status === 'pending' && canApprove"
              @click="rejectOperation"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
            >
              <XMarkIcon class="h-4 w-4 mr-2" />
              {{ $t('operations.reject') }}
            </button>
            <button
              @click="exportOperation"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
              {{ $t('common.export') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ $t('common.error') }}</h3>
            <p class="mt-2 text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Operation Details -->
      <div v-else-if="operation" class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-medium text-gray-900">{{ $t('operations.status') }}</h2>
              <div class="mt-2 flex items-center">
                <span :class="getStatusClass(operation.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ $t(`operations.status.${operation.status}`) }}
                </span>
                <span class="ml-4 text-sm text-gray-500">
                  {{ $t('operations.createdAt') }}: {{ formatDate(operation.createdAt) }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(operation.amount) }}</p>
              <p class="text-sm text-gray-500">{{ $t('operations.commission') }}: {{ formatCurrency(operation.commission) }}</p>
            </div>
          </div>
        </div>

        <!-- Client Information -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('operations.clientInfo') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.clientName') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.clientName }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.clientPhone') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.clientPhone }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.beneficiaryName') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.beneficiaryName }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.beneficiaryPhone') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.beneficiaryPhone }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.beneficiaryCountry') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.beneficiaryCountry }}</dd>
            </div>
          </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('operations.transactionDetails') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.agency') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.agency?.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.product') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.product?.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.operator') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.operator?.name }}</dd>
            </div>
            <div v-if="operation.validator">
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.validator') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ operation.validator.name }}</dd>
            </div>
            <div v-if="operation.validatedAt">
              <dt class="text-sm font-medium text-gray-500">{{ $t('operations.validatedAt') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(operation.validatedAt) }}</dd>
            </div>
          </div>
        </div>

        <!-- Bill Breakdown -->
        <div v-if="operation.billBreakdown" class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('operations.billBreakdown') }}</h3>
          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('operations.denomination') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('operations.quantity') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('operations.total') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="bill in operation.billBreakdown" :key="bill.denomination">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(bill.denomination) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ bill.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(bill.denomination * bill.quantity) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('operations.timeline') }}</h3>
          <div class="flow-root">
            <ul class="-mb-8">
              <li v-for="(event, index) in timeline" :key="index">
                <div class="relative pb-8" :class="{ 'pb-0': index === timeline.length - 1 }">
                  <span v-if="index !== timeline.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span :class="event.iconClass" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                        <component :is="event.icon" class="h-5 w-5 text-white" />
                      </span>
                    </div>
                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                      <div>
                        <p class="text-sm text-gray-500">{{ event.description }}</p>
                        <p v-if="event.user" class="text-xs text-gray-400">{{ $t('operations.by') }} {{ event.user }}</p>
                      </div>
                      <div class="text-right text-sm whitespace-nowrap text-gray-500">
                        {{ formatDate(event.date) }}
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import {
  ChevronLeftIcon,
  CheckIcon,
  XMarkIcon,
  DocumentArrowDownIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'OperationDetailsView',
  components: {
    ChevronLeftIcon,
    CheckIcon,
    XMarkIcon,
    DocumentArrowDownIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const { t } = useI18n()
    
    const operation = ref(null)
    const loading = ref(true)
    const error = ref(null)
    const canApprove = ref(true) // TODO: Check user permissions

    const timeline = computed(() => {
      if (!operation.value) return []
      
      const events = [
        {
          description: t('operations.created'),
          date: operation.value.createdAt,
          user: operation.value.operator?.name,
          icon: PlusIcon,
          iconClass: 'bg-blue-500'
        }
      ]

      if (operation.value.status === 'approved' && operation.value.validatedAt) {
        events.push({
          description: t('operations.approved'),
          date: operation.value.validatedAt,
          user: operation.value.validator?.name,
          icon: CheckCircleIcon,
          iconClass: 'bg-green-500'
        })
      } else if (operation.value.status === 'rejected' && operation.value.validatedAt) {
        events.push({
          description: t('operations.rejected'),
          date: operation.value.validatedAt,
          user: operation.value.validator?.name,
          icon: XCircleIcon,
          iconClass: 'bg-red-500'
        })
      } else if (operation.value.status === 'pending') {
        events.push({
          description: t('operations.pendingApproval'),
          date: operation.value.createdAt,
          icon: ClockIcon,
          iconClass: 'bg-yellow-500'
        })
      }

      return events
    })

    const fetchOperation = async () => {
      try {
        loading.value = true
        const response = await axios.get(`/partner-api/operations/${route.params.id}`)
        operation.value = response.data
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      } finally {
        loading.value = false
      }
    }

    const approveOperation = async () => {
      try {
        await axios.put(`/partner-api/operations/${route.params.id}/approve`)
        await fetchOperation()
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      }
    }

    const rejectOperation = async () => {
      try {
        await axios.put(`/partner-api/operations/${route.params.id}/reject`)
        await fetchOperation()
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      }
    }

    const exportOperation = () => {
      // TODO: Implement export functionality
      console.log('Export operation:', operation.value.id)
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800'
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(date))
    }

    onMounted(() => {
      fetchOperation()
    })

    return {
      operation,
      loading,
      error,
      canApprove,
      timeline,
      approveOperation,
      rejectOperation,
      exportOperation,
      getStatusClass,
      formatCurrency,
      formatDate
    }
  }
}
</script>

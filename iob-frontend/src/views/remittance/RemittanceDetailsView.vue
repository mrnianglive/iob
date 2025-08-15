<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-4">
            <li>
              <router-link to="/remittance" class="text-gray-400 hover:text-gray-500">
                <ChevronLeftIcon class="h-5 w-5" />
                {{ $t('remittance.backToList') }}
              </router-link>
            </li>
          </ol>
        </nav>
        <div class="mt-4 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('remittance.details') }}</h1>
            <p class="mt-2 text-sm text-gray-600" v-if="remittance">
              {{ $t('remittance.reference') }}: {{ remittance.reference }}
            </p>
          </div>
          <div class="flex space-x-3" v-if="remittance">
            <button
              v-if="remittance.status === 'pending' && canValidate"
              @click="validateRemittance"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
            >
              <CheckIcon class="h-4 w-4 mr-2" />
              {{ $t('remittance.validate') }}
            </button>
            <button
              v-if="remittance.status === 'pending' && canValidate"
              @click="cancelRemittance"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
            >
              <XMarkIcon class="h-4 w-4 mr-2" />
              {{ $t('remittance.cancel') }}
            </button>
            <button
              @click="printRemittance"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              <PrinterIcon class="h-4 w-4 mr-2" />
              {{ $t('common.print') }}
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

      <!-- Remittance Details -->
      <div v-else-if="remittance" class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-medium text-gray-900">{{ $t('remittance.status') }}</h2>
              <div class="mt-2 flex items-center">
                <span :class="getStatusClass(remittance.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ $t(`remittance.status.${remittance.status}`) }}
                </span>
                <span class="ml-4 text-sm text-gray-500">
                  {{ $t('remittance.createdAt') }}: {{ formatDate(remittance.createdAt) }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(remittance.amount) }}</p>
              <p class="text-sm text-gray-500">{{ remittance.currency }}</p>
            </div>
          </div>
        </div>

        <!-- Transfer Information -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('remittance.transferInfo') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Source Agency -->
            <div class="border-r border-gray-200 pr-8">
              <h4 class="text-sm font-medium text-gray-500 mb-3">{{ $t('remittance.sourceAgency') }}</h4>
              <div class="space-y-2">
                <p class="text-lg font-medium text-gray-900">{{ remittance.sourceAgency.name }}</p>
                <p class="text-sm text-gray-600">{{ remittance.sourceAgency.partner }}</p>
                <div class="flex items-center text-sm text-gray-500">
                  <BuildingOfficeIcon class="h-4 w-4 mr-1" />
                  {{ $t('remittance.agencyId') }}: {{ remittance.sourceAgency.id }}
                </div>
              </div>
            </div>

            <!-- Destination Agency -->
            <div class="pl-8">
              <h4 class="text-sm font-medium text-gray-500 mb-3">{{ $t('remittance.destinationAgency') }}</h4>
              <div class="space-y-2">
                <p class="text-lg font-medium text-gray-900">{{ remittance.destinationAgency.name }}</p>
                <p class="text-sm text-gray-600">{{ remittance.destinationAgency.partner }}</p>
                <div class="flex items-center text-sm text-gray-500">
                  <BuildingOfficeIcon class="h-4 w-4 mr-1" />
                  {{ $t('remittance.agencyId') }}: {{ remittance.destinationAgency.id }}
                </div>
              </div>
            </div>
          </div>

          <!-- Transfer Arrow -->
          <div class="flex justify-center my-6">
            <ArrowRightIcon class="h-8 w-8 text-blue-600" />
          </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('remittance.transactionDetails') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.product') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ remittance.product.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.creator') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ remittance.creator.name }}</dd>
            </div>
            <div v-if="remittance.validator">
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.validator') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ remittance.validator.name }}</dd>
            </div>
            <div v-if="remittance.validatedAt">
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.validatedAt') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(remittance.validatedAt) }}</dd>
            </div>
            <div v-if="remittance.phoneNumber">
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.phoneNumber') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ remittance.phoneNumber }}</dd>
            </div>
            <div v-if="remittance.fullName">
              <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.fullName') }}</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ remittance.fullName }}</dd>
            </div>
          </div>
          <div v-if="remittance.description" class="mt-6">
            <dt class="text-sm font-medium text-gray-500">{{ $t('remittance.description') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ remittance.description }}</dd>
          </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('remittance.timeline') }}</h3>
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
                        <p v-if="event.user" class="text-xs text-gray-400">{{ $t('remittance.by') }} {{ event.user }}</p>
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
  PrinterIcon,
  ExclamationTriangleIcon,
  BuildingOfficeIcon,
  ArrowRightIcon,
  PlusIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'RemittanceDetailsView',
  components: {
    ChevronLeftIcon,
    CheckIcon,
    XMarkIcon,
    PrinterIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
    ArrowRightIcon,
    PlusIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const { t } = useI18n()
    
    const remittance = ref(null)
    const loading = ref(true)
    const error = ref(null)
    const canValidate = ref(true) // TODO: Check user permissions

    const timeline = computed(() => {
      if (!remittance.value) return []
      
      const events = [
        {
          description: t('remittance.created'),
          date: remittance.value.createdAt,
          user: remittance.value.creator?.name,
          icon: PlusIcon,
          iconClass: 'bg-blue-500'
        }
      ]

      if (remittance.value.status === 'validated' && remittance.value.validatedAt) {
        events.push({
          description: t('remittance.validated'),
          date: remittance.value.validatedAt,
          user: remittance.value.validator?.name,
          icon: CheckCircleIcon,
          iconClass: 'bg-green-500'
        })
      } else if (remittance.value.status === 'cancelled' && remittance.value.validatedAt) {
        events.push({
          description: t('remittance.cancelled'),
          date: remittance.value.validatedAt,
          user: remittance.value.validator?.name,
          icon: XCircleIcon,
          iconClass: 'bg-red-500'
        })
      } else if (remittance.value.status === 'pending') {
        events.push({
          description: t('remittance.pendingValidation'),
          date: remittance.value.createdAt,
          icon: ClockIcon,
          iconClass: 'bg-yellow-500'
        })
      }

      return events
    })

    const fetchRemittance = async () => {
      try {
        loading.value = true
        const response = await axios.get(`/partner-api/remittance/${route.params.id}`)
        remittance.value = response.data
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      } finally {
        loading.value = false
      }
    }

    const validateRemittance = async () => {
      try {
        await axios.put(`/partner-api/remittance/${route.params.id}/validate`)
        await fetchRemittance()
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      }
    }

    const cancelRemittance = async () => {
      try {
        await axios.put(`/partner-api/remittance/${route.params.id}/cancel`)
        await fetchRemittance()
      } catch (err) {
        error.value = err.response?.data?.error || t('common.errorOccurred')
      }
    }

    const printRemittance = () => {
      window.print()
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        validated: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
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
      fetchRemittance()
    })

    return {
      remittance,
      loading,
      error,
      canValidate,
      timeline,
      validateRemittance,
      cancelRemittance,
      printRemittance,
      getStatusClass,
      formatCurrency,
      formatDate
    }
  }
}
</script>

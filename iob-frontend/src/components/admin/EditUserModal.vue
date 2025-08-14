<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                  <PencilIcon class="h-6 w-6 text-blue-600" />
                </div>
                <div class="mt-3 text-center sm:mt-5">
                  <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                    {{ $t('admin.editUser') }}
                  </DialogTitle>
                </div>
              </div>

              <form @submit.prevent="updateUser" class="mt-6" v-if="form">
                <div class="space-y-4">
                  <!-- Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.name') }} *
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :placeholder="$t('admin.namePlaceholder')"
                    />
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.email') }} *
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :placeholder="$t('admin.emailPlaceholder')"
                    />
                  </div>

                  <!-- Password -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.newPassword') }}
                    </label>
                    <input
                      v-model="form.password"
                      type="password"
                      minlength="8"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :placeholder="$t('admin.passwordPlaceholder')"
                    />
                    <p class="mt-1 text-xs text-gray-500">{{ $t('admin.leaveEmptyToKeepPassword') }}</p>
                  </div>

                  <!-- Role -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.role') }} *
                    </label>
                    <select
                      v-model="form.role"
                      required
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                      <option value="">{{ $t('admin.selectRole') }}</option>
                      <option value="admin">{{ $t('admin.roles.admin') }}</option>
                      <option value="operator">{{ $t('admin.roles.operator') }}</option>
                      <option value="viewer">{{ $t('admin.roles.viewer') }}</option>
                    </select>
                  </div>

                  <!-- Agency -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.agency') }}
                    </label>
                    <select
                      v-model="form.agencyId"
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                      <option value="">{{ $t('admin.selectAgency') }}</option>
                      <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                        {{ agency.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('admin.phone') }}
                    </label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :placeholder="$t('admin.phonePlaceholder')"
                    />
                  </div>

                  <!-- Active Status -->
                  <div class="flex items-center">
                    <input
                      v-model="form.isActive"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label class="ml-2 block text-sm text-gray-900">
                      {{ $t('admin.activeUser') }}
                    </label>
                  </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">{{ $t('common.error') }}</h3>
                      <p class="mt-2 text-sm text-red-700">{{ error }}</p>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                  >
                    {{ $t('common.cancel') }}
                  </button>
                  <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50"
                  >
                    {{ loading ? $t('common.updating') : $t('common.update') }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot
} from '@headlessui/vue'
import {
  PencilIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'EditUserModal',
  components: {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
    PencilIcon,
    ExclamationTriangleIcon
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
    agencies: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const { t } = useI18n()
    
    const form = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const initializeForm = () => {
      if (props.user) {
        form.value = {
          name: props.user.name || '',
          email: props.user.email || '',
          password: '',
          role: props.user.role || '',
          agencyId: props.user.agency?.id || '',
          phone: props.user.phone || '',
          isActive: props.user.isActive !== undefined ? props.user.isActive : true
        }
      }
      error.value = null
    }

    const updateUser = async () => {
      if (!props.user || !form.value) return

      loading.value = true
      error.value = null

      try {
        const payload = {
          ...form.value,
          agencyId: form.value.agencyId ? parseInt(form.value.agencyId) : null
        }

        // Remove password if empty
        if (!payload.password) {
          delete payload.password
        }

        await axios.put(`/partner-api/admin/users/${props.user.id}`, payload)
        emit('success')
      } catch (err) {
        error.value = err.response?.data?.error || t('admin.errorUpdatingUser')
      } finally {
        loading.value = false
      }
    }

    // Initialize form when user prop changes
    watch(() => props.user, () => {
      if (props.user) {
        initializeForm()
      }
    }, { immediate: true })

    // Reset error when modal is closed
    watch(() => props.show, (newValue) => {
      if (!newValue) {
        error.value = null
      }
    })

    return {
      form,
      loading,
      error,
      updateUser
    }
  }
}
</script>

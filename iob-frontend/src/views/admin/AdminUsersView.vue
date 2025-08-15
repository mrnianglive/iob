<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('admin.users') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $t('admin.usersDescription') }}</p>
          </div>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            {{ $t('admin.createUser') }}
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('admin.searchUsers') }}</label>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('admin.searchPlaceholder')"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('admin.role') }}</label>
            <select
              v-model="selectedRole"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">{{ $t('admin.allRoles') }}</option>
              <option value="admin">{{ $t('admin.roles.admin') }}</option>
              <option value="operator">{{ $t('admin.roles.operator') }}</option>
              <option value="viewer">{{ $t('admin.roles.viewer') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('admin.agency') }}</label>
            <select
              v-model="selectedAgency"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">{{ $t('admin.allAgencies') }}</option>
              <option v-for="agency in agencies" :key="agency.id" :value="agency.id">
                {{ agency.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('admin.status') }}</label>
            <select
              v-model="selectedStatus"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">{{ $t('admin.allStatuses') }}</option>
              <option value="active">{{ $t('admin.active') }}</option>
              <option value="inactive">{{ $t('admin.inactive') }}</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            {{ $t('common.resetFilters') }}
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              {{ $t('admin.usersList') }} ({{ filteredUsers.length }})
            </h2>
            <div class="flex space-x-3">
              <button
                @click="exportUsers"
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
                  {{ $t('admin.user') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('admin.role') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('admin.agency') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('admin.lastLogin') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('admin.status') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in paginatedUsers" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <UserIcon class="h-6 w-6 text-gray-600" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getRoleClass(user.role)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ $t(`admin.roles.${user.role}`) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.agency?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ user.lastLogin ? formatDate(user.lastLogin) : $t('admin.neverLoggedIn') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(user.isActive)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ user.isActive ? $t('admin.active') : $t('admin.inactive') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editUser(user)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      {{ $t('common.edit') }}
                    </button>
                    <button
                      @click="toggleUserStatus(user)"
                      :class="user.isActive ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                    >
                      {{ user.isActive ? $t('admin.deactivate') : $t('admin.activate') }}
                    </button>
                    <button
                      @click="deleteUser(user)"
                      class="text-red-600 hover:text-red-900"
                    >
                      {{ $t('common.delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="filteredUsers.length === 0" class="text-center py-12">
            <UserIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('admin.noUsers') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('admin.noUsersDescription') }}</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              {{ $t('common.previous') }}
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              {{ $t('common.next') }}
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                {{ $t('common.showing') }}
                <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                {{ $t('common.to') }}
                <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }}</span>
                {{ $t('common.of') }}
                <span class="font-medium">{{ filteredUsers.length }}</span>
                {{ $t('common.results') }}
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="currentPage--"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronLeftIcon class="h-5 w-5" />
                </button>
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="currentPage = page"
                  :class="[
                    page === currentPage
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="currentPage++"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronRightIcon class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Create User Modal -->
      <CreateUserModal
        :show="showCreateModal"
        :agencies="agencies"
        @close="showCreateModal = false"
        @success="handleUserCreated"
      />

      <!-- Edit User Modal -->
      <EditUserModal
        :show="showEditModal"
        :user="selectedUser"
        :agencies="agencies"
        @close="showEditModal = false"
        @success="handleUserUpdated"
      />
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import {
  PlusIcon,
  DocumentArrowDownIcon,
  UserIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import CreateUserModal from '@/components/admin/CreateUserModal.vue'
import EditUserModal from '@/components/admin/EditUserModal.vue'

export default {
  name: 'AdminUsersView',
  components: {
    PlusIcon,
    DocumentArrowDownIcon,
    UserIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CreateUserModal,
    EditUserModal
  },
  setup() {
    const { t } = useI18n()
    
    const users = ref([])
    const agencies = ref([])
    const loading = ref(true)
    const searchQuery = ref('')
    const selectedRole = ref('')
    const selectedAgency = ref('')
    const selectedStatus = ref('')
    const currentPage = ref(1)
    const itemsPerPage = ref(20)
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const selectedUser = ref(null)

    const filteredUsers = computed(() => {
      let filtered = users.value

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(user =>
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query)
        )
      }

      if (selectedRole.value) {
        filtered = filtered.filter(user => user.role === selectedRole.value)
      }

      if (selectedAgency.value) {
        filtered = filtered.filter(user => user.agency?.id === parseInt(selectedAgency.value))
      }

      if (selectedStatus.value) {
        const isActive = selectedStatus.value === 'active'
        filtered = filtered.filter(user => user.isActive === isActive)
      }

      return filtered
    })

    const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value))

    const paginatedUsers = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredUsers.value.slice(start, end)
    })

    const visiblePages = computed(() => {
      const pages = []
      const start = Math.max(1, currentPage.value - 2)
      const end = Math.min(totalPages.value, currentPage.value + 2)
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    })

    const fetchUsers = async () => {
      try {
        loading.value = true
        const response = await axios.get('/partner-api/admin/users')
        users.value = response.data.users || []
      } catch (error) {
        console.error('Error fetching users:', error)
      } finally {
        loading.value = false
      }
    }

    const fetchAgencies = async () => {
      try {
        const response = await axios.get('/partner-api/admin/agencies')
        agencies.value = response.data.agencies || []
      } catch (error) {
        console.error('Error fetching agencies:', error)
      }
    }

    const editUser = (user) => {
      selectedUser.value = user
      showEditModal.value = true
    }

    const toggleUserStatus = async (user) => {
      try {
        await axios.put(`/partner-api/admin/users/${user.id}`, {
          isActive: !user.isActive
        })
        await fetchUsers()
      } catch (error) {
        console.error('Error toggling user status:', error)
      }
    }

    const deleteUser = async (user) => {
      if (confirm(t('admin.confirmDeleteUser', { name: user.name }))) {
        try {
          await axios.delete(`/partner-api/admin/users/${user.id}`)
          await fetchUsers()
        } catch (error) {
          console.error('Error deleting user:', error)
        }
      }
    }

    const handleUserCreated = () => {
      showCreateModal.value = false
      fetchUsers()
    }

    const handleUserUpdated = () => {
      showEditModal.value = false
      selectedUser.value = null
      fetchUsers()
    }

    const resetFilters = () => {
      searchQuery.value = ''
      selectedRole.value = ''
      selectedAgency.value = ''
      selectedStatus.value = ''
      currentPage.value = 1
    }

    const exportUsers = () => {
      // TODO: Implement export functionality
      console.log('Export users')
    }

    const getRoleClass = (role) => {
      const classes = {
        admin: 'bg-purple-100 text-purple-800',
        operator: 'bg-blue-100 text-blue-800',
        viewer: 'bg-gray-100 text-gray-800'
      }
      return classes[role] || 'bg-gray-100 text-gray-800'
    }

    const getStatusClass = (isActive) => {
      return isActive
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800'
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

    // Reset pagination when filters change
    watch([searchQuery, selectedRole, selectedAgency, selectedStatus], () => {
      currentPage.value = 1
    })

    onMounted(async () => {
      await Promise.all([fetchUsers(), fetchAgencies()])
    })

    return {
      users,
      agencies,
      loading,
      searchQuery,
      selectedRole,
      selectedAgency,
      selectedStatus,
      currentPage,
      itemsPerPage,
      showCreateModal,
      showEditModal,
      selectedUser,
      filteredUsers,
      totalPages,
      paginatedUsers,
      visiblePages,
      editUser,
      toggleUserStatus,
      deleteUser,
      handleUserCreated,
      handleUserUpdated,
      resetFilters,
      exportUsers,
      getRoleClass,
      getStatusClass,
      formatDate
    }
  }
}
</script>

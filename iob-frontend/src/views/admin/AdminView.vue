<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.title') }}</h1>
        </div>

        <!-- Admin Tabs -->
        <div class="mb-8">
          <nav class="flex space-x-8" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="card">
          <!-- Users Tab -->
          <div v-if="activeTab === 'users'">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.users') }}</h3>
              <button @click="showCreateUserModal = true" class="btn-primary">
                Créer utilisateur
              </button>
            </div>
            <UsersTable :users="users" @edit="editUser" @delete="deleteUser" />
          </div>

          <!-- Agencies Tab -->
          <div v-if="activeTab === 'agencies'">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.agencies') }}</h3>
              <button @click="showCreateAgencyModal = true" class="btn-primary">
                Créer agence
              </button>
            </div>
            <AgenciesTable :agencies="agencies" @edit="editAgency" @delete="deleteAgency" />
          </div>

          <!-- Products Tab -->
          <div v-if="activeTab === 'products'">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.products') }}</h3>
              <button @click="showCreateProductModal = true" class="btn-primary">
                Créer produit
              </button>
            </div>
            <ProductsTable :products="products" @edit="editProduct" @delete="deleteProduct" />
          </div>

          <!-- Permissions Tab -->
          <div v-if="activeTab === 'permissions'">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">{{ $t('admin.permissions') }}</h3>
            </div>
            <PermissionsMatrix :permissions="permissions" @update="updatePermissions" />
          </div>
        </div>
      </div>
    </main>

    <!-- Modals -->
    <CreateUserModal
      v-if="showCreateUserModal"
      @close="showCreateUserModal = false"
      @created="handleUserCreated"
    />
    
    <CreateAgencyModal
      v-if="showCreateAgencyModal"
      @close="showCreateAgencyModal = false"
      @created="handleAgencyCreated"
    />
    
    <CreateProductModal
      v-if="showCreateProductModal"
      @close="showCreateProductModal = false"
      @created="handleProductCreated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppNavbar from '../../components/layout/AppNavbar.vue'
import UsersTable from '../../components/admin/UsersTable.vue'
import AgenciesTable from '../../components/admin/AgenciesTable.vue'
import ProductsTable from '../../components/admin/ProductsTable.vue'
import PermissionsMatrix from '../../components/admin/PermissionsMatrix.vue'
import CreateUserModal from '../../components/admin/CreateUserModal.vue'
import CreateAgencyModal from '../../components/admin/CreateAgencyModal.vue'
import CreateProductModal from '../../components/admin/CreateProductModal.vue'
import axios from 'axios'

const activeTab = ref('users')
const users = ref([])
const agencies = ref([])
const products = ref([])
const permissions = ref([])

const showCreateUserModal = ref(false)
const showCreateAgencyModal = ref(false)
const showCreateProductModal = ref(false)

const tabs = [
  { id: 'users', name: 'Utilisateurs' },
  { id: 'agencies', name: 'Agences' },
  { id: 'products', name: 'Produits' },
  { id: 'permissions', name: 'Permissions' }
]

const fetchUsers = async () => {
  try {
    const response = await axios.get('/partner-api/admin/users')
    users.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
  }
}

const fetchAgencies = async () => {
  try {
    const response = await axios.get('/partner-api/admin/agencies')
    agencies.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des agences:', error)
  }
}

const fetchProducts = async () => {
  try {
    const response = await axios.get('/partner-api/admin/products')
    products.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
  }
}

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/partner-api/admin/permissions')
    permissions.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des permissions:', error)
  }
}

const editUser = (user) => {
  // Handle user edit
  console.log('Edit user:', user)
}

const deleteUser = async (userId) => {
  try {
    await axios.delete(`/partner-api/admin/users/${userId}`)
    fetchUsers()
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const editAgency = (agency) => {
  console.log('Edit agency:', agency)
}

const deleteAgency = async (agencyId) => {
  try {
    await axios.delete(`/partner-api/admin/agencies/${agencyId}`)
    fetchAgencies()
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const editProduct = (product) => {
  console.log('Edit product:', product)
}

const deleteProduct = async (productId) => {
  try {
    await axios.delete(`/partner-api/admin/products/${productId}`)
    fetchProducts()
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const updatePermissions = async (permissionData) => {
  try {
    await axios.put('/partner-api/admin/permissions', permissionData)
    fetchPermissions()
  } catch (error) {
    console.error('Erreur lors de la mise à jour des permissions:', error)
  }
}

const handleUserCreated = () => {
  showCreateUserModal.value = false
  fetchUsers()
}

const handleAgencyCreated = () => {
  showCreateAgencyModal.value = false
  fetchAgencies()
}

const handleProductCreated = () => {
  showCreateProductModal.value = false
  fetchProducts()
}

onMounted(() => {
  fetchUsers()
  fetchAgencies()
  fetchProducts()
  fetchPermissions()
})
</script>

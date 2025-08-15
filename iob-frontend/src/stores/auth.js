import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const isLoading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // Configure axios defaults
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  const login = async (credentials) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await axios.post('/partner-api/auth/login', credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur de connexion'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = () => {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await axios.get('/partner-api/auth/me')
      user.value = response.data.user
    } catch (err) {
      logout()
    }
  }

  const verifyTwoFactor = async (code) => {
    try {
      isLoading.value = true
      const response = await axios.post('/partner-api/auth/verify-2fa', { code })
      
      token.value = response.data.token
      user.value = response.data.user
      
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Code invalide'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    fetchUser,
    verifyTwoFactor
  }
})

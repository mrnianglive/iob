import { ref, onMounted, onUnmounted } from 'vue'
import { io } from 'socket.io-client'
import { useAuthStore } from '@/stores/auth'

export function useWebSocket() {
  const socket = ref(null)
  const connected = ref(false)
  const authStore = useAuthStore()

  const connect = () => {
    if (!authStore.token) return

    socket.value = io(import.meta.env.VITE_API_URL || 'http://localhost:3001', {
      path: '/partner-socket.io',
      auth: {
        token: authStore.token
      },
      transports: ['websocket', 'polling']
    })

    socket.value.on('connect', () => {
      connected.value = true
      console.log('WebSocket connected')
    })

    socket.value.on('disconnect', () => {
      connected.value = false
      console.log('WebSocket disconnected')
    })

    socket.value.on('connect_error', (error) => {
      console.error('WebSocket connection error:', error)
      connected.value = false
    })
  }

  const disconnect = () => {
    if (socket.value) {
      socket.value.disconnect()
      socket.value = null
      connected.value = false
    }
  }

  const emit = (event, data) => {
    if (socket.value && connected.value) {
      socket.value.emit(event, data)
    }
  }

  const on = (event, callback) => {
    if (socket.value) {
      socket.value.on(event, callback)
    }
  }

  const off = (event, callback) => {
    if (socket.value) {
      socket.value.off(event, callback)
    }
  }

  // Dashboard subscriptions
  const subscribeToDashboard = () => {
    emit('subscribe_dashboard')
  }

  const unsubscribeFromDashboard = () => {
    emit('unsubscribe_dashboard')
  }

  // Operations subscriptions
  const subscribeToOperations = () => {
    emit('subscribe_operations')
  }

  const unsubscribeFromOperations = () => {
    emit('unsubscribe_operations')
  }

  // Caisse subscriptions
  const subscribeToCaisse = (caisseId) => {
    emit('subscribe_caisse', caisseId)
  }

  const unsubscribeFromCaisse = (caisseId) => {
    emit('unsubscribe_caisse', caisseId)
  }

  onMounted(() => {
    if (authStore.isAuthenticated) {
      connect()
    }
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    socket,
    connected,
    connect,
    disconnect,
    emit,
    on,
    off,
    subscribeToDashboard,
    unsubscribeFromDashboard,
    subscribeToOperations,
    unsubscribeFromOperations,
    subscribeToCaisse,
    unsubscribeFromCaisse
  }
}

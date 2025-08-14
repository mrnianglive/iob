import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { authService } from '@/services/auth.service';
import type { User, LoginCredentials } from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter();
  const toast = useToast();

  // State
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token'));
  const refreshToken = ref<string | null>(localStorage.getItem('refreshToken'));
  const loading = ref(false);
  const require2FA = ref(false);
  const tempToken = ref<string | null>(null);

  // Computed
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isAdmin = computed(() => 
    user.value?.permissions?.some(p => ['admin', 'super_admin'].includes(p)) || false
  );
  const userCountry = computed(() => user.value?.country);
  const userPartner = computed(() => user.value?.partner);
  const userCashRegisters = computed(() => user.value?.cashRegisters || []);

  // Actions
  async function login(credentials: LoginCredentials) {
    loading.value = true;
    try {
      const response = await authService.login(credentials);
      
      if (response.require2FA) {
        require2FA.value = true;
        tempToken.value = response.tempToken;
        toast.info('Code de vérification à deux facteurs requis');
        return { require2FA: true };
      }

      // Store tokens
      token.value = response.accessToken;
      refreshToken.value = response.refreshToken;
      user.value = response.user;

      // Save to localStorage
      localStorage.setItem('token', response.accessToken);
      localStorage.setItem('refreshToken', response.refreshToken);
      localStorage.setItem('user', JSON.stringify(response.user));

      toast.success(`Bienvenue ${response.user.name} !`);
      
      // Redirect to dashboard
      await router.push('/dashboard');
      
      return { success: true };
    } catch (error: any) {
      toast.error(error.message || 'Erreur de connexion');
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function verify2FA(code: string) {
    if (!tempToken.value) {
      throw new Error('Token temporaire manquant');
    }

    loading.value = true;
    try {
      const response = await authService.verify2FA(tempToken.value, code);

      // Store tokens
      token.value = response.accessToken;
      refreshToken.value = response.refreshToken;
      user.value = response.user;

      // Save to localStorage
      localStorage.setItem('token', response.accessToken);
      localStorage.setItem('refreshToken', response.refreshToken);
      localStorage.setItem('user', JSON.stringify(response.user));

      // Reset 2FA state
      require2FA.value = false;
      tempToken.value = null;

      toast.success(`Bienvenue ${response.user.name} !`);
      
      // Redirect to dashboard
      await router.push('/dashboard');
      
      return { success: true };
    } catch (error: any) {
      toast.error(error.message || 'Code invalide');
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await authService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Clear state
      user.value = null;
      token.value = null;
      refreshToken.value = null;
      
      // Clear localStorage
      localStorage.removeItem('token');
      localStorage.removeItem('refreshToken');
      localStorage.removeItem('user');
      
      // Redirect to login
      await router.push('/login');
      
      toast.info('Vous êtes déconnecté');
    }
  }

  async function checkAuth() {
    if (!token.value) {
      return false;
    }

    try {
      const response = await authService.getProfile();
      user.value = response.user;
      return true;
    } catch (error) {
      // Token might be expired, try to refresh
      if (refreshToken.value) {
        try {
          const response = await authService.refreshAccessToken(refreshToken.value);
          token.value = response.accessToken;
          localStorage.setItem('token', response.accessToken);
          
          // Get user profile with new token
          const profileResponse = await authService.getProfile();
          user.value = profileResponse.user;
          
          return true;
        } catch (refreshError) {
          // Refresh failed, logout
          await logout();
          return false;
        }
      }
      
      await logout();
      return false;
    }
  }

  async function setup2FA() {
    loading.value = true;
    try {
      const response = await authService.setup2FA();
      toast.success('2FA configuré avec succès');
      return response;
    } catch (error: any) {
      toast.error(error.message || 'Erreur lors de la configuration 2FA');
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function disable2FA() {
    loading.value = true;
    try {
      await authService.disable2FA();
      toast.success('2FA désactivé');
    } catch (error: any) {
      toast.error(error.message || 'Erreur lors de la désactivation 2FA');
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function changePassword(oldPassword: string, newPassword: string) {
    loading.value = true;
    try {
      await authService.changePassword(oldPassword, newPassword);
      toast.success('Mot de passe modifié avec succès');
    } catch (error: any) {
      toast.error(error.message || 'Erreur lors du changement de mot de passe');
      throw error;
    } finally {
      loading.value = false;
    }
  }

  // Check if user has specific permission
  function hasPermission(permission: string): boolean {
    return user.value?.permissions?.includes(permission) || false;
  }

  // Check if user has access to specific cash register
  function hasCashRegisterAccess(cashRegisterId: number): boolean {
    return userCashRegisters.value.some(cr => cr.id === cashRegisterId);
  }

  return {
    // State
    user,
    token,
    loading,
    require2FA,
    
    // Computed
    isAuthenticated,
    isAdmin,
    userCountry,
    userPartner,
    userCashRegisters,
    
    // Actions
    login,
    verify2FA,
    logout,
    checkAuth,
    setup2FA,
    disable2FA,
    changePassword,
    hasPermission,
    hasCashRegisterAccess,
  };
});
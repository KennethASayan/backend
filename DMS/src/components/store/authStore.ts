import { ref, computed, watchEffect } from 'vue';
import type { User } from '@/types/user';
import toast from '@/components/ui/message/toast';
import apiClient from '@/services/api';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  sidebarActive: boolean;
}

const state = ref<AuthState>({
  user: null,
  token: null,
  isAuthenticated: false,
  sidebarActive: false,
});

const loading = ref(false);
const error = ref<string | null>(null);

const { addToast } = toast;

function setAuth(authUser: User, authToken: string, rememberMe: boolean) {
  state.value.user = { ...authUser };
  state.value.token = authToken;
  state.value.isAuthenticated = true;
  state.value.sidebarActive = true;

  const storage = rememberMe ? localStorage : sessionStorage;
  storage.setItem('auth_token', authToken);
  localStorage.setItem('user_data', JSON.stringify(state.value.user)); // <-- This line saves user
  storage.setItem('sidebar_active', 'true');
}

function clearAuth() {
  state.value.user = null;
  state.value.token = null;
  state.value.isAuthenticated = false;
  state.value.sidebarActive = false;
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_data');
  localStorage.removeItem('sidebar_active');
  sessionStorage.removeItem('auth_token');
  sessionStorage.removeItem('user_data');
  sessionStorage.removeItem('sidebar_active');
}

async function login(department: string, password: string, rememberMe: boolean = false): Promise<boolean> {
  loading.value = true;
  error.value = null;

  try {
    // Get Sanctum CSRF cookie before login
    await apiClient.get('/sanctum/csrf-cookie');

    const response = await apiClient.post('/dms/login', { department, password, rememberMe });
    const user: User = response.data.user;
    const token: string = response.data.token ?? '';
    setAuth(user, token, rememberMe);
    setSidebarState(true);

    addToast({
      title: 'Login Successful',
      description: `Welcome back, ${user.name}!`,
    });
    return true;
  } catch (e: any) {
    console.error('Login error:', e);
    const errorMessage = e.response?.data?.message || 'An unexpected error occurred during login.';
    addToast({
      title: 'Login Failed',
      description: errorMessage,
      type: 'error',
    });
    return false;
  } finally {
    loading.value = false;
  }
}

async function logout(router?: any) {
  loading.value = true;
  try {
    const token = state.value.token;
    if (token) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      await apiClient.post('/dms/logout');
      delete apiClient.defaults.headers.common['Authorization'];
    }
    clearAuth();

    addToast({
      title: 'Logged Out',
      description: 'You have been successfully logged out.',
      type: 'info',
    });

    if (router) {
      await router.push('/login');
    }
  } catch (e: any) {
    console.error('Logout error:', e);
    const errorMessage = e.response?.data?.message || 'An error occurred during logout.';
    addToast({
      title: 'Logout Error',
      description: errorMessage,
      type: 'error',
    });
  } finally {
    loading.value = false;
  }
}

function initAuth() {
try {
    const storedToken = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const storedUser = localStorage.getItem('user_data') || sessionStorage.getItem('user_data');
    const sidebarActive = localStorage.getItem('sidebar_active') || sessionStorage.getItem('sidebar_active');

    if (storedToken && storedUser) {
      state.value.user = JSON.parse(storedUser);
      state.value.token = storedToken;
      state.value.isAuthenticated = true;
      state.value.sidebarActive = sidebarActive === 'true';

      if (state.value.isAuthenticated) {
        setSidebarState(true);
      }
    }
  } catch (e) {
    console.error('Error initializing auth state:', e);
    clearAuth();
  }
}

function setSidebarState(isActive: boolean) {
  state.value.sidebarActive = isActive;
  if (state.value.isAuthenticated) {
    const storage = state.value.user && localStorage.getItem('auth_token') ? localStorage : sessionStorage;
    storage.setItem('sidebar_active', isActive.toString());
  }
}

export function useAuthStore() {
  watchEffect(() => {
    if (state.value.isAuthenticated && state.value.user) {
      const storage = localStorage.getItem('auth_token') === state.value.token ? localStorage : sessionStorage;
      storage.setItem('sidebar_active', state.value.sidebarActive.toString());
    }
  });

  return {
    user: computed(() => state.value.user),
    token: computed(() => state.value.token),
    isAuthenticated: computed(() => state.value.isAuthenticated),
    sidebarActive: computed(() => state.value.sidebarActive),
    loading: computed(() => loading.value),
    error: computed(() => error.value),

    setAuth,
    clearAuth,
    initAuth,
    login,
    logout,
    setSidebarState,
  };
}

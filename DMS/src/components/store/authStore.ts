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
const initialized = ref(false);

const { addToast } = toast;

// Helper functions for cookie management
const setCookie = (name: string, value: string, days: number = 7) => {
  const d = new Date();
  d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
  const expires = `expires=${d.toUTCString()}`;
  document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
};

const getCookie = (name: string): string | null => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift() || null;
  return null;
};

const deleteCookie = (name: string) => {
  document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
};

function setAuth(authUser: User, authToken: string, rememberMe: boolean) {
    console.log(' Setting auth with user:', authUser.name, 'token length:', authToken.length);
    
    state.value.user = { ...authUser };
    state.value.token = authToken;
    state.value.isAuthenticated = true;
    state.value.sidebarActive = true;

    // Store token in localStorage/sessionStorage based on rememberMe
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem('auth_token', authToken);
    storage.setItem('user_data', JSON.stringify(authUser));
    storage.setItem('remember_me', rememberMe.toString());

    // Store in cookies
    const expirationDays = rememberMe ? 30 : 1;
    setCookie('auth_token', authToken, expirationDays);
    setCookie('user_data', JSON.stringify(authUser), expirationDays);
    setCookie('sidebar_active', 'true', expirationDays);
    setCookie('remember_me', rememberMe.toString(), expirationDays);

    // Set token in API client
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    console.log(' Auth data stored successfully');
}

function clearAuth() {
    console.log(' Clearing auth data');
    state.value.user = null;
    state.value.token = null;
    state.value.isAuthenticated = false;
    state.value.sidebarActive = false;

    // Clear token from API client
    delete apiClient.defaults.headers.common['Authorization'];

    // Clear all storage
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
    localStorage.removeItem('remember_me');
    sessionStorage.removeItem('auth_token');
    sessionStorage.removeItem('user_data');
    sessionStorage.removeItem('remember_me');

    // Clear cookies
    deleteCookie('auth_token');
    deleteCookie('user_data');
    deleteCookie('sidebar_active');
    deleteCookie('remember_me');
}

// Get token from all possible sources
function getStoredToken(): string | null {
  const cookieToken = getCookie('auth_token');
  const localToken = localStorage.getItem('auth_token');
  const sessionToken = sessionStorage.getItem('auth_token');
  
  console.log(' Token sources:', {
    cookie: cookieToken ? `${cookieToken.substring(0, 20)}...` : null,
    localStorage: localToken ? `${localToken.substring(0, 20)}...` : null,
    sessionStorage: sessionToken ? `${sessionToken.substring(0, 20)}...` : null
  });
  
  return cookieToken || localToken || sessionToken;
}

// Get user data from all possible sources
function getStoredUser(): User | null {
  const sources = [
    localStorage.getItem('user_data'),
    sessionStorage.getItem('user_data'),
    getCookie('user_data')
  ];
  
  for (const userData of sources) {
    if (userData) {
      try {
        return JSON.parse(userData);
      } catch (e) {
        console.error('Failed to parse stored user data:', e);
      }
    }
  }
  return null;
}


async function validateSession(): Promise<boolean> {
    console.log(' Starting session validation...');
    
    try {
        const token = state.value.token || getStoredToken();
        
        if (!token) {
            console.log(' No token found during validation');
            return false;
        }

        // Set token in API client before making request
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        const response = await apiClient.get('/dms/me');
        const userData = response.data.user;

        if (!userData) {
            console.log(' No user data returned from server');
            return false;
        }

        // Update state with fresh data
        state.value.user = userData;
        state.value.token = token; // Keep the existing token
        state.value.isAuthenticated = true;
        
        console.log(' Session validation successful');
        return true;
        
    } catch (error: any) {
        console.error(' Session validation error:', error);
        clearAuth();
        return false;
    }
}

async function login(department: string, password: string, rememberMe: boolean = false): Promise<boolean> {
  loading.value = true;
  error.value = null;

  try {
    console.log(' Attempting login for department:', department);
    
    // Get CSRF cookie first
    await apiClient.get('/sanctum/csrf-cookie');

    const response = await apiClient.post('/dms/login', { 
      department, 
      password, 
      rememberMe 
    });
    
    const user: User = response.data.user;
    const token: string = response.data.token ?? '';
    
    console.log(' Login successful for user:', user.name || user.fullName);
    
    // Ensure the user object has the department field
    if (!user.department && department) {
      user.department = department;
    }
    
    setAuth(user, token, rememberMe);
    setSidebarState(true);

    addToast({
      title: 'Login Successful',
      description: `Welcome back, ${user.name || user.fullName}!`,
    });
    return true;
  } catch (e: any) {
    console.error(' Login error:', e);
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
    }
  } catch (e: any) {
    console.error('Logout error:', e);
  }

  clearAuth();

  addToast({
    title: 'Logged Out',
    description: 'You have been successfully logged out.',
    type: 'info',
  });

  if (router) {
    await router.push('/LandingPage');
  }
  
  loading.value = false;
}

async function initAuth(): Promise<void> {
  if (initialized.value) {
    console.log('Auth already initialized, skipping...');
    return;
  }

  console.log(' Initializing auth state...');
  loading.value = true;
  
  try {
    // Get stored token and user data
    const storedToken = getStoredToken();
    const storedUser = getStoredUser();

    if (!storedToken || !storedUser) {
      console.log(' No stored credentials found');
      clearAuth();
      return;
    }

    console.log(' Found stored credentials, validating with server...');
    
    // Set token in API client
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
    
    try {
      // Validate with server
      const response = await apiClient.get('/dms/me');
      
      if (response.data && response.data.user) {
        // Server validation successful
        console.log('Server validation successful');
        state.value.user = response.data.user;
        state.value.token = storedToken;
        state.value.isAuthenticated = true;
        state.value.sidebarActive = getCookie('sidebar_active') === 'true' || 
                                    localStorage.getItem('sidebar_active') === 'true' ||
                                    sessionStorage.getItem('sidebar_active') === 'true';
      } else {
        throw new Error('No user data received from server');
      }
    } catch (serverError) {
      console.error(' Server validation failed:', serverError);
      // Check if it's a network error vs auth error
      if (serverError.response?.status === 401) {
        console.log(' Token expired or invalid, clearing auth');
        clearAuth();
      } else {
        console.log(' Network/server error, using stored data temporarily');
        // Use stored data but mark as potentially stale
        state.value.user = storedUser;
        state.value.token = storedToken;
        state.value.isAuthenticated = true;
        state.value.sidebarActive = true;
      }
    }
  } catch (error) {
    console.error('💥 Critical error in auth initialization:', error);
    clearAuth();
  } finally {
    initialized.value = true;
    loading.value = false;
    console.log('Auth initialization completed:', {
      authenticated: state.value.isAuthenticated,
      user: state.value.user?.name || 'None',
      hasToken: !!state.value.token
    });
  }
}
function setSidebarState(isActive: boolean) {
  state.value.sidebarActive = isActive;
  if (state.value.isAuthenticated) {
    const rememberMe = localStorage.getItem('remember_me') === 'true';
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem('sidebar_active', isActive.toString());
  }
}

function getCurrentUserDepartment(): string {
  return state.value.user?.department || '';
}

async function refreshUserData(): Promise<boolean> {
  if (!state.value.isAuthenticated || !state.value.token) {
    return false;
  }

  try {
    const response = await apiClient.get('/dms/me');
    const userData = response.data.user;
    
    state.value.user = userData;
    
    // Update stored data
    const rememberMe = getCookie('remember_me') === 'true' || 
                      localStorage.getItem('remember_me') === 'true';
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem('user_data', JSON.stringify(userData));
    setCookie('user_data', JSON.stringify(userData), rememberMe ? 30 : 1);
    
    return true;
  } catch (error) {
    console.error('Failed to refresh user data:', error);
    return false;
  }
}

export function useAuthStore() {
  watchEffect(() => {
    if (state.value.isAuthenticated && state.value.user) {
      const rememberMe = localStorage.getItem('remember_me') === 'true';
      const storage = rememberMe ? localStorage : sessionStorage;
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
    initialized: computed(() => initialized.value),

    setAuth,
    clearAuth,
    initAuth,
    login,
    logout,
    setSidebarState,
    getCurrentUserDepartment,
    validateSession,
    refreshUserData,
  };
}
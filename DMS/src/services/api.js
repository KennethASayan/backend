import axios from 'axios'

const apiClient = axios.create({
    baseURL: 'http://127.0.0.1:8000',
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})

// Helper function to get token from multiple sources
const getAuthToken = () => {
    // Check cookies first
    const cookieValue = `; ${document.cookie}`;
    const parts = cookieValue.split(`; auth_token=`);
    if (parts.length === 2) {
        const token = parts.pop()?.split(';').shift();
        if (token) return token;
    }

    // Then check localStorage and sessionStorage
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
}

// Add request interceptor for auth token
apiClient.interceptors.request.use(config => {
    const token = getAuthToken()
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
}, error => {
    return Promise.reject(error)
})

// Add response interceptor for error handling
apiClient.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Clear all auth data on unauthorized response
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user_data')
            localStorage.removeItem('remember_me')
            sessionStorage.removeItem('auth_token')
            sessionStorage.removeItem('user_data')
            sessionStorage.removeItem('remember_me')

            // Clear cookies
            document.cookie = 'auth_token=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/'
            document.cookie = 'user_data=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/'
            document.cookie = 'sidebar_active=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/'
            document.cookie = 'remember_me=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/'

            // Redirect to landing page
            if (window.location.pathname !== '/LandingPage' && window.location.pathname !== '/login') {
                window.location.href = '/LandingPage'
            }
        }
        return Promise.reject(error)
    }
)

export default apiClient

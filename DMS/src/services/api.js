import axios from 'axios'

const apiClient = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true, // Important for CORS
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Add interceptor to handle CSRF token
apiClient.interceptors.request.use(async config => {
  // Get CSRF cookie before sensitive requests
  if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase() || '')) {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie')
  }
  return config
})

export default apiClient

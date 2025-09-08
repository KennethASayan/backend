import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/components/store/authStore'
import Antd from 'ant-design-vue'
import 'vue-loading-overlay/dist/css/index.css'

async function initializeApp() {
    const app = createApp(App)
    const authStore = useAuthStore()
    
    app.use(Antd)
    app.use(router)

    try {
        // Initialize auth state before mounting, this is the correct place to do this
        await authStore.initAuth()
        console.log('Auth initialized:', {
            isAuthenticated: authStore.isAuthenticated.value,
            user: authStore.user.value?.name
        })
    } catch (error) {
        console.error('Failed to initialize auth:', error)
    }

    // Mount the app after initialization
    app.mount('#app')
}

// Initialize the app
initializeApp().catch(console.error)

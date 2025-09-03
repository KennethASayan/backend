import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import 'vue-loading-overlay/dist/css/index.css'
import Antd from 'ant-design-vue'
import { useAuthStore } from '@/components/store/authStore'

async function initializeApp() {
  const app = createApp(App)
  
  // Initialize auth store before router
  const authStore = useAuthStore()
  await authStore.initAuth()
  
  app.use(router)
  app.use(Antd)
  app.mount('#app')
}

// Initialize the app
initializeApp().catch(console.error)
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import 'vue-loading-overlay/dist/css/index.css'
import Antd from 'ant-design-vue'
import { useAuthStore } from '@/components/store/authStore'

const app = createApp(App)
const auth = useAuthStore()
auth.initAuth()
app.use(router)
app.use(Antd)
app.mount('#app')
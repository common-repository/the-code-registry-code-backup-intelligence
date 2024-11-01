import { createApp } from 'vue'
import App from './App.vue'
import VaultPage from './VaultPage.vue'
import '../../css/index.css'

// popper
import Popper from "vue3-popper";

// Determine which component to render based on the current WordPress admin page
const app = createApp(window.location.search.includes('page=code-vault') ? VaultPage : App).use(Popper)

// Pass WordPress data to Vue app
app.config.globalProperties.$wpData = window.codeIntelligenceData || {}

app.mount('#code-intelligence-app')
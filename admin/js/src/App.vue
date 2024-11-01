<script setup>
import { ref, computed, onMounted } from 'vue'
import Welcome from './components/Welcome.vue'
import Signup from './components/Signup.vue'
import CreateProjectAndCodeVault from './components/CreateProjectAndCodeVault.vue'
import ShowProjectDashboard from './components/ShowProjectDashboard.vue'
import ShowProjectMetrics from './components/ShowProjectMetrics.vue'

const currentView = ref('Welcome')

const changeView = (view) => {
  currentView.value = view
}

const CurrentComponent = computed(() => {
  switch (currentView.value) {
    case 'Welcome':
      return Welcome
    case 'Signup':
      return Signup
    case 'CreateProjectAndCodeVault':
      return CreateProjectAndCodeVault
    case 'ShowProjectDashboard':
      return ShowProjectDashboard
    case 'ShowProjectMetrics':
      return ShowProjectMetrics
    default:
      return Welcome
  }
})

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.get('action') === 'account_created') {
    currentView.value = 'CreateProjectAndCodeVault'
  } else {
    // Check if API key, team ID, and user ID are set
    const apiKey = window.codeIntelligenceData.apiKey
    const teamId = window.codeIntelligenceData.teamId
    const userId = window.codeIntelligenceData.userId
    const projectId = window.codeIntelligenceData.projectId
    const codeVaultId = window.codeIntelligenceData.codeVaultId

    if (apiKey && teamId && userId) {
      if (projectId && codeVaultId) {
        currentView.value = 'ShowProjectDashboard'
      } else {
        currentView.value = 'CreateProjectAndCodeVault'
      }
    }
  }
})
</script>

<template>
  <div class="font-sans bg-white mt-5">
    <component :is="CurrentComponent" @change-view="changeView" />
  </div>
</template>
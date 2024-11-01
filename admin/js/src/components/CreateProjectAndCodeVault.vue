<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()

const isLoading = ref(true)
const errorMessage = ref('') 
const successMessage = ref('')

const emit = defineEmits(['changeView'])

const createProjectAndCodeVault = async () => {
  try {
    const response = await fetch(proxy.$wpData.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'tcr_cbi_create_project_and_vault',
        nonce: proxy.$wpData.nonce,
      })
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      throw new Error("Oops! We haven't received a JSON response from the server.");
    }

    const data = await response.json();

    if (data.success) {
      emit('changeView', 'ShowProjectDashboard');
    } else {
      errorMessage.value = data.data.message || 'An error occurred while creating the project and code vault.';
    }
  } catch (error) {
    console.error('Error:', error);
    errorMessage.value = `A network error occurred: ${error.message}. Please try again.`;
  } finally {
    isLoading.value = false;
  }
}

onMounted(async () => {
  await createProjectAndCodeVault()
})
</script>

<template>
  <div class="relative overflow-x-hidden overflow-y-hidden isolate bg-white px-6 py-24 sm:py-32 lg:px-8">
    <img class="absolute z-[-1] w-[400px] -top-[90px] -right-[130px] opacity-25 lg:opacity-50 2xl:w-[640px] 2xl:-top-[160px] 2xl:-right-[220px]" :src="`${proxy.$wpData.pluginUrl}admin/img/angled-icon.svg`" alt="The Code Registry angled icon">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
      <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
    <div class="mx-auto max-w-xl lg:max-w-4xl">
      <img class="block mb-10 h-12 w-auto" src="https://thecoderegistry.com/wp-content/uploads/2023/12/CR_POS_HOR@2x.png" alt="" />
      <h2 class="font-serif text-4xl tracking-tight text-brand-blue">Thanks for registering! Let's analyze your code.</h2>
      <div v-if="isLoading" class="mt-4 text-lg text-gray-600">
        <h3 class="text-xl font-semibold animate-pulse text-brand-purple">Setting up your project and archiving your site's code...</h3>
        <h3 class="text-lg font-semibold animate-pulse text-brand-black">This can be as quick as 5 minutes, but can take longer depending on the size of your site's codebase.</h3>
        <img :src="`${proxy.$wpData.pluginUrl}admin/img/building-archive.gif`" class="w-[400px] mt-10" />
      </div>
      <div v-else-if="errorMessage" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ errorMessage }}
      </div>
      <div v-else-if="successMessage" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ successMessage }}
      </div>
    </div>
    <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
      <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
  </div>
</template>
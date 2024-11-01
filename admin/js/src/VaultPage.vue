<script setup>
import { toRefs, getCurrentInstance, ref, onMounted, onUnmounted, computed } from 'vue';

const { proxy } = getCurrentInstance()

// Define props
const props = defineProps({
  facet_values: {
    type: Object,
    default: () => ({
      'security': null,
      'cms_detection': null,
      'licenses': null,
      'complexity': null,
      'languages': null,
      'file_types': null,
      'valuation': null,
      'ai_quotient': null,
      'status': null
    })
  },
  all_ready: Boolean
});

// In your setup function
const { facet_values, all_ready } = toRefs(props);

const accountSetup = ref(false);

// code that polls the backend for facet status and data
const isPolling = ref(false);
let pollingInterval;

// hold our language array for the vault layout component
const languagesArray = ref([]);
const fileTypesArray = ref([]);

const pollFacetsStatus = async () => {
  try {
    const response = await fetch(proxy.$wpData.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'tcr_cbi_get_facet_data',
        nonce: proxy.$wpData.nonce
      })
    });

    const body = await response.json();

    if (response.ok && body.success && body.data && body.data.data) {
      let allReady = true;
      const newFacetValues = { ...facet_values.value };

      Object.entries(body.data.data).forEach(([facet, facetData]) => {
        if (['id', 'name', 'created_at', 'version', 'code_last_synced', 'code_contributors', 'subscription_status'].includes(facet)) {
          return;
        }

        // Normalize the facet key
        const normalizedFacet = facet.replace(/-/g, '_');

        if (facetData.ready && facetData.data) {
          newFacetValues[normalizedFacet] = facetData.data;
        } else {
          newFacetValues[normalizedFacet] = null;
        }

        if (normalizedFacet === 'languages' && facetData.ready && facetData.data && facetData.data.by_language) {
          languagesArray.value = Object.entries(facetData.data.by_language)
            .map(([index, languageData]) => ({
              language: languageData.language,
              sourceCount: languageData.sourceCount,
            }));
        }

        if (normalizedFacet === 'file_types' && facetData.ready && facetData.data && facetData.data.file_types) {
          fileTypesArray.value = Object.entries(facetData.data.file_types)
            .map(([type, data]) => ({
              fileType: type,
              size: data.file_size,
            }));
        }

        if (!facetData.ready) {
          allReady = false;
        }
      });

      // Update facet_values reactively
      facet_values.value = newFacetValues;

      // Check if all facets are ready
      if (allReady) {
        isPolling.value = false;
        clearInterval(pollingInterval);
      }
    } else {
      console.log('Invalid response from server:', body);
    }
  } catch (error) {
    console.error('Error:', error);
  }
};

const vaultData = computed(() => {
  if (!facet_values.value.status) {
    return {
      size: '(analyzing...)',
      code_last_synced: '(syncing...)',
      status: '...',
    };
  }
  return {
    size: facet_values.value.status.size === '(analyzing...)' ? '(analyzing...)' : formatFileSize(facet_values.value.status.size),
    code_last_synced: facet_values.value.status.code_last_synced,
    status: facet_values.value.status.status,
  };
});

function formatFileSize(bytes) {
    if (bytes === '(analyzing...)') return bytes;

    const MB = 1024 * 1024;
    const GB = 1024 * MB;

    if (bytes > GB) {
        return (bytes / GB).toFixed(2) + ' GB';
    } else {
        return (bytes / MB).toFixed(2) + ' MB';
    }
}

// Code to show the top languages
const top3Languages = computed(() => {
  const sortedLanguages = [...languagesArray.value].sort((a, b) => b.sourceCount - a.sourceCount);
  return sortedLanguages.slice(0, 3).map(lang => lang.language);
});

// Code to show the top file types
const top3FileTypes = computed(() => {
  const sortedFileTypes = [...fileTypesArray.value].sort((a, b) => b.size - a.size);
  return sortedFileTypes.slice(0, 3).map(lang => lang.fileType);
});

onMounted(() => {
  // Check if we have an account yet
  const apiKey = window.codeIntelligenceData.apiKey
  const teamId = window.codeIntelligenceData.teamId
  const userId = window.codeIntelligenceData.userId
  if (apiKey && teamId && userId) {
    accountSetup.value = true;
  }

  // Check if we need to start polling
  if (!props.all_ready) {
    isPolling.value = true;
    pollFacetsStatus(); // Initial call
    pollingInterval = setInterval(pollFacetsStatus, 5000);
  }
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>
<template>
  <div class="relative isolate flex items-center mt-10 gap-x-6 overflow-hidden bg-gray-50 border border-1 border-gray-600 px-6 pt-3 pb-4 sm:px-3.5 sm:before:flex-1 sm:ml-10 sm:max-w-xl rounded-lg" v-if="proxy.$wpData.subscriptionStatus === 'inactive'">
    <div class="absolute left-[max(-7rem,calc(50%-52rem))] top-1/2 -z-10 -translate-y-1/2 transform-gpu blur-2xl" aria-hidden="true">
      <div class="aspect-[577/310] w-[36.0625rem] bg-gradient-to-r from-[#ff80b5] to-[#9089fc] opacity-30" style="clip-path: polygon(74.8% 41.9%, 97.2% 73.2%, 100% 34.9%, 92.5% 0.4%, 87.5% 0%, 75% 28.6%, 58.5% 54.6%, 50.1% 56.8%, 46.9% 44%, 48.3% 17.4%, 24.7% 53.9%, 0% 27.9%, 11.9% 74.2%, 24.9% 54.1%, 68.6% 100%, 74.8% 41.9%)" />
    </div>
    <div class="absolute left-[max(45rem,calc(50%+8rem))] top-1/2 -z-10 -translate-y-1/2 transform-gpu blur-2xl" aria-hidden="true">
      <div class="aspect-[577/310] w-[36.0625rem] bg-gradient-to-r from-[#ff80b5] to-[#9089fc] opacity-30" style="clip-path: polygon(74.8% 41.9%, 97.2% 73.2%, 100% 34.9%, 92.5% 0.4%, 87.5% 0%, 75% 28.6%, 58.5% 54.6%, 50.1% 56.8%, 46.9% 44%, 48.3% 17.4%, 24.7% 53.9%, 0% 27.9%, 11.9% 74.2%, 24.9% 54.1%, 68.6% 100%, 74.8% 41.9%)" />
    </div>
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
      <p class="text-sm leading-6 text-gray-900">
        <strong class="font-semibold">Your 14 day free trial has ended.</strong> Your code is still backed up and you can still view your data but the insights and analysis won't be automatically updated each month.
      </p>
      <a href="https://app.thecoderegistry.com" target="_blank" class="rounded-lg bg-brand-purple px-3.5 py-1 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">View our main web app to manage your subscription</a>
    </div>
  </div>
  <div class="font-sans relative bg-white px-6 pb-8 pt-10 mt-10 mr-2 shadow-xl ring-1 ring-gray-900/5 sm:ml-10 sm:max-w-xl sm:rounded-lg sm:px-10">
    <div class="flex items-center justify-between">
      <img src="https://thecoderegistry.com/wp-content/uploads/2023/12/CR_POS_HOR@2x.png" class="h-12" alt="The Code Registry" />
      <img src="https://app.thecoderegistry.com/img/ip-vault-status-icon.png" class="h-14" alt="The Code Registry Code Vault" />
    </div>
    <div class="divide-y divide-gray-300/50">
      <div class="space-y-4 py-8 leading-7 text-black">
        <h1 class="text-xl font-serif tracking-tight text-brand-blue sm:text-2xl">Backed up. Secured. Encrypted.</h1>
        <div v-if="!accountSetup">
          <p class="mb-2">You need to setup your account first before you can view your code backup and replication status.</p>
          <p class="mb-4">Click the button below to get started.</p>
          <a href="/wp-admin/admin.php?page=code-intelligence" class="font-semibold text-white rounded-md bg-brand-purple px-3 py-2 text-sm shadow-sm hover:bg-brand-blue hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-purple">Get started for free!</a>
        </div>
        <div v-else-if="!isPolling">
          <p>Your WordPress site's codebase is securely backed up and encrypted in our private cloud infrastructure. You can view some stats on your backup below and download the latest backup if needed.</p>
          <ul class="my-5 space-y-2">
            <li class="flex items-center">
              <svg class="h-6 w-6 flex-none fill-sky-100 stroke-brand-blue stroke-2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="11" />
                <path d="m8 13 2.165 2.165a1 1 0 0 0 1.521-.126L16 9" fill="none" />
              </svg>
              <p class="ml-4">
                Code Vault Version: 
                <code class="text-sm font-bold text-gray-900">{{ proxy.$wpData.codeVaultVersion }}</code>
              </p>
            </li>
            <li class="flex items-center">
              <svg class="h-6 w-6 flex-none fill-sky-100 stroke-brand-blue stroke-2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="11" />
                <path d="m8 13 2.165 2.165a1 1 0 0 0 1.521-.126L16 9" fill="none" />
              </svg>
              <p class="ml-4">
                Total size: 
                <code class="text-sm font-bold text-gray-900">{{ vaultData.size }}</code>
              </p>
            </li>
            <li class="flex items-center">
              <svg class="h-6 w-6 flex-none fill-sky-100 stroke-brand-blue stroke-2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="11" />
                <path d="m8 13 2.165 2.165a1 1 0 0 0 1.521-.126L16 9" fill="none" />
              </svg>
              <p class="ml-4">
                Programming languages:
                <code class="text-xs font-bold text-gray-900" v-if="languagesArray.length > 0">
                  {{ top3Languages.join(', ') }}
                  <template v-if="(Math.max(0, languagesArray.length - 3)) > 0">
                      and {{ (Math.max(0, languagesArray.length - 3)) }} more
                    </template>
                </code>
              </p>
            </li>
            <li class="flex items-center">
              <svg class="h-6 w-6 flex-none fill-sky-100 stroke-brand-blue stroke-2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="11" />
                <path d="m8 13 2.165 2.165a1 1 0 0 0 1.521-.126L16 9" fill="none" />
              </svg>
              <p class="ml-4">
                File types:
                <code class="text-xs font-bold text-gray-900">
                  <template v-if="fileTypesArray && fileTypesArray.length > 0">
                    {{ top3FileTypes.join(', ') }}
                    <template v-if="(Math.max(0, fileTypesArray.length - 3)) > 0">
                      and {{ (Math.max(0, fileTypesArray.length - 3)) }} more
                    </template>
                  </template>
                  <template v-else>
                    Data not available
                  </template>
                </code>
              </p>
            </li>
          </ul>
        </div>
        <div v-else>
          <p class="animate-pulse">Your WordPress site's codebase is still being analysed and backed up...</p>
          <img :src="`${proxy.$wpData.pluginUrl}admin/img/analysing.gif`" class="mt-5 mb-2" loading="lazy" />
        </div>
      </div>
      <div class="pt-4 text-base font-semibold leading-7">
        <p class="text-gray-900">Want to download the latest version we have of your code?</p>
        <p>
          <a href="https://app.thecoderegistry.com" target="_blank" class="text-brand-purple hover:text-brand-blue">Login to our main app &rarr;</a>
        </p>
      </div>
    </div>
  </div>
</template>
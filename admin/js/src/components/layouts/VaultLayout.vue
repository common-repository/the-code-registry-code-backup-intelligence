<script setup>
import { ref, onMounted, onUnmounted, getCurrentInstance, computed } from 'vue';
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Bars3Icon, CalendarIcon, ClockIcon, CodeBracketSquareIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import AdaAvatar from '@/components/partials/AdaAvatar.vue';

const { proxy } = getCurrentInstance()

const props = defineProps({
  languages: {
    type: Array,
    default: () => []
  }
});
const currentView = ref('ShowProjectDashboard');

// Open web app dialog when clicking certain buttons
const featurePreviews = {
  'main': {
    title: 'View this in our main web app',
    description: 'Our main web app is the best place to see all the features of The Code Registry. It includes live chat with our AI assistant Ada, full security issue triaging, exporting SBOMS of your components and license and much more.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/homepage.png`,  
  },
  'aiq': {
    title: 'AI Quotient&#8482; - view this in our main web app',
    description: 'Our platform analyses your entire codebase for common bad practices, coding quality and structure issues that we know most generative AI coding models are perfectly suited to improve.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/aiq.png`,
  },
  'insights': {
    title: 'AI Insights - view this in our main web app',
    description: 'Our AI assistant Ada generates insights form the results of your code analysis. You can see some of these on our Summary page under "What does Ada say?", but our web app has a lot more on this dedicated page.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/insights.png`,
  },
  'complexity': {
    title: 'Code Complexity - view this in our main web app',
    description: 'We use a number of metrics to calculate how "complex" your code is. The main one is "Cyclomatic Complexity" or "CC". On this dedicated page you can see the overall average complexity, statistics for each language and AI insights generated from this data.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/complexity.png`,
  },
  'security': {
    title: 'Security &amp; Vulnerabilities - view this in our main web app',
    description: 'Our platform scans your codebase and any detected third party dependencies to find any potential security vulnerabilities. There\'s a summary of our findings on the Summary and Metrics Dashboard pages, but we have a lot more information and functionality on this dedicated page.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/security.png`,
  },
  'components': {
    title: 'Open Source Components - view this in our main web app',
    description: 'This dedicated page shows every detected open source component in your codebase, along with its latest version, total lines of code and more. You can also see the license and compliance checklists for each component.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/components.png`,
  },
  'valuation': {
    title: 'Cost to Replicate - view this in our main web app',
    description: 'This dedicated page goes into detail about data used in our "Cost to Replicate" calculations we use to determine the cost of replicating your code. You can see the total cost, the breakdown by language and the AI-generated cost to replicate insights.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/valuation.png`,
  },
  'history': {
    title: 'Vault Version History - view this in our main web app',
    description: 'Every time your WordPress site\'s codebase is replicated a new version is created in our system. Think of each version like a snapshot in time of your codebase. This dedicated page shows you a full history of every previous version of your IP Code Vaults in this project.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/history.png`,
  },
  'chat': {
    title: 'Chat with Ada - view this in our main web app',
    description: 'Ada is our code expert AI assistant. She can answer any questions you have about your own code and any of our analysis results. She has access to all the data we have collected about your code and can talk to you about any complex coding topic.',
    image: `${proxy.$wpData.pluginUrl}admin/img/screenshots/chat.png`,
  }
};
const currentFeature = ref(null);
const openWebAppDialog = ref(false);
const webAppDialogContext = ref(null);
const previewWebAppDialog = (context) => {
  webAppDialogContext.value = context;
  currentFeature.value = featurePreviews[context];
  openWebAppDialog.value = true;
}

// Variables for PDF report
const isPdfPolling = ref(false);
let pdfPollingInterval;
const pdfReportUrl = ref('');
const isPdfReady = ref(false);
const comparisonPdfReportUrl = ref('');
const isComparisonPdfReady = ref(false);

const pollPdfStatus = async () => {
  try {
    const response = await fetch(proxy.$wpData.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'tcr_cbi_get_pdf_report',
        nonce: proxy.$wpData.nonce
      })
    })

    const body = await response.json()

    if (response.ok && body.success) {
      if (body.data && body.data.snapshot != '' && body.data.snapshot != 'not_ready') {
        pdfReportUrl.value = body.data.snapshot;
        isPdfReady.value = true;
        isPdfPolling.value = false;

        if (body.data.comparison != '' && body.data.comparison != 'not_ready') {
          comparisonPdfReportUrl.value = body.data.comparison;
          isComparisonPdfReady.value = true;
        }

        if (isPdfReady.value) {
          if (proxy.$wpData.codeVaultVersion == '1.0.0') {
            clearInterval(pdfPollingInterval);
          } else if (isComparisonPdfReady.value) {
            clearInterval(pdfPollingInterval);
          }
        }
      }
    } else {
      console.log('An error occurred while fetching PDF report status.');
    }
  } catch (error) {
    console.error('Error:', error)
  }
};

const downloadPdfReport = () => {
  if (isPdfReady.value && pdfReportUrl.value) {
    window.open(pdfReportUrl.value, '_blank');
  }
};

const downloadComparisonPdfReport = () => {
  if (isComparisonPdfReady.value && comparisonPdfReportUrl.value) {
    window.open(comparisonPdfReportUrl.value, '_blank');
  }
};

onMounted(() => {
  isPdfPolling.value = true;
  pollPdfStatus(); // Initial call
  pdfPollingInterval = setInterval(pollPdfStatus, 5000);
});

onUnmounted(() => {
  if (pdfPollingInterval) {
    clearInterval(pdfPollingInterval);
  }
});

const emit = defineEmits(['changeView'])
const changeView = (view) => {
  currentView.value = view;
  window.location.hash = view;
  emit('changeView', view === 'summary' ? 'ShowProjectDashboard' : 'ShowProjectMetrics');
};

// Code to show the top languages
const top3Languages = computed(() => {
  const sortedLanguages = [...props.languages].sort((a, b) => b.sourceCount - a.sourceCount);
  return sortedLanguages.slice(0, 3).map(lang => lang.language);
});

const handleHashChange = () => {
  const hash = window.location.hash.slice(1);
  if (hash === 'summary' || hash === 'metrics') {
    currentView.value = hash;
    emit('changeView', hash === 'summary' ? 'ShowProjectDashboard' : 'ShowProjectMetrics');
  }
};

onMounted(() => {
  window.addEventListener('hashchange', handleHashChange);
  
  // Check initial hash
  const initialHash = window.location.hash.slice(1);
  if (initialHash === 'summary' || initialHash === 'metrics') {
    currentView.value = initialHash;
    emit('changeView', initialHash === 'summary' ? 'ShowProjectDashboard' : 'ShowProjectMetrics');
  } else {
    // Default to summary if no hash
    window.location.hash = 'summary';
  }
});

onUnmounted(() => {
  window.removeEventListener('hashchange', handleHashChange);
});
</script>
<template>
  <div class="relative overflow-x-hidden overflow-y-hidden isolate bg-white px-4 py-5 sm:px-6 sm:py-10 lg:px-8">
    <img class="absolute z-[-1] w-[400px] -top-[90px] -right-[130px] opacity-25 lg:opacity-50 2xl:w-[640px] 2xl:-top-[160px] 2xl:-right-[220px]" :src="`${proxy.$wpData.pluginUrl}admin/img/angled-icon.svg`" alt="The Code Registry angled icon">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
      <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
    <div class="">
      <div class="relative">
        <main class="relative overflow-x-hidden min-h-screen h-full pt-6 pb-10 2xl:pt-10">
          <div>
            <!-- Page Heading -->
            <img class="block mb-10 h-12 w-auto" src="https://thecoderegistry.com/wp-content/uploads/2023/12/CR_POS_HOR@2x.png" alt="" />
            <div class="relative isolate flex items-center mt-4 mb-5 gap-x-6 overflow-hidden bg-gray-50 border border-1 border-gray-600 px-4 pt-3 pb-4 sm:px-3 sm:before:flex-1 sm:max-w-2xl rounded-lg" v-if="proxy.$wpData.subscriptionStatus === 'inactive'">
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
                <a href="https://app.thecoderegistry.com" target="_blank" class="rounded-lg bg-brand-purple px-3.5 py-1 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue">View our main web app to manage your subscription</a>
              </div>
            </div>
            <header class="pb-5">
              <div class="md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                  <h1 class="font-serif leading-[22px] text-2xl sm:text-3xl" id="tour-project-summary">
                    <slot name="header">
                      <span>
                        Viewing Code Intelligence for your WordPress site code
                      </span>
                    </slot>
                  </h1>
                  <div>
                    <!-- vault info for smaller screens -->
                    <div class="mt-3 grid grid-cols-12 gap-1 p-2 rounded-lg border-2 border-gray-400 bg-white/50 shadow-sm xl:hidden">
                      <div class="col-span-1">
                        <ClockIcon class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-brand-purple" aria-hidden="true" />
                      </div>
                      <div class="flex items-center text-xs capitalize text-black col-span-11">
                        Current version: {{ proxy.$wpData.codeVaultVersion }}
                        <a href="#" class="ml-3 rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('history')">
                          View history
                        </a>
                      </div>
                      <div class="col-span-1">
                        <CodeBracketSquareIcon class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-orange-600" aria-hidden="true" />
                      </div>
                      <div class="flex text-xs capitalize text-black col-span-11 items-start sm:items-center">
                        Programming Languages:&nbsp;
                        <span class="text-xs" v-if="languages.length > 0">
                          {{ top3Languages.join(', ') }}
                          <template v-if="(Math.max(0, languages.length - 3)) > 0">
                            and {{ Math.max(0, languages.length - 3) }} more
                          </template>
                        </span>
                        <template v-else>
                          ...
                        </template>
                      </div>
                      <div class="col-span-1">
                        <CalendarIcon class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                      </div>
                      <div class="flex items-center text-xs capitalize text-black col-span-11">
                        Code Vault Created:&nbsp;<time class="text-xs" :datetime="proxy.$wpData.codeVaultCreatedDate">{{ proxy.$wpData.codeVaultCreatedDate }}</time>
                      </div>
                      <div class="col-span-1">
                        <CalendarIcon class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                      </div>
                      <div class="flex items-center text-xs capitalize text-black col-span-11">
                        Code Last Synced:&nbsp;<time class="text-xs" :datetime="proxy.$wpData.codeVaultCodeSyncedDate">{{ proxy.$wpData.codeVaultCodeSyncedDate }}</time>
                      </div>
                    </div>
                    <!-- vault info for larger screens -->
                    <dl class="mt-3 flex-wrap hidden xl:flex">
                      <dt>
                        <ClockIcon class="mr-1.5 h-5 w-5 flex-shrink-0 text-brand-purple" aria-hidden="true" />
                      </dt>
                      <dd class="flex items-center text-sm capitalize text-black sm:mr-6">
                        Current version: {{ proxy.$wpData.codeVaultVersion }}
                        <a href="#" class="ml-3 rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('history')">
                          View history
                        </a>
                      </dd>
                      <dt>
                        <CodeBracketSquareIcon class="mr-1.5 h-5 w-5 flex-shrink-0 text-orange-600" aria-hidden="true" />
                      </dt>
                      <dd class="mt-3 flex items-center text-sm capitalize text-black sm:mr-6 sm:mt-0">
                        Programming Languages:&nbsp;
                        <span class="text-xs" v-if="languages.length > 0">
                          {{ top3Languages.join(', ') }}
                          <template v-if="(Math.max(0, languages.length - 3)) > 0">
                            and {{ Math.max(0, languages.length - 3) }} more
                          </template>
                        </span>
                        <template v-else>
                          ...
                        </template>
                      </dd>
                      <div class="w-full flex xl:w-auto">
                        <dt>
                          <CalendarIcon class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                        </dt>
                        <dd class="mt-3 flex items-center text-sm capitalize text-black sm:mr-6 sm:mt-0">
                          Code Vault Created:&nbsp;<time class="text-xs" :datetime="proxy.$wpData.codeVaultCreatedDate">{{ proxy.$wpData.codeVaultCreatedDate }}</time>
                        </dd>
                        <dt>
                          <CalendarIcon class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
                        </dt>
                        <dd class="mt-3 flex items-center text-sm capitalize text-black sm:mr-6 sm:mt-0">
                          Code Last Synced:&nbsp;<time class="text-xs" :datetime="proxy.$wpData.codeVaultCodeSyncedDate">{{ proxy.$wpData.codeVaultCodeSyncedDate }}</time>
                        </dd>
                      </div>
                    </dl>
                  </div>
                </div>
                <div class="mt-4 text-right md:ml-4 md:mb-auto md:mt-0" id="tour-page-buttons">
                  <button type="button" class="bg-brand-blue text-white rounded-md px-3 py-2 font-semibold shadow-sm inline-flex items-center hover:bg-brand-purple hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 text-xs w-full sm:w-auto sm:text-sm sm:text-center" :class="{ 'animate-pulse cursor-not-allowed': !isPdfReady }" @click="downloadPdfReport" :disabled="!isPdfReady">
                    {{ isPdfReady ? 'Download PDF Report' : 'Generating PDF report...' }}
                  </button>
                  <br />
                  <button type="button" class="bg-brand-blue text-white rounded-md mt-2 px-3 py-2 font-semibold shadow-sm inline-flex items-center hover:bg-brand-purple hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 text-xs w-full sm:w-auto sm:text-sm sm:text-center" :class="{ 'animate-pulse cursor-not-allowed': !isComparisonPdfReady }" @click="downloadComparisonPdfReport" :disabled="!isComparisonPdfReady" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
                    {{ isPdfReady ? 'Download Comparison Report' : 'Generating comparison report...' }}
                  </button>
                </div>
              </div>
            </header>
            <!-- page sub navigation menu -->
            <div id="tour-page-sub-nav">
              <!-- vault page nav -->
              <Disclosure as="nav" class="rounded-md bg-gray-800">
                <div class="mx-auto px-2 sm:px-4">
                  <div class="relative flex items-center justify-between h-15 md:h-16">
                    <div class="flex items-center px-2 lg:px-0">
                      <div class="hidden xl:block">
                        <div class="flex space-x-2">
                          <button type="button" :class="['rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm', currentView === 'summary' ? 'bg-black text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white']" @click="changeView('summary')">Summary</button>
                          <button type="button" :class="['rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm', currentView === 'metrics' ? 'bg-black text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white']" @click="changeView('metrics')">Metrics Dashboard</button>
                          <Menu as="div" class="relative">
                            <MenuButton as="a" class="text-gray-300 hover:bg-gray-700 hover:text-white relative flex items-center cursor-pointer rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm">
                              AI<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                              </svg>
                            </MenuButton>
                            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                              <MenuItems class="absolute right-0 z-10 mt-2 p-2 w-56 origin-top-right rounded-md bg-gray-800 border border-white inline-block outline-none gap-2">
                                <MenuItem v-slot="{ active }">
                                  <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 font-medium text-xs hover:text-white focus:outline-none focus:text-white 2xl:text-sm" @click.stop="previewWebAppDialog('aiq')">AI Quotient&#8482;</a>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                  <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 font-medium text-xs hover:text-white focus:outline-none focus:text-white 2xl:text-sm" @click.stop="previewWebAppDialog('insights')">AI Analysis &amp; Insights</a>
                                </MenuItem>
                              </MenuItems>
                            </transition>
                          </Menu>
                          <a href="#" class="rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('complexity')">Code Complexity</a>
                          <a href="#" class="rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('security')">Security &amp; Vulnerabilities</a>
                          <a href="#" class="rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('components')">Open Source Components</a>
                          <a href="#" class="rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('valuation')">Cost to Replicate</a>
                          <a href="#" class="rounded-md px-3 py-2 font-medium text-xs 2xl:text-sm text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('history')"><span class="hidden xl:inline">Vault&nbsp;</span>Version History</a>
                        </div>
                      </div>
                    </div>
                    <div class="flex xl:hidden">
                      <!-- Mobile menu button -->
                      <DisclosureButton class="inline-flex items-center justify-center rounded-md p-2 text-white hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="text-sm text-white pr-2">Code vault menu</span>
                        <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
                        <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
                      </DisclosureButton>
                    </div>
                  </div>
                </div>
                <DisclosurePanel class="xl:hidden">
                  <div class="space-y-1 px-2 pb-3 pt-2">
                    <DisclosureButton as="a" href="#summary" class="block rounded-md px-3 py-2 text-base font-medium bg-gray-900 text-white focus:outline-none focus:text-white" @click="changeView('ShowProjectDashboard')">Summary</DisclosureButton>
                    <DisclosureButton as="a" href="#metrics" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click="changeView('ShowProjectMetrics')">Metrics Dashboard</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('aiq')">AI Quotient&#8482;</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('complexity')">Code Complexity</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('security')">Security &amp; Vulnerabilities</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('components')">Open Source Components</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('valuation')">Cost to Replicate</DisclosureButton>
                    <DisclosureButton as="a" href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white focus:outline-none focus:text-white" @click.stop="previewWebAppDialog('history')"><span class="hidden xl:inline">Vault&nbsp;</span>Version History</DisclosureButton>
                  </div>
                </DisclosurePanel>
              </Disclosure>
            </div>
          </div>
          <!-- Page Content -->
          <main class="pb-20">
            <slot :previewWebAppDialog="previewWebAppDialog" />
          </main>
        </main>
      </div>
    </div>
    <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
      <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
  </div>
  <div class="fixed bottom-0 right-0 z-[50] pr-2 pb-2 sm:pr-5 sm:pb-5" id="tour-chatbot-avatar">
    <div>
      <div class="flex flex-row mb-1">
        <AdaAvatar class="mt-auto w-[50px] ml-2 mr-2 sm:w-[60px] sm:ml-0 sm:mr-4" />
        <div class="bg-brand-blue rounded-lg relative border-t-2 border-t-white border-b-2 border-b-white p-2 sm:p-4">
          <div class="w-11 overflow-hidden inline-block absolute bottom-0 -left-[10px] sm:-left-[20px]">
            <div class="h-10 bg-brand-blue rotate-45 transform origin-bottom-left"></div>
          </div>
          <button class="font-semibold text-white mb-1 relative text-xs sm:text-sm" @click="previewWebAppDialog('chat')">Hi, I'm your code intelligence AI Ada. How can I help you today?</button>
        </div>
      </div>
    </div>
  </div>
  <div class="fixed inset-0 z-10 w-screen overflow-y-auto" v-if="openWebAppDialog">
    <div class="flex min-h-full items-end justify-center p-4 pb-20 text-center bg-gray-800/50 sm:items-center sm:p-0 sm:pb-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:p-6">
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="font-serif text-xl font-semibold leading-6 text-brand-blue" v-html="currentFeature.title"></h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500" v-html="currentFeature.description"></p>
              <img :src="currentFeature.image" alt="Feature preview" class="mt-5 mb-10 w-full " />
            </div>
          </div>
          <div class="mt-2">
            <p class="text-sm text-gray-500">This and many more features are exclusive to our main web app, including live chat with our AI assistant Ada, full security issue triaging, exporting SBOMS of your components and license and much more.</p>
            <p class="mt-2 text-sm text-gray-500">If you're here you already have an account! Simply login with the same email you used when you setup this plugin.</p> 
          </div>
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
            <a href="https://app.thecoderegistry.com" target="_blank" class="inline-flex w-full justify-center rounded-md bg-brand-purple px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue hover:text-white focus:outline-none focus:text-white sm:col-start-2">Open the web app</a>
            <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0" @click="openWebAppDialog = false" ref="cancelButtonRef">Cancel</button>
          </div>
        </div>
    </div>
  </div>
</template>
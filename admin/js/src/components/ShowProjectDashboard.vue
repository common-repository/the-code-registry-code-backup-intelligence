<script setup>
import { getCurrentInstance, ref, onMounted, onUnmounted, computed, watch } from 'vue';
import VaultLayout from './layouts/VaultLayout.vue';
import VaultStatus from './widgets/summary/VaultStatus.vue';
import {
    SparklesIcon,
    CodeBracketSquareIcon, 
    CurrencyDollarIcon, 
    ClipboardDocumentCheckIcon,
    DocumentIcon, 
    LockClosedIcon, 
    PuzzlePieceIcon, 
    RectangleStackIcon,
    PaintBrushIcon
} from '@heroicons/vue/20/solid';

const { proxy } = getCurrentInstance()

// Define props
const props = defineProps({
    facet_values: Object,
    all_ready: Boolean
});

// computed property to handle empty values etc
const facet_values = computed(() => {
    return props.facet_values ? props.facet_values : {
      'security': null,
      'cms-detection': null,
      'licenses': null,
      'complexity': null,
      'languages': null,
      'file-types': null,
      'valuation': null,
      'ai-quotient': null
    };
});

// loaders and widgets
import GenericSkeleton from './widgets/summary/Skeleton.vue';
import PollingWidget from './widgets/summary/PollingWidget.vue';

// code that polls the backend for facet status and data
const isPolling = ref(false);
let pollingInterval;

// reference to our vault layout component
const vaultLayout = ref(null);

// hold our language array for the vault layout component
const languagesArray = ref([]);

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
    })

    const body = await response.json()

    if (response.ok) {
      if (body.success && body.data) {
        // for each item in the response, update props.facet_values
        const allReady = ref(true);
        Object.keys(body.data.data).forEach(facet => {
          // ignore "id", "name", "created_at", "version", "code_last_synced", "code_contributors", "subscription_status"
          if (facet === 'id' || facet === 'name' || facet === 'created_at' || facet === 'version' || facet === 'code_last_synced' || facet === 'code_contributors' || facet === 'subscription_status') return;
          
          if (props.facet_values) {
            props.facet_values[facet] = body.data.data[facet].ready ? body.data.data[facet].data : null;
          } else {
            props.facet_values = { [facet]: body.data.data[facet].ready ? body.data.data[facet].data : null };
          }

          if (facet === 'languages' && body.data.data[facet].ready) {
            languagesArray.value = Object.entries(body.data.data[facet].data.by_language).map(([index, languageData]) => ({
              language: languageData.language,
              sourceCount: languageData.sourceCount,
            }));
          }

          if (!body.data.data[facet].ready) {
            allReady.value = false;
          }
        });

        // Check if all facets are ready
        if (allReady.value) {
          isPolling.value = false;
          clearInterval(pollingInterval);
        }
      } else {
        console.log('Invalid response from server. Please try again.');
      }
    } else {
      console.log('An error occurred. Please try again.');
    }
  } catch (error) {
    console.error('Error:', error)
  }
};

onMounted(() => {
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

const emit = defineEmits(['changeView'])
const changeView = (view) => {
  emit('changeView', view)
}
</script>
<template> 
  <VaultLayout :languages="languagesArray" @changeView="changeView">
    <template v-slot="{ previewWebAppDialog }">
      <div class="relative isolate overflow-hidden mt-2 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl">
          <div class="mx-auto max-w-2xl 2xl:mx-0" id="tour-vault-summary">
            <h1 class="mt-2 font-bold tracking-tight text-gray-900 text-2xl sm:text-4xl">
              Your Code Intelligence Summary Report
            </h1>
            <VaultStatus :value="facet_values.status" @changeView="changeView" @previewWebAppDialog="previewWebAppDialog" />
          </div>
          <!-- floating summary block -->
          <div class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-10 mt-6 md:gap-y-16 2xl:mx-0 2xl:mt-10 2xl:max-w-none 2xl:grid-cols-12">
            <div class="relative 2xl:order-last 2xl:col-span-5">
              <h5 class="text-xl sm:text-2xl font-extrabold leading-none tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-black mb-4">Here is everything we know about your code vault.</h5>
              <figure class="border-l border-indigo-600 pl-4 sm:pl-8">
                <blockquote class="font-semibold text-gray-900 p-0 bg-transparent text-base leading-tight lg:text-lg">
                  <p class="text-base mb-3">This is our streamlined view of the data &amp; insights we've generated from your code vault.</p><p class="text-base mb-3">Each insight has buttons to provide more info or see what our AI Assistant Ada has to say about it.</p><p class="text-base mb-3">The most important insights are listed first.</p><p class="text-base">Much more detail is available in in our metrics dashboard and dedicated facet pages (on our main web app).</p>
                </blockquote>
              </figure>
            </div>
            <div class="max-w-xl text-base leading-7 text-gray-700 2xl:col-span-7">
              <ul role="list" class="max-w-xl space-y-8 text-gray-600 mt-4 md:mt-8 2xl:mt-4">
                <!-- security scan -->
                <PollingWidget facet="security" :value="facet_values.security" type="overall">
                  <LockClosedIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing overall security issues...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <PollingWidget facet="security" :value="facet_values.security" type="by_plugin">
                  <PuzzlePieceIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing security issues by plugin...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <PollingWidget facet="security" :value="facet_values.security" type="by_theme">
                  <PaintBrushIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing security issues by theme...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- components -->
                <PollingWidget facet="cms-detection" :value="facet_values.cms_detection">
                  <PuzzlePieceIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing open source components...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- ai quotient -->
                <PollingWidget facet="ai-quotient" :value="facet_values.ai_quotient">
                  <SparklesIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing AI Quotient&#8482;...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- complexity -->
                <PollingWidget facet="complexity" :value="facet_values.complexity">
                  <RectangleStackIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing code complexity...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- valuation -->
                <!-- <PollingWidget facet="valuation" :value="facet_values.valuation">
                  <CurrencyDollarIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing "Cost to Replicate"...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget> -->
                <!-- licenses -->
                <PollingWidget facet="licenses" :value="facet_values.cms_detection">
                  <ClipboardDocumentCheckIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing third party licenses...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- languages -->
                <PollingWidget facet="languages" :value="facet_values.languages">
                  <CodeBracketSquareIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing programming languages...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
                <!-- file types -->
                <PollingWidget facet="file-types" :value="facet_values.file_types">
                  <DocumentIcon class="mt-1 flex-none text-gray-300 h-4 w-4 sm:h-6 sm:w-6" aria-hidden="true" />
                  <div class="w-full">
                    <span class="animate-pulse">Analyzing file types...</span>
                    <GenericSkeleton />
                  </div>
                </PollingWidget>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>
  </VaultLayout>
</template>
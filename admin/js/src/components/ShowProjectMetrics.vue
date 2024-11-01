<script setup>
import { getCurrentInstance, ref, onMounted, onUnmounted, computed, watch } from 'vue';
import VaultLayout from './layouts/VaultLayout.vue';
import VaultStatus from './widgets/metrics/VaultStatus.vue';

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
import GenericSkeleton from './widgets/metrics/Skeleton.vue';
import PollingWidget from './widgets/metrics/PollingWidget.vue';

// code that polls the backend for facet status and data
const isPolling = ref(false);
let pollingInterval;

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
      <div class="mt-5 mb-5 flex flex-col flex-wrap">
        <div class="gap-5 w-full [column-fill:_balance] box-border before:box-inherit after:box-inherit columns-1 sm:columns-2 xl:columns-3 2xl:columns-4">
          <!-- vault status -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <VaultStatus :value="facet_values.status" />
          </div>
          <!-- ai quotient -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="ai-quotient" :value="facet_values.ai_quotient" type="graph" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  AI Quotient&#8482; <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- CMS detector  -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="cms-detection" :value="facet_values.cms_detection" type="table" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Open Source Components <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- code complexity -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100" id="tour-widget-complexity">
            <PollingWidget facet="complexity" :value="facet_values.complexity" type="graph" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Code Complexity <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- languages % -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="languages" :value="facet_values.languages" type="graph" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Most Common Languages (%) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="p-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- languages count -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="languages" :value="facet_values.languages" type="table" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Most Common Languages (#) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- ip valuation  -->
          <!-- <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="valuation" :value="facet_values.valuation" type="table" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  "Cost to Replicate" Estimate <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton message="The 'Cost to replicate' can take a bit longer to calculate than the others, as it's dependent on some of the other data calculating first!" />
              </div>
            </PollingWidget>
          </div> -->
          <!-- security scan -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="security" :value="facet_values.security" type="image" scope="overall" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Security Status <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="security" :value="facet_values.security" type="graph" scope="overall" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Security Vulnerabilities <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="security" :value="facet_values.security" type="graph" scope="by_plugin" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Security Issues by Plugin <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="security" :value="facet_values.security" type="graph" scope="by_theme" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Security Issues by Theme <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- licenses  -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="licenses" :value="facet_values.cms_detection" type="graph" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Third Party Licenses (%) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="licenses" :value="facet_values.cms_detection" type="table" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Third Party Licenses (#) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- file type % -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="file-types" :value="facet_values.file_types" type="graph" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Most Common File Types (%) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="p-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
          <!-- file type count -->
          <div class="break-inside-avoid mb-5 rounded-lg bg-white shadow-lg flex-none grow-0 shrink-0 w-auto h-fit border border-gray-100">
            <PollingWidget facet="file-types" :value="facet_values.file_types" type="table" :previewWebAppDialog="previewWebAppDialog">
              <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="text-sm font-medium text-white">
                  Most Common File Types (#) <span class="text-xs">(analysing...)</span>
                </div>
              </div>
              <div class="py-2 px-5">
                <GenericSkeleton />
              </div>
            </PollingWidget>
          </div>
        </div>
      </div>
    </template>
  </VaultLayout>
</template>
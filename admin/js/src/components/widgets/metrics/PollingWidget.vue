<!-- PollingWidget.vue -->
<script setup>
import { ref, watch, computed } from 'vue';

// Import all possible widget components
import CmsDetectionTable from './facets/CmsDetection/Table.vue';
import LicensesGraph from './facets/Licenses/Graph.vue';
import LicensesTable from './facets/Licenses/Table.vue';
import AIQuotientGraph from './facets/AIQuotient/Graph.vue';
import ComplexityGraph from './facets/Complexity/Graph.vue';
import FileTypesGraph from './facets/FileTypes/Graph.vue';
import FileTypesTable from './facets/FileTypes/Table.vue';
import LanguagesGraph from './facets/Languages/Graph.vue';
import LanguagesTable from './facets/Languages/Table.vue';
import SecurityGraph from './facets/Security/Graph.vue';
import SecurityImage from './facets/Security/Image.vue';
import ValuationTable from './facets/Valuation/Table.vue';

// Map widgets and types to components
const componentMap = {
  'cms-detection-table': CmsDetectionTable,
  'licenses-graph': LicensesGraph,
  'licenses-table': LicensesTable,
  'complexity-graph': ComplexityGraph,
  'file-types-graph': FileTypesGraph,
  'file-types-table': FileTypesTable,
  'languages-graph': LanguagesGraph,
  'languages-table': LanguagesTable,
  'security-graph': SecurityGraph,
  'security-image': SecurityImage,
  'valuation-table': ValuationTable,
  'ai-quotient-graph': AIQuotientGraph
}

const props = defineProps({
  facet: String,
  value: null,
  type: String,
  scope: null,
  previewWebAppDialog: Function
});

const data = ref(null)
const isDataLoaded = computed(() => data.value !== null)

// Watch for changes in the value prop
watch(() => props.value, (newValue) => {
  if (newValue) {
    data.value = newValue;
  }
}, { immediate: true });

const getComponent = () => {
  const key = `${props.facet}-${props.type}`
  return componentMap[key] || null
}
</script>

<template>
  <div>
    <component :is="getComponent()" v-if="isDataLoaded" :data="data" :scope="scope" :previewWebAppDialog="previewWebAppDialog"></component>
    <slot v-else></slot>
  </div>
</template>
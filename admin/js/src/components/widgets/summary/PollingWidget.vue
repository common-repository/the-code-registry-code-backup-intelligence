<!-- PollingWidget.vue -->
<script setup>
import { ref, watch, computed } from 'vue';

// Import all possible widget components
import SecurityWidget from './facets/Security.vue';
import CmsDetectionWidget from './facets/CmsDetection.vue';
import LicensesWidget from './facets/Licenses.vue';
import ComplexityWidget from './facets/Complexity.vue';
import AIQuotientWidget from './facets/AIQuotient.vue';
import LanguagesWidget from './facets/Languages.vue';
import FileTypesWidget from './facets/FileTypes.vue';
import ValuationWidget from './facets/Valuation.vue';

// Map widgets and types to components
const componentMap = {
  'cms-detection': CmsDetectionWidget,
  'licenses': LicensesWidget,
  'complexity': ComplexityWidget,
  'languages': LanguagesWidget,
  'file-types': FileTypesWidget,
  'security': SecurityWidget,
  'valuation': ValuationWidget,
  'ai-quotient': AIQuotientWidget
};

const props = defineProps({
  facet: String,
  value: null,
  type: null
});

const data = ref(null);
const aiInsights = ref(null);
const isDataLoaded = computed(() => data.value !== null);

// Watch for changes in the value prop
watch(() => props.value, (newValue) => {
  if (newValue) {
    data.value = newValue;
    aiInsights.value = newValue.ai_insights;
  }
}, { immediate: true });

const getComponent = () => {
  return componentMap[props.facet] || null
}
</script>

<template>
  <li class="flex gap-x-3" :id="'tour-widget-' + facet">
    <component :is="getComponent()" v-if="isDataLoaded" :data="data" :type="type" :ai_insights="aiInsights"></component>
    <slot v-else></slot>
  </li>
</template>
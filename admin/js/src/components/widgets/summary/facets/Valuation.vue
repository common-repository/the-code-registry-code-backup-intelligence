<script setup>
import { defineProps, watchEffect, ref, computed } from 'vue';
import { CurrencyDollarIcon } from '@heroicons/vue/20/solid';

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

const props = defineProps({
    data: Object,
    ai_insights: Array
});

const showCalcTable = ref(false);
const showLanguageTable = ref(false);
const showInsight = ref(false);

// data for the table
const languageArray = ref([]);

const languageData = computed(() => {
    return languageArray.value
        .filter(lang => lang.language !== "__empty__")
        .sort((a, b) => b.valuation - a.valuation) // Sort by valuation descending
        .slice(0, 10);
});

watchEffect(() => {
    languageArray.value = Object.values(props.data.by_languages);
});

const toggleShowCalcTable = () => {
    showInsight.value = false;
    showLanguageTable.value = false;
    showCalcTable.value = !showCalcTable.value;
}
const toggleShowLanguageTable = () => {
    showInsight.value = false;
    showCalcTable.value = false;
    showLanguageTable.value = !showLanguageTable.value;
}
const toggleShowInsight = () => {
    showCalcTable.value = false;
    showLanguageTable.value = false;
    showInsight.value = !showInsight.value;
}
</script>

<template>
    <CurrencyDollarIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-black" aria-hidden="true" />
    <div class="text-sm sm:pt-1">
        <p>
            <span class="font-semibold text-black text-base">The Code Registry "Cost to Replicate" estimate for your project is {{ props.data.total_range }}.</span> This is a complex algorythm that takes many factors into account across each code vault in your project. You can delve into more detail below or in our full dashboard. <span class="font-semibold">Note: If your project has a lot of commercial third party components (not open source) this may skew our calcuation as they aren't detected by our component scanner.</span>
        </p>
        <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowCalcTable">
                <span v-if="!showCalcTable">Show me more details</span>
                <span v-else>Hide details</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowLanguageTable">
                <span v-if="!showLanguageTable">Show me by language</span>
                <span v-else>Hide the languages</span>
            </button>
            <button type="button" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowInsight">
                <span v-if="!showInsight">What does Ada say?</span>
                <span v-else>Hide Ada's insight</span>
            </button>
        </div>
        <DataSlideout title="Calculation details" v-if="showCalcTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <th class="py-2 pl-4 pr-3 text-xs font-semibold sm:pl-0">Total lines of code</th>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ props.data.factors.total_lines_of_code.toLocaleString() }}</td>
                    </tr>
                    <tr>
                        <th class="py-2 pl-4 pr-3 text-xs font-semibold sm:pl-0">Lines of code discounted</th>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ props.data.factors.total_lines_discounted.toLocaleString() }}</td>
                    </tr>
                    <tr>
                        <th class="py-2 pl-4 pr-3 text-xs font-semibold sm:pl-0">Languages multiple</th>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ props.data.factors.language_multiple }}</td>
                    </tr>
                    <tr>
                        <th class="py-2 pl-4 pr-3 text-xs font-semibold sm:pl-0">Complexity multiple</th>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ props.data.factors.complexity_multiple }}</td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <DataSlideout title="10 largest contributors" v-if="showLanguageTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="langData in languageData" :key="langData.language">
                        <th class="py-2 pl-4 pr-3 text-xs font-semibold sm:pl-0">Estimate for language "{{ langData.language }}"</th>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ langData.valuation_range }}</td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on replication estimates" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
<script setup>
import { defineProps, ref, computed } from 'vue';
import { RectangleStackIcon } from '@heroicons/vue/20/solid';

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

const props = defineProps({
    data: Object,
    ai_insights: Array
});

const showLanguageTable = ref(false);
const showFileTable = ref(false);
const showInsight = ref(false);

// data for the tables
const processedData = computed(() => {
    // Check if data is present
    if (!props.data) {
        return {
            languagesData: null,
            mostComplexFiles: null
        };
    }

    // Processing for the language-specific data
    const languagesData = Object.entries(props.data.by_language).map(([language, langData]) => ({
        language,
        ...langData
    }));

    // Process most and least complex files
    const mostComplexFiles = props.data.most_complex_files;

    return {
        languagesData,
        mostComplexFiles
    };
});

function truncateString(str, maxLength) {
    return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
}

const toggleShowLanguageTable = () => {
    showInsight.value = false;
    showFileTable.value = false;
    showLanguageTable.value = !showLanguageTable.value;
}
const toggleShowFileTable = () => {
    showInsight.value = false;
    showLanguageTable.value = false;
    showFileTable.value = !showFileTable.value;
}
const toggleShowInsight = () => {
    showLanguageTable.value = false;
    showFileTable.value = false;
    showInsight.value = !showInsight.value;
}
</script>

<template>
    <RectangleStackIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" aria-hidden="true" v-if="props.data.summary.overall_complexity_score > 5" />
    <RectangleStackIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" aria-hidden="true" v-else-if="props.data.summary.overall_complexity_score > 1" />
    <RectangleStackIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" aria-hidden="true" v-else />
    <div class="text-sm sm:pt-1">
        <p v-if="props.data.summary.overall_complexity_score > 5">
            <span class="font-semibold text-red-600 text-base">The Code Registry Complexity Score for your entire project is {{ Number(props.data.summary.overall_complexity_score).toFixed(2) }}, which is quite high. This should be reviewed to see if it's manageable or can be reduced.</span> Our system calculates the complexity across every file in every code vault and then calculates an overall score for your project. You can find out which languages and files contribute the most to the high complexity below or in the full dashboard.
        </p>
        <p v-else-if="props.data.summary.overall_complexity_score > 1">
            <span class="font-semibold text-green-600 text-base">The Code Registry Complexity Score for your entire project is {{ Number(props.data.summary.overall_complexity_score).toFixed(2) }}, which is in our ideal range!</span> Our system calculates the complexity across every file in every code vault and then calculates an overall score for your project. You can find out which languages and files contribute the most to your project's complexity below or in the full dashboard.
        </p>
        <p v-else>
            <span class="font-semibold text-orange-600 text-base">The Code Registry Complexity Score for your entire project is {{ Number(props.data.summary.overall_complexity_score).toFixed(2) }}, which is very low. This may indicate code that is too simplistic.</span> Our system calculates the complexity across every file in every code vault and then calculates an overall score for your project. You can find out which languages and files contribute the most to your project's complexity below or in the full dashboard.
        </p>
        <div class="flex gap-x-2 flex-wrap gap-y-2 md:flex-nowrap sm:mt-2">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowLanguageTable">
                <span v-if="!showLanguageTable">Show me by language</span>
                <span v-else>Hide the language data</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowFileTable">
                <span v-if="!showFileTable">Show me the most complex files</span>
                <span v-else>Hide the files</span>
            </button>
            <button type="button" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowInsight">
                <span v-if="!showInsight">What does Ada say?</span>
                <span v-else>Hide Ada's insight</span>
            </button>
        </div>
        <DataSlideout title="Complexity by language" v-if="showLanguageTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <thead>
                    <tr>
                        <th scope="col" class="text-left text-xs font-semibold py-2 pr-2 sm:py-3.5 sm:pr-3">Language</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">Lines of code</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5 hidden md:table-cell">File count</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5 hidden md:table-cell">Function count</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">Average CC per file</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="languageData in processedData.languagesData" :key="languageData.language">
                        <td class="text-xs font-medium py-2 pr-2 sm:py-2 sm:pr-3">{{ languageData.language }}</td>
                        <td class="text-xs text-gray-200 px-2 py-2 sm:px-3 sm:py-2">{{ languageData.nloc }}</td>
                        <td class="text-xs text-gray-200 px-2 py-2 sm:px-3 sm:py-2 hidden md:table-cell">{{ languageData.file_count }}</td>
                        <td class="text-xs text-gray-200 px-2 py-2 sm:px-3 sm:py-2 hidden md:table-cell">{{ languageData.function_count }}</td>
                        <td class="font-bold text-blue-500 px-2 py-2 text-sm sm:px-3 sm:py-2 sm:text-md">
                            {{ Number(languageData.average_ccn_per_file).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <DataSlideout title="10 most complex files" v-if="showFileTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <thead>
                    <tr>
                        <th scope="col" class="text-left text-xs font-semibold py-2 pr-2 sm:py-3.5 sm:pr-3">File</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">Lines of code</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 hidden sm:px-3 sm:py-3.5 sm:table-cell hidden md:table-cell">Function count</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">CC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="file in processedData.mostComplexFiles" :key="file.name">
                        <td class="text-xs font-medium py-2 pr-2 sm:py-2 sm:pr-3">
                            <span class="block truncate max-w-[180px] sm:max-w-[220px] md:max-w-none">{{ truncateString(file.name, 32) }}</span>
                        </td>
                        <td class="text-xs text-gray-200 px-2 py-2 sm:px-3 sm:py-2">{{ file.nloc }}</td>
                        <td class="text-xs text-gray-200 px-2 py-2 hidden sm:px-3 sm:py-2 sm:table-cell hidden md:table-cell">{{ file.functions }}</td>
                        <td class="font-bold text-blue-400 px-2 py-2 text-sm sm:px-3 sm:py-2 sm:text-md">
                            {{ Number(file.ccn).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on code complexity" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
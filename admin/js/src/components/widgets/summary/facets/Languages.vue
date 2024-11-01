<script setup>
import { defineProps, watchEffect, ref, computed } from 'vue';
import { CodeBracketSquareIcon } from '@heroicons/vue/20/solid';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend);

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

const props = defineProps({
    data: Object,
    ai_insights: Array
});

const showGraph = ref(false);
const showTable = ref(false);
const showInsight = ref(false);

// data for the intro
const totalLanguages = computed(() => {
    const languages = props.data.by_language;

    // Filter the languages to exclude those starting with an underscore and count them
    const languageCount = Object.values(languages)
        .filter(lang => typeof lang === 'object' && lang.language && !lang.language.startsWith('_'))
        .length;

    return languageCount;
});

const topThreeLanguages = computed(() => {
    const languages = props.data.by_language;

    // Convert the object to an array, filter out underscore-prefixed languages, sort by sourceCount, and pick the top three
    const sortedLanguages = Object.values(languages)
        .filter(lang => typeof lang === 'object' && lang.language && !lang.language.startsWith('_'))
        .sort((a, b) => b.sourceCount - a.sourceCount)
        .slice(0, 3);

    // Extract the language names and join them into a sentence
    return sortedLanguages.map(lang => lang.language).join(', ');
});

// chart data
// we want to remove any language from props.data.by_language starting with _
// and limit to 10
const languages = computed(() => {
    if (props.data && props.data.by_language) {
        return Object.entries(props.data.by_language)
            .filter(([_, languageData]) => {
                const name = languageData.language ?? languageData.name;
                return !name.startsWith('_') && languageData.sourceCount > 0;
            })
            .map(([_, languageData]) => {
                const name = languageData.language ?? languageData.name;
                return {
                    name,
                    ...languageData
                };
            })
            .slice(0, 10);
    }
    return [];
});

// Computed property for totalSourceCount
const totalSourceCount = computed(() => {
    return languages.value.reduce((total, language) => total + language.sourceCount, 0);
});

// Computed property for languageChartData
const languageChartData = computed(() => {
    const data = {
        labels: [],
        datasets: [{
            backgroundColor: [],
            data: []
        }]
    };

    languages.value.forEach((language, index) => {
        const percentage = ((language.sourceCount / totalSourceCount.value) * 100).toFixed(2);
        const color = generateColor(index);
        data.labels.push(`${language.name} (${percentage}%)`);
        data.datasets[0].backgroundColor.push(color);
        data.datasets[0].data.push(percentage);
    });

    return data;
});

const languageChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    height: 200,
    scales: {
        y: {
            ticks: { color: '#fff' }
        },
        x: {
            ticks: { color: '#fff' }
        }
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#fff',
                font: {
                    size: 12
                }
            }
        }
    }
};

function generateColor(index) {
    const hue = index * 137.508; // Use golden angle approximation for even distribution
    return `hsl(${hue % 360}, 50%, 60%)`; // HSL: Hue, Saturation, Lightness
}

const toggleShowGraph = () => {
    showTable.value = false;
    showInsight.value = false;
    showGraph.value = !showGraph.value;
}
const toggleShowTable = () => {
    showGraph.value = false;
    showInsight.value = false;
    showTable.value = !showTable.value;
}
const toggleShowInsight = () => {
    showGraph.value = false;
    showTable.value = false;
    showInsight.value = !showInsight.value;
}
</script>

<template>
    <CodeBracketSquareIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-black" aria-hidden="true" />
    <div class="text-sm sm:pt-1">
        <p>
            <span class="font-semibold text-black text-base">There are a total of {{ totalLanguages.toLocaleString() }} programming languages across your entire project.</span> The three most used languages are <span class="font-semibold">{{ topThreeLanguages }}</span>.
        </p>
        <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowGraph">
                <span v-if="!showGraph">Show me a chart</span>
                <span v-else>Hide the chart</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowTable">
                <span v-if="!showTable">Show me a table</span>
                <span v-else>Hide the table</span>
            </button>
            <button type="button" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowInsight">
                <span v-if="!showInsight">What does Ada say?</span>
                <span v-else>Hide Ada's insight</span>
            </button>
        </div>
        <DataSlideout title="10 most used languages by lines of code" v-if="showGraph">
            <div class="w-[250px] sm:w-full">
                <Pie
                    id="vault-facet-languages-chart"
                    :options="languageChartOptions"
                    :data="languageChartData"
                />
            </div>
        </DataSlideout>
        <DataSlideout title="10 most used languages by lines of code" v-if="showTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Language</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold"># files</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold"># lines of code</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="language in languages" :key="language.language">
                        <td class=" py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ language.language }}</td>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ language.fileCount.toLocaleString() }}</td>
                        <td class=" px-3 py-2 text-xs text-gray-200">{{ language.sourceCount.toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on languages" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
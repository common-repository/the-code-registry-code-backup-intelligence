<script setup>
import { defineProps, watchEffect, ref, computed } from 'vue';
import { DocumentIcon } from '@heroicons/vue/20/solid';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
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
// Counting the total number of file types
const totalFileTypes = computed(() => {
    return Object.keys(props.data.file_types).length;
});

// Finding the top three file types by size
const topThreeFileTypesBySize = computed(() => {
    const fileTypes = props.data.file_types;

    // Convert the object to an array, sort by file_size, and pick the top three
    const sortedFileTypes = Object.entries(fileTypes)
        .sort(([, a], [, b]) => b.file_size - a.file_size)
        .slice(0, 3)
        .map(([type]) => type); // Extracting the file type names

    return sortedFileTypes.join(', ');
});

// data for the chart
// Computed property for chart data
const filetypeChartData = computed(() => {
    if (!props.data || !props.data.file_types) {
        return { labels: [], datasets: [{ backgroundColor: [], data: [] }] };
    }
    return generateChartData(props.data.file_types);
});

const filetypeChartOptions = {
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

// Function to generate chart data
function generateChartData(fileTypes) {
    const sortedFileTypes = Object.entries(fileTypes)
        .sort((a, b) => b[1].file_size - a[1].file_size)
        .slice(0, 10);

    const totalSize = sortedFileTypes.reduce((total, [_, fileInfo]) => total + fileInfo.file_size, 0);
    const chartData = {
        labels: [],
        datasets: [{
            backgroundColor: [],
            data: []
        }]
    };

    sortedFileTypes.forEach(([extension, fileInfo], index) => {
        const percentage = ((fileInfo.file_size / totalSize) * 100).toFixed(2);
        const color = generateColor(index);
        
        chartData.labels.push(`${extension} (${percentage}%)`);
        chartData.datasets[0].backgroundColor.push(color);
        chartData.datasets[0].data.push(percentage);
    });

    return chartData;
}

// data for the table
// we only want 20 file types and we want file_size sorted descending
const file_types = computed(() => {
    if (props.data && props.data.file_types && typeof props.data.file_types === 'object') {
        return Object.entries(props.data.file_types)
            .map(([type, data]) => ({
                type,
                ...data,
                formattedFileSize: formatFileSize(data.file_size) // Add a formatted file size
            }))
            .sort((a, b) => b.file_size - a.file_size)
            .slice(0, 20);
    }
    return [];
});

function formatFileSize(bytes) {
    const MB = 1024 * 1024;
    const GB = 1024 * MB;

    if (bytes > GB) {
        return (bytes / GB).toFixed(2) + ' GB';
    } else {
        return (bytes / MB).toFixed(2) + ' MB';
    }
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
    <DocumentIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-black" aria-hidden="true" />
    <div class="text-sm sm:pt-1">
        <p>
            <span class="font-semibold text-black text-base">There are a total of {{ totalFileTypes.toLocaleString() }} file types.</span> The three most used file types are <span class="font-semibold">{{ topThreeFileTypesBySize }}</span>.
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
        <DataSlideout title="10 most used file types by size" v-if="showGraph">
            <div class="w-[250px] sm:w-full">
                <Doughnut
                    id="vault-facet-filetype-chart"
                    :options="filetypeChartOptions"
                    :data="filetypeChartData"
                />
            </div>
        </DataSlideout>
        <DataSlideout title="10 most used file types by size" v-if="showTable">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">File type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">File size</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">File #</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="(type_data) in file_types" :key="type_data.type">
                        <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ type_data.type }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ type_data.formattedFileSize }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ type_data.file_count.toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on security issues" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
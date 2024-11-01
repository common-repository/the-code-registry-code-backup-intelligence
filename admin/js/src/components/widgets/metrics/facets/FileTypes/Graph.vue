<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

const { proxy } = getCurrentInstance()

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend);

const props = defineProps({
  data: Object
});

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
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
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
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Most Common File Types (%)
            </div>
            <div>
                <Popper arrow placement="right" content="The 10 largest file types within your codebase presented on a chart">
                    <button type="button" class="font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center">
                <Doughnut
                    id="vault-facet-filetype-chart"
                    :options="filetypeChartOptions"
                    :data="filetypeChartData"
                />
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="file-types" />
        </div>
    </div>
</template>
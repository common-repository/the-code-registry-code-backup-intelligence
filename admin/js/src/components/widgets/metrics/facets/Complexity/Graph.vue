<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

const { proxy } = getCurrentInstance()

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend);

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});

const complexityChartData = computed(() => {
    if (!props.data || !props.data.summary) {
        return { labels: [], datasets: [] };
    }

    // Extracting the summary data
    const summary = props.data.summary;

    // Calculating ignored lines
    let lines_ignored = props.data.languages.overall.totalSourceCount - summary.overall_nloc;
    if (lines_ignored < 0) lines_ignored = 0;

    // Labels for the chart
    const labels = ['Cyclomatic complexity', 'Lines of code', 'Total function count', 'Lines not analyzed'];

    // Data for the chart
    const data = [
        summary.overall_ccn,
        summary.overall_nloc,
        summary.overall_function_count,
        lines_ignored
    ];

    return {
        labels: labels,
        datasets: [
            {
                label: 'Count',
                backgroundColor: labels.map((_, index) => generateColor(index)),
                data: data
            }
        ]
    };
});

const complexityChartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 1,
    plugins: {
        legend: {
            display: false
        }
    }
};

function generateColor(index) {
    const hue = index * 137.508; // Use golden angle approximation for even distribution
    return `hsl(${hue % 360}, 50%, 60%)`; // HSL: Hue, Saturation, Lightness
}
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap" id="tour-widget-complexity-header">
            <div class="text-sm font-medium text-white">
                Code Complexity
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('complexity')">
                    More info
                </a>
                <Popper arrow placement="right" content="This is a summary of your code's complexity. Click 'More info' for more details.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5" id="tour-widget-complexity-content">
            <div class="flex items-center">
                <Bar
                    id="vault-facet-complexity-chart"
                    :options="complexityChartOptions"
                    :data="complexityChartData"
                />
            </div>
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <td class=" py-2 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-0 text-center">The Code Registry Complexity Score</td>
                            <td class=" px-3 py-2 text-md font-bold text-blue-500">{{ Number(Number(props.data.summary.overall_complexity_score).toFixed(2)).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" id="tour-widget-complexity-over-time" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="complexity" />
        </div>
    </div>
</template>
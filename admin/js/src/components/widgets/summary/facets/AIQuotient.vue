<script setup>
import { defineProps, ref, computed } from 'vue';
import { SparklesIcon } from '@heroicons/vue/20/solid';

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

// Chart.js imports
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: Object,
    ai_insights: Array
});

// Computed property for chart data
const aiQuotientChartData = computed(() => {
    if (!props.data) {
        return { labels: [], datasets: [{ backgroundColor: [], data: [] }] };
    }
    const improvablePercentage = props.data.ai_quotient_percentage;
    const remainingPercentage = 100 - improvablePercentage;
    return {
        labels: ['Can be improved by AI', 'Remaining code'],
        datasets: [{
            backgroundColor: ['#4eba6b', '#e63922'],
            data: [improvablePercentage, remainingPercentage]
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#ffffff',
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return `${context.label}: ${context.formattedValue}%`;
                }
            }
        }
    },
    cutout: '70%'
};

const totalIssueCount = computed(() => {
    return Object.values(props.data.issue_type_counts).reduce((total, count) => total + count, 0);
});

const showChart = ref(false);
const showIssueTypes = ref(false);
const showInsight = ref(false);

// data for the tables
const processedData = computed(() => {
    if (!props.data) {
        return {
            issueTypeData: null
        };
    }

    const issueTypeData = Object.keys(props.data.issue_type_counts).map(issueType => ({
        issueType,
        count: props.data.issue_type_counts[issueType],
        lines: props.data.issue_type_lines[issueType] || props.data.issue_type_counts[issueType]
    }));

    issueTypeData.sort((a, b) => b.lines - a.lines);

    return {
        issueTypeData
    };
});

function truncateString(str, maxLength) {
    return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
}

const toggleShowChart = () => {
    showInsight.value = false;
    showIssueTypes.value = false;
    showChart.value = !showChart.value;
}
const toggleShowIssueTypes = () => {
    showInsight.value = false;
    showChart.value = false;
    showIssueTypes.value = !showIssueTypes.value;
}
const toggleShowInsight = () => {
    showChart.value = false;
    showIssueTypes.value = false;
    showInsight.value = !showInsight.value;
}
</script>

<template>
    <SparklesIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" aria-hidden="true" v-if="props.data.ai_quotient_percentage >= 75" />
    <SparklesIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" aria-hidden="true" v-else-if="props.data.ai_quotient_percentage >= 50" />
    <SparklesIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-yellow-600" aria-hidden="true" v-else-if="props.data.ai_quotient_percentage >= 25" />
    <SparklesIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" aria-hidden="true" v-else />
    <div class="text-sm sm:pt-1">
        <p v-if="props.data.ai_quotient_percentage >= 75">
            <span class="font-semibold text-red-600 text-base">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code, which is very high.</span> Our system scans your code for common bad practises, coding quality and structure issues that AI is very good at improving. A score this high means that we found a lot of these types of issues and this should be looked at.
        </p>
        <p v-else-if="props.data.ai_quotient_percentage >= 50">
            <span class="font-semibold text-orange-600 text-base">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code, which is quite high.</span> Our system scans your code for common bad practises, coding quality and structure issues that AI is very good at improving. A score this high means that we found a lot of these types of issues and this should be looked at.
        </p>
        <p v-else-if="props.data.ai_quotient_percentage >= 25">
            <span class="font-semibold text-yellow-600 text-base">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code, which is high.</span> Our system scans your code for common bad practises, coding quality and structure issues that AI is very good at improving. A score like this isn't too much to be worried about but still worth the time to review what we've found!
        </p>
        <p v-else>
            <span class="font-semibold text-green-600 text-base">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code, which is an OK amount.</span> Our system scans your code for common bad practises, coding quality and structure issues that AI is very good at improving. This is an OK score for this metric, but still worth the time to review what we've found!
        </p>
        <div class="flex gap-x-2 flex-wrap gap-y-2 md:flex-nowrap sm:mt-2">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowChart">
                <span v-if="!showChart">Show me a chart</span>
                <span v-else>Hide the chart</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowIssueTypes">
                <span v-if="!showIssueTypes">Show me the types of issues</span>
                <span v-else>Hide the types of issues</span>
            </button>
            <button type="button" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowInsight">
                <span v-if="!showInsight">What does Ada say?</span>
                <span v-else>Hide Ada's insight</span>
            </button>
        </div>
        <DataSlideout title="AI Quotient details" v-if="showChart">
            <div class="flex items-center justify-center text-white" style="height: 200px;">
                <Doughnut
                    :options="chartOptions"
                    :data="aiQuotientChartData"
                />
            </div>
            <div class="flex items-center text-white">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-white sm:pl-0">Total issues found</th>
                            <td class="px-3 py-2 text-xs text-white">{{ totalIssueCount.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-white sm:pl-0">Affected lines of code</th>
                            <td class="px-3 py-2 text-xs text-white">{{ props.data.total_issue_lines.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-white sm:pl-0">Total lines of code</th>
                            <td class="px-3 py-2 text-xs text-white">{{ props.data.total_lines_of_code.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-base font-bold text-white sm:pl-0 text-center" colspan="2">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </DataSlideout>
        <DataSlideout title="Types of issues found" v-if="showIssueTypes">
            <table class="min-w-full divide-y divide-gray-200 text-white">
                <thead>
                    <tr>
                        <th scope="col" class="text-left text-xs font-semibold py-2 pr-2 sm:py-3.5 sm:pr-3">Issue type</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">Count</th>
                        <th scope="col" class="text-left text-xs font-semibold px-2 py-2 sm:px-3 sm:py-3.5">Total lines</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="(item, index) in processedData.issueTypeData" :key="index">
                        <td class="text-xs font-medium py-2 pr-2 sm:py-2 sm:pr-3">
                            <span class="block truncate max-w-[180px] sm:max-w-[220px] md:max-w-none">{{ truncateString(item.issueType, 40) }}</span>
                        </td>
                        <td class="text-xs text-gray-200 px-2 py-2 sm:px-3 sm:py-2">
                            {{ Number(item.count).toLocaleString() }}
                        </td>
                        <td class="font-bold text-blue-400 px-2 py-2 text-sm sm:px-3 sm:py-2 sm:text-md">
                            {{ Number(item.lines).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on code complexity" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
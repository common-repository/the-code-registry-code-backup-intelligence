<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';

const { proxy } = getCurrentInstance()

// Chart.js imports
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
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
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                AI Quotient&#8482;
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('aiq')">
                    More info
                </a>
                <Popper arrow placement="right" content="This is the percentage of your code that can be improved by AI. Click 'More info' for more details.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center justify-center" style="height: 200px;">
                <Doughnut
                    :options="chartOptions"
                    :data="aiQuotientChartData"
                />
            </div>
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Total issues found</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ totalIssueCount.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Affected lines of code</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ props.data.total_issue_lines.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Total lines of code</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ props.data.total_lines_of_code.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-base font-bold text-gray-900 sm:pl-0 text-center" colspan="2">AI could improve {{ Math.round(props.data.ai_quotient_percentage) }}% of your code</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="ai-quotient" />
        </div>
    </div>
</template>
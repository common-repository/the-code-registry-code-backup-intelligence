<script setup>
import { defineProps, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

const { proxy } = getCurrentInstance()

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend);

const props = defineProps({
  data: Object
});

// we want to remove any language from props.data.by_language starting with _
// and limit to 10
const languages = props.data.by_language
    .filter(language => !language.language.startsWith('_'))
    .slice(0, 10);

const totalSourceCount = languages.reduce((total, language) => total + language.sourceCount, 0);

const languageChartData = {
    labels: [],
    datasets: [{
        backgroundColor: [],
        data: []
    }]
};

languages.forEach((language, index) => {
    const percentage = ((language.sourceCount / totalSourceCount) * 100).toFixed(2);
    const color = generateColor(index);
    
    languageChartData.labels.push(`${language.language} (${percentage}%)`);
    languageChartData.datasets[0].backgroundColor.push(color);
    languageChartData.datasets[0].data.push(percentage);
});

const languageChartOptions = {
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
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Most Common Languages (%)
            </div>
            <div>
                <Popper arrow placement="right" content="The 10 most common programming languages within your codebase presented on a chart">
                    <button type="button" class="font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center">
                <Pie
                    id="vault-facet-languages-chart"
                    :options="languageChartOptions"
                    :data="languageChartData"
                />
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="languages" />
        </div>
    </div>
</template>
<script setup>
import { defineProps, ref, computed, getCurrentInstance } from 'vue';
import { PuzzlePieceIcon } from '@heroicons/vue/20/solid';

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, RadialLinearScale, ArcElement, BarElement, Tooltip, Legend);

const props = defineProps({
    data: Object,
    ai_insights: Array
});

const { proxy } = getCurrentInstance()

const showGraph = ref(false);
const showTable = ref(false);
const showInsight = ref(false);

// components
const componentsData = computed(() => {
    const components = props?.data?.components ?? {};
    const totalComponents = Object.keys(props?.data?.components ?? {}).length;
    let outdatedComponents = 0;
    let currentComponents = 0;

    // Count the number of outdated components
    for (const key in components) {
        if (components[key].current_version !== components[key].latest_version) {
            outdatedComponents++;
        }
    }

    currentComponents = totalComponents - outdatedComponents;

    // Generate colors for the chart
    const totalComponentsColor = generateColor(0); // First color for total components
    const outdatedComponentsColor = generateColor(1); // Second color for outdated components

    // Bar chart data
    const complexityChartData = {
        labels: ['Number of components', 'Outdated components'],
        datasets: [
            {
                label: 'Count',
                backgroundColor: [totalComponentsColor, outdatedComponentsColor],
                data: [totalComponents, outdatedComponents]
            }
        ]
    };

    // Bar chart options
    const complexityChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        height: 300,
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
                display: false
            }
        }
    };

    return {
        currentComponents,
        outdatedComponents,
        complexityChartData,
        complexityChartOptions
    };
});

function generateColor(index) {
    const hue = (240 + index * 137.508) % 360; // Adjust the base hue to blue
    return `hsl(${hue}, 70%, 60%)`; // HSL: Hue, Saturation, Lightness
}

// sorted components by size for the table
const sortedComponents = computed(() => {
    return Object.entries(props.data.components)
        .map(([name, component]) => ({ name, ...component }))
        .sort((a, b) => b.total_lines_of_code - a.total_lines_of_code)
        .slice(0, 10);
});

const toggleShowGraph = () => {
    showInsight.value = false;
    showTable.value = false;
    showGraph.value = !showGraph.value;
}
const toggleShowTable = () => {
    showInsight.value = false;
    showGraph.value = false;
    showTable.value = !showTable.value;
}
const toggleShowInsight = () => {
    showGraph.value = false;
    showTable.value = false;
    showInsight.value = !showInsight.value;
}
</script>

<template>
    <PuzzlePieceIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" aria-hidden="true" v-if="componentsData.outdatedComponents > 10" />
    <PuzzlePieceIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" aria-hidden="true" v-else-if="componentsData.outdatedComponents > 0" />
    <PuzzlePieceIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" aria-hidden="true" v-else />
    <div class="text-sm sm:pt-1">
        <p v-if="componentsData.outdatedComponents > 0">
            <span class="font-semibold text-red-600 text-base">There <span v-if="componentsData.outdatedComponents > 1">are</span><span v-else>is</span> {{ Number(componentsData.outdatedComponents).toLocaleString() }} outdated third-party <span v-if="componentsData.outdatedComponents > 1">dependencies</span><span v-else>dependency</span>, <span v-if="componentsData.outdatedComponents > 1">these</span><span v-else>this</span> should be reviewed to reduce potential security risks and improve stability.</span>
            &nbsp;<span v-if="componentsData.currentComponents > 0">We've also detected {{ Number(componentsData.currentComponents).toLocaleString() }} <span v-if="componentsData.currentComponents > 1">dependencies</span><span v-else>dependency</span> that <span v-if="componentsData.currentComponents > 1">are</span><span v-else>is</span> up to date.</span>
        </p>
        <p v-else>
            <span class="font-semibold text-green-600 text-base">We've found no outdated third party dependencies across your entire project. This is really good!</span>
            &nbsp;<span v-if="componentsData.currentComponents > 0">There <span v-if="componentsData.currentComponents > 1">are</span><span v-else>is</span> {{ Number(componentsData.currentComponents).toLocaleString() }} third party <span v-if="componentsData.currentComponents > 1">packages and dependencies</span><span v-else>package or dependency</span> in total which are up to date. It's still worth reviewing these to see if they are still needed.</span>
        </p>
        <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowGraph">
                <span v-if="!showGraph">Show me a chart</span>
                <span v-else>Hide the chart</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowTable">
                <span v-if="!showTable">Show me the largest dependencies</span>
                <span v-else>Hide the largest dependencies</span>
            </button>
            <button type="button" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowInsight">
                <span v-if="!showInsight">What does Ada say?</span>
                <span v-else>Hide Ada's insight</span>
            </button>
        </div>
        <DataSlideout title="Number of outdated components" v-if="showGraph">
            <div class="text-white">
                <div class="w-[250px] sm:w-full">
                    <Bar
                        id="vault-facet-complexity-chart"
                        :options="componentsData.complexityChartOptions"
                        :data="componentsData.complexityChartData"
                    />
                </div>
            </div>
        </DataSlideout>
        <DataSlideout title="Largest open source components" v-if="showTable">
            <table class="min-w-full divide-y divide-gray-400 text-white" v-if="sortedComponents && sortedComponents.length">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Component</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold hidden md:table-cell">Total Files</th>
                        <!-- <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Latest Version</th> -->
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Current Version</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Total Lines of Code</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="component in sortedComponents" :key="name">
                        <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ component.name }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200 hidden md:table-cell">{{ component.total_files.toLocaleString() }}</td>
                        <!-- <td class=" px-3 py-2 text-xs text-gray-200">{{ component.latest_version }}</td> -->
                        <td class="px-3 py-2 text-xs text-gray-200">{{ component.current_version }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ component.total_lines_of_code.toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="py-2 text-center" v-else>
                <div class="flex justify-center items-center mb-2">
                    <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" />
                </div>
                <p class="text-xs italic">We didn't detect any Open Source Software components in your code. This isn't a bad thing!</p>
            </div>
        </DataSlideout>
        <InsightSlideout title="Ada's insights on open source components" :ai_insights="ai_insights" v-if="showInsight" />
    </div>
</template>
<script setup>
import { defineProps, computed, watchEffect, getCurrentInstance, ref } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

// Chart.js imports
import { 
    Chart as ChartJS, 
    CategoryScale, 
    LinearScale, 
    RadialLinearScale, 
    ArcElement, 
    BarElement, 
    Tooltip, 
    Legend,
    PieController
} from 'chart.js';
import { Bar, Pie } from 'vue-chartjs';
ChartJS.register(
    CategoryScale, 
    LinearScale, 
    RadialLinearScale, 
    ArcElement, 
    BarElement, 
    Tooltip, 
    Legend,
    PieController
);

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    scope: {
        type: String,
        default: 'overall' // 'overall', 'by_plugin', 'by_theme'
    },
    previewWebAppDialog: {
        type: Function,
        required: true
    }
});

console.log(props.scope);

const { proxy } = getCurrentInstance();

// State variables for toggling visibility
const showGraph = ref(false);
const showTable = ref(false);

// Initialize counts
let overallCount = 0;
let otherCount = 0;

// Calculate the overall count
watchEffect(() => {
    if (props.scope !== 'overall') return;

    const severity = props.data.severity_count || {};

    // Ensure all severity levels are present
    ['ERROR', 'WARNING', 'INFO', 'OTHER'].forEach(level => {
        if (typeof severity[level] !== 'number') {
            severity[level] = 0;
        }
    });

    overallCount = severity.ERROR + severity.WARNING + severity.INFO + severity.OTHER;
    otherCount = severity.WARNING + severity.INFO + severity.OTHER;
});

// Function to generate colors
function generateColor(index) {
    const hue = index * 137.508; // Golden angle approximation for even distribution
    return `hsl(${hue % 360}, 50%, 60%)`; // HSL: Hue, Saturation, Lightness
}

// ==================== Computations for "overall" ====================

// Compute chart data for overall security issues
const overallChartData = computed(() => {
    if (props.scope !== 'overall') return {};

    const labels = ['INFO', 'WARNING', 'ERROR'];
    const backgroundColors = labels.map((_, index) => generateColor(index));

    return {
        labels: labels,
        datasets: [
            {
                label: 'Count',
                backgroundColor: backgroundColors,
                data: [
                    props.data.severity_count.INFO || 0,
                    props.data.severity_count.WARNING || 0,
                    props.data.severity_count.ERROR || 0
                ]
            }
        ]
    };
});

// ==================== Computations for "by_plugin" and "by_theme" ====================

// Generalized function to compute data for plugins or themes
const computeEntityData = (entityType) => {
    // entityType: 'plugins' or 'themes'
    const data = props.data.by_path || {};
    const entityCounts = {};

    // Initialize counts for each entity
    Object.keys(data).forEach(path => {
        const regex = new RegExp(`^(?:wp-content|web/app)/${entityType}/([^/]+)/`);
        const match = path.match(regex);
        if (match) {
            const entityName = match[1];
            const entityInfo = data[path];

            // Initialize entity entry if not present
            if (!entityCounts[entityName]) {
                entityCounts[entityName] = 0;
            }

            // Sum the total issues
            const severity = entityInfo.severity || {};
            entityCounts[entityName] += (severity.ERROR || 0) + (severity.WARNING || 0) + (severity.INFO || 0);
        }
    });

    return entityCounts;
};

// Compute plugin counts
const pluginCounts = computed(() => {
    if (props.scope !== 'by_plugin') return {};
    return computeEntityData('plugins');
});

// Compute theme counts
const themeCounts = computed(() => {
    if (props.scope !== 'by_theme') return {};
    return computeEntityData('themes');
});

// Sorted entities by total issues descending, limited to top 20
const sortedEntities = computed(() => {
    let counts = {};
    if (props.scope === 'by_plugin') {
        counts = pluginCounts.value;
    } else if (props.scope === 'by_theme') {
        counts = themeCounts.value;
    } else {
        return [];
    }

    return Object.entries(counts)
        .map(([name, total]) => ({ name, total }))
        .sort((a, b) => b.total - a.total)
        .slice(0, 20); // Limit to top 20
});

// Compute chart data for entities (plugins or themes)
const entityChartData = computed(() => {
    if (props.scope !== 'by_plugin' && props.scope !== 'by_theme') return {};

    const labels = sortedEntities.value.map(entity => entity.name);
    const data = sortedEntities.value.map(entity => entity.total);
    const backgroundColors = sortedEntities.value.map((_, index) => generateColor(index));

    return {
        labels: labels,
        datasets: [
            {
                label: 'Total Issues',
                backgroundColor: backgroundColors,
                data: data
            }
        ]
    };
});

// ==================== Chart Options ====================

const securityChartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    aspectRatio: 1,
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                color: '#000' // Black text for legends
            }
        },
        tooltip: {
            enabled: true
        }
    },
    scales: {
        y: {
            ticks: {
                color: '#000' // Black text for Y-axis
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.1)' // Light grid lines
            }
        },
        x: {
            ticks: {
                color: '#000' // Black text for X-axis
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.1)' // Light grid lines
            }
        }
    }
};
</script>

<template>
    <div v-if="props.data" class="bg-white rounded-lg overflow-hidden shadow">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white" v-if="props.scope == 'overall'">Security Vulnerabilities</div>
            <div class="text-sm font-medium text-white" v-else-if="props.scope == 'by_plugin'">Security Issues by Plugin</div>
            <div class="text-sm font-medium text-white" v-else-if="props.scope == 'by_theme'">Security Issues by Theme</div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('security')">
                    More info
                </a>
                <Popper arrow placement="right" content="Your snapshot security vulnerability status based on our analysis.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="p-5">
            <template v-if="props.scope === 'overall'">
                <div v-if="overallCount">
                    <!-- Bar Chart -->
                    <div class="mb-4">
                        <Bar
                            :options="securityChartOptions"
                            :data="overallChartData"
                        />
                    </div>
                    <!-- Table -->
                    <div>
                        <table class="min-w-full divide-y divide-gray-300 text-xs">
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-left text-black font-medium sm:pl-0">Total rules checked</td>
                                    <td class="px-3 py-2 text-left text-gray-700">4,051</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-left text-black font-medium sm:pl-0">Urgent issues found</td>
                                    <td class="px-3 py-2 text-left text-gray-700">{{ props.data.severity_count.ERROR.toLocaleString() }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-left text-black font-medium sm:pl-0">Other issues found</td>
                                    <td class="px-3 py-2 text-left text-gray-700">{{ otherCount.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else class="text-center">
                    <div class="flex justify-center items-center mb-2">
                        <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" alt="No results found" />
                    </div>
                    <p class="text-sm italic text-gray-600">We didn't find any security vulnerabilities in your code. This is great!</p>
                </div>
            </template>

            <!-- ==================== By Plugin Scope ==================== -->
            <template v-else-if="props.scope === 'by_plugin'">
                <div v-if="sortedEntities.length">
                    <!-- Pie Chart -->
                    <div class="mb-4">
                        <Pie
                            :options="securityChartOptions"
                            :data="entityChartData"
                        />
                    </div>
                    <!-- Table -->
                    <div>
                        <table class="min-w-full divide-y divide-gray-400">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">Plugin</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Total Issues</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-400">
                                <tr v-for="plugin in sortedEntities" :key="plugin.name">
                                    <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ plugin.name }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-500">{{ plugin.total.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                            Displaying top 20 plugins.
                        </div>
                    </div>
                </div>
                <div v-else class="text-center">
                    <div class="flex justify-center items-center mb-2">
                        <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" alt="No results found" />
                    </div>
                    <p class="text-sm italic text-gray-600">We didn't find any security vulnerabilities in your plugins. This is great!</p>
                </div>
            </template>

            <!-- ==================== By Theme Scope ==================== -->
            <template v-else-if="props.scope === 'by_theme'">
                <div v-if="sortedEntities.length">
                    <!-- Pie Chart -->
                    <div class="mb-4">
                        <Pie
                            :options="securityChartOptions"
                            :data="entityChartData"
                        />
                    </div>
                    <!-- Table -->
                    <div>
                        <table class="min-w-full divide-y divide-gray-400">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">Theme</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Total Issues</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-400">
                                <tr v-for="theme in sortedEntities" :key="theme.name">
                                    <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ theme.name }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-500">{{ theme.total.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                            Displaying top 20 themes.
                        </div>
                    </div>
                </div>
                <div v-else class="text-center">
                    <div class="flex justify-center items-center mb-2">
                        <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" alt="No results found" />
                    </div>
                    <p class="text-sm italic text-gray-600">We didn't find any security vulnerabilities in your themes. This is great!</p>
                </div>
            </template>

            <!-- ==================== Fallback Template ==================== -->
            <template v-else>
                <div class="py-2 text-center">
                    <div class="flex justify-center items-center mb-2">
                        <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" alt="No results found" />
                    </div>
                    <p class="text-sm italic text-gray-600">We didn't find any security vulnerabilities in your code. This is great!</p>
                </div>
            </template>
        </div>

        <!-- ==================== Changes Over Time Button ==================== -->
        <div 
            class="rounded-b-lg bg-gray-100 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" 
            v-if="proxy.$wpData.codeVaultVersion != '1.0.0' && props.scope === 'overall'"
        >
            <ChangesOverTimeButton context="security" />
        </div>
    </div>
</template>

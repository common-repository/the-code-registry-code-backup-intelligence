<script setup>
import { defineProps, watchEffect, ref, computed } from 'vue';
import { 
    LockClosedIcon, 
    PuzzlePieceIcon, 
    PaintBrushIcon 
} from '@heroicons/vue/20/solid';

import DataSlideout from '../ReportDataSlideout.vue';
import InsightSlideout from '../ReportInsightSlideout.vue';

// Chart.js imports
import { 
    Chart as ChartJS, 
    CategoryScale, 
    LinearScale, 
    RadialLinearScale, 
    ArcElement, 
    BarElement, 
    Tooltip, 
    Legend 
} from 'chart.js';
import { Bar } from 'vue-chartjs';
ChartJS.register(
    CategoryScale, 
    LinearScale, 
    RadialLinearScale, 
    ArcElement, 
    BarElement, 
    Tooltip, 
    Legend
);

const props = defineProps({
    data: Object,
    type: String, // 'overall', 'by_plugin', or 'by_theme'
    ai_insights: Array
});

const showGraph = ref(false);
const showTable = ref(false);
const showInsight = ref(false);

// ==================== Utility Functions ====================

// Function to generate colors
function generateColor(index) {
    const hue = index * 137.508; // Golden angle approximation for even distribution
    return `hsl(${hue % 360}, 50%, 60%)`; // HSL: Hue, Saturation, Lightness
}

// Function to determine severity color classes
const severityColorClass = (severity) => {
    let classes = 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset';
    switch (severity) {
        case 'INFO':
            return `${classes} text-green-700 bg-green-50 ring-green-600/20`;
        case 'WARNING':
            return `${classes} text-orange-700 bg-orange-50 ring-orange-600/20`;
        case 'ERROR':
            return `${classes} text-red-700 bg-red-50 ring-red-600/20`;
        default:
            return `${classes} text-gray-700 bg-gray-50 ring-gray-600/20`;
    }
};

// Function to determine border color classes
const borderColorClass = (severity) => {
    switch (severity) {
        case 'INFO':
            return 'border border-t-4 border-green-200';
        case 'WARNING':
            return 'border border-t-4 border-orange-200';
        case 'ERROR':
            return 'border border-t-4 border-red-200';
        default:
            return 'border border-t-4 border-gray-200';
    }
};

// ==================== Computations for "overall" ====================

// Initialize counts
let overallCount = 0;
let otherCount = 0;

// Calculate the overall count
watchEffect(() => {
    if (props.type !== 'overall') return;

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

// Compute chart data for overall security issues
const overallChartData = computed(() => {
    if (props.type !== 'overall') return {};

    const labels = ['INFO', 'WARNING', 'ERROR'];
    const backgroundColors = labels.map((_, index) => generateColor(index));
    return {
        labels: labels,
        fontColor: '#fff',
        datasets: [
            {
                label: 'Count',
                fontColor: '#fff',
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
    const entitySeverity = {};

    // Initialize severity counts for each entity
    Object.keys(data).forEach(path => {
        const regex = new RegExp(`^(?:wp-content|web/app)/${entityType}/([^/]+)/$`);
        const match = path.match(regex);
        if (match) {
            const entityName = match[1];
            const entityInfo = data[path];

            // Initialize entity entry if not present
            if (!entityCounts[entityName]) {
                entityCounts[entityName] = { ERROR: 0, WARNING: 0, INFO: 0 };
            }

            // Sum the severities
            const severity = entityInfo.severity || {};
            ['ERROR', 'WARNING', 'INFO'].forEach(level => {
                entityCounts[entityName][level] += severity[level] || 0;
            });
        }
    });

    // Aggregate total severities across all entities
    const totalSeverity = { ERROR: 0, WARNING: 0, INFO: 0, OTHER: 0 };
    Object.values(entityCounts).forEach(sev => {
        totalSeverity.ERROR += sev.ERROR;
        totalSeverity.WARNING += sev.WARNING;
        totalSeverity.INFO += sev.INFO;
    });

    return { entityCounts, entitySeverity: totalSeverity };
};

// Compute plugin counts and severities
const pluginData = computed(() => {
    if (props.type !== 'by_plugin') return { entityCounts: {}, entitySeverity: { ERROR: 0, WARNING: 0, INFO: 0, OTHER: 0 } };
    return computeEntityData('plugins');
});

// Compute theme counts and severities
const themeData = computed(() => {
    if (props.type !== 'by_theme') return { entityCounts: {}, entitySeverity: { ERROR: 0, WARNING: 0, INFO: 0, OTHER: 0 } };
    return computeEntityData('themes');
});

// Sorted entities by total issues descending, limited to top 20
const sortedEntities = computed(() => {
    if (props.type !== 'by_plugin' && props.type !== 'by_theme') return [];

    const data = props.type === 'by_plugin' ? pluginData.value.entityCounts : themeData.value.entityCounts;

    return Object.entries(data)
        .map(([name, counts]) => ({
            name,
            urgent: counts.ERROR,
            medium: counts.WARNING,
            info: counts.INFO
        }))
        .sort((a, b) => (b.urgent + b.medium + b.info) - (a.urgent + a.medium + a.info))
        .slice(0, 20); // Limit to top 20
});

// Compute chart data for entities (plugins or themes)
// Compute chart data for entities (plugins or themes)
const entityChartData = computed(() => {
    if (props.type !== 'by_plugin' && props.type !== 'by_theme') return {};

    const labels = sortedEntities.value.map(entity => entity.name);
    const urgentData = sortedEntities.value.map(entity => entity.urgent);
    const mediumData = sortedEntities.value.map(entity => entity.medium);
    const infoData = sortedEntities.value.map(entity => entity.info);

    return {
        labels: labels,
        datasets: [
            {
                label: 'Urgent',
                backgroundColor: 'hsl(0, 50%, 60%)',
                data: urgentData
            },
            {
                label: 'Medium',
                backgroundColor: 'hsl(30, 50%, 60%)',
                data: mediumData
            },
            {
                label: 'Info',
                backgroundColor: 'hsl(120, 50%, 60%)',
                data: infoData
            }
        ]
    };
});

// Compute total severities for entities
const entitySeverityCount = computed(() => {
    if (props.type !== 'by_plugin' && props.type !== 'by_theme') return { ERROR: 0, WARNING: 0, INFO: 0, OTHER: 0 };

    return props.type === 'by_plugin' ? pluginData.value.entitySeverity : themeData.value.entitySeverity;
});

// ==================== Common Computations ====================

// Severity order for sorting
const severityOrder = {
    'ERROR': 1,
    'WARNING': 2,
    'INFO': 3,
    'OTHER': 4
};

// Toggle functions remain the same, reused for all types
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

// ==================== Sorted Items ====================

// Reuse sortedIssues for 'overall' and sortedEntities for 'by_plugin' and 'by_theme'
const sortedItems = computed(() => {
    if (props.type === 'overall') {
        const combined = [...props.data.code, ...props.data.dependency];
        return combined
            .sort((a, b) => severityOrder[a.severity] - severityOrder[b.severity])
            .slice(0, 10);
    } else if (props.type === 'by_plugin' || props.type === 'by_theme') {
        return sortedEntities.value;
    }
    return [];
});
</script>

<template>
    <!-- ==================== Overall Type Template ==================== -->
    <template v-if="props.type === 'overall'">
        <LockClosedIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" 
            aria-hidden="true" 
            v-if="data.severity_count.ERROR > 0" 
        />
        <LockClosedIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" 
            aria-hidden="true" 
            v-else-if="data.severity_count.WARNING > 0" 
        />
        <LockClosedIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" 
            aria-hidden="true" 
            v-else 
        />
        <div class="text-sm sm:pt-1">
            <p v-if="data.severity_count.ERROR > 0">
                <span class="font-semibold text-red-600 text-base">
                    Overall across all of your WordPress site's code - including all plugins, themes, custom and core WordPress code - we've detected 
                    {{ Number(data.severity_count.ERROR).toLocaleString() }} urgent security 
                    <span v-if="data.severity_count.ERROR > 1">issues</span>
                    <span v-else>issue</span> within your project. These should be reviewed ASAP.
                </span>
                &nbsp;
                <span class="text-orange-600" v-if="data.severity_count.WARNING > 0">
                    We've also detected 
                    {{ Number(data.severity_count.WARNING).toLocaleString() }} medium severity issues.
                </span>
                <span class="text-green-600" v-else>
                    There are however no medium severity issues across all code vaults.
                </span>
                &nbsp;
                <span v-if="data.severity_count.INFO > 0">
                    There 
                    <span v-if="data.severity_count.INFO > 1">are</span>
                    <span v-else>is</span> 
                    {{ Number(data.severity_count.INFO).toLocaleString() }} low severity 
                    <span v-if="data.severity_count.INFO > 1">issues</span>
                    <span v-else>issue</span>, but these are often informational or advisory notes. They are still worth reviewing though!
                </span>
            </p>
            <p v-else-if="data.severity_count.WARNING > 0">
                <span class="font-semibold text-green-600 text-base">
                    Overall across all of your WordPress site's code - including all plugins, themes, custom and core WordPress code - we've found no urgent security issues across your entire project. This is great!
                </span>&nbsp;
                <span class="font-semibold text-orange-600">
                    There are however 
                    {{ Number(data.severity_count.WARNING).toLocaleString() }} medium severity 
                    <span v-if="data.severity_count.WARNING > 1">issues</span>
                    <span v-else>issue</span> across this project's code vaults.
                </span>
                &nbsp;
                <span v-if="data.severity_count.INFO > 0">
                    There 
                    <span v-if="data.severity_count.INFO > 1">are</span>
                    <span v-else>is</span> 
                    {{ Number(data.severity_count.INFO).toLocaleString() }} low severity 
                    <span v-if="data.severity_count.INFO > 1">issues</span>
                    <span v-else>issue</span>, but these are often informational or advisory notes. They are still worth reviewing though!
                </span>
            </p>
            <p v-else>
                <span class="font-semibold text-green-600 text-base">
                    Overall across all of your WordPress site's code - including all plugins, themes, custom and core WordPress code - we've found no urgent or medium severity security issues across your entire project. This is great!
                </span>
                &nbsp;
                <span v-if="data.severity_count.INFO > 0">
                    There 
                    <span v-if="data.severity_count.INFO > 1">are</span>
                    <span v-else>is</span> 
                    {{ Number(data.severity_count.INFO).toLocaleString() }} low severity 
                    <span v-if="data.severity_count.INFO > 1">issues</span>
                    <span v-else>issue</span>, but these are often informational or advisory notes. They are still worth reviewing though!
                </span>
            </p>
            <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap" id="tour-widget-security-actions">
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowGraph"
                >
                    <span v-if="!showGraph">Show me a chart</span>
                    <span v-else>Hide the chart</span>
                </button>
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowTable"
                >
                    <span v-if="!showTable">Show me the most urgent issues</span>
                    <span v-else>Hide the most urgent issues</span>
                </button>
                <button 
                    type="button" 
                    class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowInsight"
                >
                    <span v-if="!showInsight">What does Ada say?</span>
                    <span v-else>Hide Ada's insight</span>
                </button>
            </div>
            <DataSlideout title="Number of issues by severity" v-if="showGraph">
                <div class="text-white">
                    <div class="w-[250px] sm:w-full">
                        <Bar
                            id="vault-facet-security-chart"
                            :options="securityChartOptions"
                            :data="overallChartData"
                        />
                    </div>
                    <div class="flex items-center">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">Total rules checked</td>
                                    <td class="px-3 py-2 text-xs">4,051</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">Urgent issues found</td>
                                    <td class="px-3 py-2 text-xs">{{ data.severity_count.ERROR.toLocaleString() }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">Other issues found</td>
                                    <td class="px-3 py-2 text-xs">{{ otherCount.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </DataSlideout>
            <DataSlideout title="10 most urgent issues" v-if="showTable">
                <div class="space-y-4">
                    <div v-for="issue in sortedItems" :key="issue.check_id">
                        <div :class="['relative bg-gray-800 text-white p-3 rounded-lg shadow-md', borderColorClass(issue.severity)]">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold truncate text-white text-xs max-w-[190px] sm:max-w-[220px] md:text-sm sm:max-w-[370px]">
                                    {{ issue.path }}
                                </h3>
                                <span 
                                    class="absolute top-1 right-1 md:relative md:top-auto md:right-auto" 
                                    :class="severityColorClass(issue.severity)"
                                >
                                    {{ issue.severity }}
                                </span>
                            </div>
                            <h4 class="text-xs text-white">
                                Line {{ issue.line }}
                            </h4>
                            <div class="mt-2 text-xs text-gray-600">
                                <p class="mb-2 text-white">{{ issue.message }}</p>
                                <div class="font-mono border border-black bg-slate-200 p-2 whitespace-pre-wrap break-all">
                                    {{ issue.code_snippet }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </DataSlideout>
            <InsightSlideout 
                title="Ada's insights on security issues" 
                :ai_insights="ai_insights" 
                v-if="showInsight" 
            />
            <p class="mt-5">
                Below we list issues we've detected in each plugin and theme. With WordPress plugins can be bought, installed for free and also developed by your developers (or yourself!). The same for themes. We've added the information below to help you see exactly where potentially issues have been found. There is much more information in our web app!
            </p>
        </div>
    </template>

    <!-- ==================== By Plugin Type Template ==================== -->
    <template v-else-if="props.type === 'by_plugin'">
        <PuzzlePieceIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" 
            aria-hidden="true" 
            v-if="entitySeverityCount.ERROR > 0" 
        />
        <PuzzlePieceIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" 
            aria-hidden="true" 
            v-else-if="entitySeverityCount.WARNING > 0" 
        />
        <PuzzlePieceIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" 
            aria-hidden="true" 
            v-else 
        />
        <div class="text-sm sm:pt-1">
            <p>
                <span class="font-semibold text-red-600 text-base">
                    We've found 
                    {{ Number(entitySeverityCount.ERROR).toLocaleString() }} urgent issues within your WordPress site's plugins.
                </span>
                &nbsp;
                <span class="text-orange-600">
                    Additionally, there are 
                    {{ Number(entitySeverityCount.WARNING).toLocaleString() }} medium severity issues.
                </span>
                &nbsp;
                <span v-if="entitySeverityCount.INFO > 0">
                    There 
                    <span v-if="entitySeverityCount.INFO > 1">are</span>
                    <span v-else>is</span> 
                    {{ Number(entitySeverityCount.INFO).toLocaleString() }} low severity 
                    <span v-if="entitySeverityCount.INFO > 1">issues</span>
                    <span v-else>issue</span>, but these are often informational or advisory notes.
                </span>
            </p>
            <p class="mt-2">There are thousands of plugins available for WordPress, covering all sorts of amazing and unique functionality. If any of these plugins where issues have been found have been built for you or paid for, you should review these with your development partner.</p>
            <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap" id="tour-widget-security-actions">
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowTable"
                >
                    <span v-if="!showTable">Show me issues in each plugin</span>
                    <span v-else>Hide issues in each plugin</span>
                </button>
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowGraph"
                >
                    <span v-if="!showGraph">Show me a chart</span>
                    <span v-else>Hide the chart</span>
                </button>
            </div>
            <DataSlideout title="Issues by plugin" v-if="showTable">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-white">
                        <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Plugin</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Urgent</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Medium</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-400">
                            <tr v-for="plugin in sortedEntities" :key="plugin.name">
                                <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ plugin.name }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ plugin.urgent.toLocaleString() }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ plugin.medium.toLocaleString() }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ plugin.info.toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                        Displaying top 20 {{ props.type === 'by_plugin' ? 'plugins' : 'themes' }}.
                    </div>
                </div>
            </DataSlideout>
            <DataSlideout title="Issues by plugin in a chart" v-if="showGraph">
                <div class="text-white">
                    <div class="w-full">
                        <Bar
                            id="plugin-security-chart"
                            :options="securityChartOptions"
                            :data="entityChartData"
                        />
                    </div>
                    <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                        Displaying top 20 {{ props.type === 'by_plugin' ? 'plugins' : 'themes' }}.
                    </div>
                </div>
            </DataSlideout>
        </div>
    </template>

    <!-- ==================== By Theme Type Template ==================== -->
    <template v-else-if="props.type === 'by_theme'">
        <PaintBrushIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-red-600" 
            aria-hidden="true" 
            v-if="entitySeverityCount.ERROR > 0" 
        />
        <PaintBrushIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-orange-600" 
            aria-hidden="true" 
            v-else-if="entitySeverityCount.WARNING > 0" 
        />
        <PaintBrushIcon 
            class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-green-600" 
            aria-hidden="true" 
            v-else 
        />
        <div class="text-sm sm:pt-1">
            <p>
                <span class="font-semibold text-red-600 text-base">
                    We've found 
                    {{ Number(entitySeverityCount.ERROR).toLocaleString() }} urgent issues within your WordPress site's themes.
                </span>
                &nbsp;
                <span class="text-orange-600">
                    Additionally, there are 
                    {{ Number(entitySeverityCount.WARNING).toLocaleString() }} medium severity issues.
                </span>
                &nbsp;
                <span v-if="entitySeverityCount.INFO > 0">
                    There 
                    <span v-if="entitySeverityCount.INFO > 1">are</span>
                    <span v-else>is</span> 
                    {{ Number(entitySeverityCount.INFO).toLocaleString() }} low severity 
                    <span v-if="entitySeverityCount.INFO > 1">issues</span>
                    <span v-else>issue</span>, but these are often informational or advisory notes.
                </span>
            </p>
            <p class="mt-2">Your list of themes will include your current active theme and any other themes you've installed. Some themes come pre-installed with WordPress. If there are issues found in a theme you've built yourself or had built for you, you can review these with your development partner..</p>
            <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap" id="tour-widget-security-actions">
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowTable"
                >
                    <span v-if="!showTable">Show me issues in each theme</span>
                    <span v-else>Hide issues in each theme</span>
                </button>
                <button 
                    type="button" 
                    class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" 
                    @click="toggleShowGraph"
                >
                    <span v-if="!showGraph">Show me a chart</span>
                    <span v-else>Hide the chart</span>
                </button>
            </div>
            <DataSlideout title="Issues by theme" v-if="showTable">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-white">
                        <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Theme</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Urgent</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Medium</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-400">
                            <tr v-for="theme in sortedEntities" :key="theme.name">
                                <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ theme.name }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ theme.urgent.toLocaleString() }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ theme.medium.toLocaleString() }}</td>
                                <td class="px-3 py-2 text-xs text-gray-200">{{ theme.info.toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                        Displaying top 20 {{ props.type === 'by_plugin' ? 'plugins' : 'themes' }}.
                    </div>
                </div>
            </DataSlideout>
            <DataSlideout title="Issues by theme in a chart" v-if="showGraph">
                <div class="text-white">
                    <div class="w-full">
                        <Bar
                            id="theme-security-chart"
                            :options="securityChartOptions"
                            :data="entityChartData"
                        />
                    </div>
                    <div v-if="sortedEntities.length === 20" class="mt-2 text-xs text-gray-600">
                        Displaying top 20 {{ props.type === 'by_plugin' ? 'plugins' : 'themes' }}.
                    </div>
                </div>
            </DataSlideout>
        </div>
    </template>
</template>

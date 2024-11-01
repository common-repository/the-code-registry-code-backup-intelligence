<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const { proxy } = getCurrentInstance()

// chart objects
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'vue-chartjs';
ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});

function isLikelyOpenSource(licenseType) {
    const openSourceIdentifiers = [
        'MIT', 'Apache', 'GPL', 'AGPL', 'LGPL', 'BSD', 'Eclipse', 'Mozilla', 'CDDL', 'Public Domain', 
        'Creative Commons', 'OSL', 'ISC', 'X11', 'Zlib', 'AFL', 'BSL', 'CC', 'ODbL', 'Python', 'EPL', 
        'FSFAP', 'ICU', 'NAIST', 'MPL', 'Unlicense', 'WTFPL', 'ZPL', 'OFL'
    ];

    return openSourceIdentifiers.some(keyword => {
        const regex = new RegExp(keyword, 'i'); // Case insensitive matching
        return regex.test(licenseType);
    });
}

const licenseInfo = computed(() => {
    const components = props.data.components ?? {};
    let totalLicenses = 0;
    let outdatedLicenses = 0; // Counter for licenses in outdated components
    const licenseTypeDetails = {};

    Object.values(components).forEach(component => {
        const isOutdated = component.current_version !== component.latest_version;
        Object.entries(component.licenses).forEach(([type, { url, checklist_url }]) => {
            const isCommercial = !isLikelyOpenSource(type);
            const category = isCommercial ? 'Commercial' : 'Open Source';

            if (!licenseTypeDetails[type]) {
                licenseTypeDetails[type] = { type, count: 0, url, checklist_url, category };
            }
            licenseTypeDetails[type].count += 1;
            totalLicenses += 1;

            // Count the license if the component is outdated
            if (isOutdated) {
                outdatedLicenses += 1;
            }
        });
    });

    const commercialCount = Object.values(licenseTypeDetails).filter(detail => detail.category === 'Commercial').reduce((acc, curr) => acc + curr.count, 0);
    const licenseDetails = Object.values(licenseTypeDetails); // Convert object to array for display

    // Prepare chart data
    const chartData = {
        labels: ['Commercial', 'Open Source'],
        datasets: [{
            label: 'Count',
            backgroundColor: [generateColor(0), generateColor(1)],
            data: [commercialCount, totalLicenses - commercialCount]
        }]
    };

    return {
        totalLicenses,
        commercialCount,
        outdatedLicenses,
        licenseDetails,
        chartData,
        chartOptions: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    };
});

function generateColor(index) {
    const hue = (240 + index * 137.508) % 360; // Adjust the base hue to blue
    return `hsl(${hue}, 70%, 60%)`; // HSL: Hue, Saturation, Lightness
}
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Third Party Licenses (%)
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('components')">
                    More info
                </a>
                <Popper arrow placement="right" content="These are the licenses we've detected in your third party packages and dependencies">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center">
                <Bar
                    id="vault-facet-licenses-chart"
                    :options="licenseInfo.chartOptions"
                    :data="licenseInfo.chartData"
                />
            </div>
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Open source licenses</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ (licenseInfo.totalLicenses-licenseInfo.commercialCount).toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Commercial licenses</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ licenseInfo.commercialCount.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Total licenses detected</th>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ licenseInfo.totalLicenses.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-sm font-semibold text-red-600 sm:pl-0">Licenses in outdated components</th>
                            <td class="px-3 py-2 text-sm text-red-600">{{ licenseInfo.outdatedLicenses.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
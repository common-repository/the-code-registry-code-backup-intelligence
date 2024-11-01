<script setup>
import { defineProps, ref, computed, getCurrentInstance } from 'vue';
import { ClipboardDocumentCheckIcon } from '@heroicons/vue/20/solid';
import DataSlideout from '../ReportDataSlideout.vue';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({ data: Object });
const { proxy } = getCurrentInstance()

const showGraph = ref(false);
const showCommercialTable = ref(false);
const showOpenSourceTable = ref(false);

function isLikelyOpenSource(licenseType) {
    const openSourceRegex = /MIT|Apache|GPL|AGPL|LGPL|BSD|Eclipse|Mozilla|CDDL|Public Domain|Creative Commons|CC-BY|OSL|ISC|X11|Zlib|AFL|BSL|CC0|OFL|ZPL|ODbL|Python|EPL|FSFAP|ICU|NAIST|MPL|Unlicense|WTFPL/i;
    return openSourceRegex.test(licenseType);
}

const licenseInfo = computed(() => {
    const components = props.data.components ?? {};
    let totalLicenses = 0;
    const licenseTypeDetails = {};

    // Collecting and categorizing licenses
    Object.values(components).forEach(component => {
        Object.entries(component.licenses).forEach(([type, { url, checklist_url }]) => {
            const isCommercial = !isLikelyOpenSource(type);
            const category = isCommercial ? 'Commercial' : 'Open Source';

            if (!licenseTypeDetails[type]) {
                licenseTypeDetails[type] = { type, count: 0, url, checklist_url, category };
            }
            licenseTypeDetails[type].count += 1;
            totalLicenses += 1;
        });
    });

    // Preparing sorted lists of commercial and open-source licenses
    const commercialLicenses = Object.values(licenseTypeDetails)
                                      .filter(detail => detail.category === 'Commercial')
                                      .sort((a, b) => b.count - a.count);
    const openSourceLicenses = Object.values(licenseTypeDetails)
                                      .filter(detail => detail.category === 'Open Source')
                                      .sort((a, b) => b.count - a.count);

    // Calculate total count for commercial licenses
    const commercialCount = commercialLicenses.reduce((acc, curr) => acc + curr.count, 0);

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
        commercialLicenses,
        openSourceLicenses,
        chartData,
        chartOptions: {
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
        }
    };
});

function generateColor(index) {
    const hue = (240 + index * 137.508) % 360;
    return `hsl(${hue}, 70%, 60%)`;
}

function toggleShowGraph() {
    showOpenSourceTable.value = false;
    showCommercialTable.value = false;
    showGraph.value = !showGraph.value;
}

function toggleShowTable(tableType) {
    showGraph.value = false;
    if (tableType === 'commercial') {
        showCommercialTable.value = !showCommercialTable.value;
        showOpenSourceTable.value = false;
    } else {
        showOpenSourceTable.value = !showOpenSourceTable.value;
        showCommercialTable.value = false;
    }
}
</script>


<template>
    <ClipboardDocumentCheckIcon class="mt-1 h-4 w-4 sm:h-6 sm:w-6 flex-none text-black" aria-hidden="true" />
    <div class="text-sm sm:pt-1">
        <p v-if="licenseInfo.commercialCount">
            <span class="font-semibold text-black text-base">We have detected a total of {{ licenseInfo.totalLicenses.toLocaleString() }} licenses in your third-party packages and dependencies. {{ licenseInfo.commercialCount.toLocaleString() }} of these are potentially commercial licenses and should be reviewed to make sure you are compliant.</span> You can find more information using the buttons below and on our dedicated "Open Source Components" page.
        </p>
        <p v-else>
            <span class="font-semibold text-black text-base">We have detected a total of {{ licenseInfo.totalLicenses.toLocaleString() }} licenses in your third-party packages and dependencies.</span> We believe none of these are commercial licenses but it's still worth reviewing them to make sure you are compliant. You can find more information using the buttons below and on our dedicated "Open Source Components" page.
        </p>
        <div class="flex gap-x-2 mt-2 flex-wrap gap-y-2 md:flex-nowrap">
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowGraph">
                <span v-if="!showGraph">Show me a chart</span>
                <span v-else>Hide the chart</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowTable('commercial')">
                <span v-if="!showCommercialTable">Show commercial licenses</span>
                <span v-else>Hide commercial licenses</span>
            </button>
            <button type="button" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click="toggleShowTable('openSource')">
                <span v-if="!showOpenSourceTable">Show open source licenses</span>
                <span v-else>Hide open source licenses</span>
            </button>
        </div>
        <DataSlideout title="Number of licenses by type" v-if="showGraph">
            <div class="text-white">
                <div class="w-[250px] sm:w-full">
                    <Bar
                        id="vault-facet-licenses-chart"
                        :options="licenseInfo.chartOptions"
                        :data="licenseInfo.chartData"
                    />
                </div>
            </div>
        </DataSlideout>
        <DataSlideout title="Commercial licenses" v-if="showCommercialTable">
            <table class="min-w-full divide-y divide-gray-400 text-white" v-if="licenseInfo && licenseInfo.commercialLicenses.length">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Count</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">More info</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="detail in licenseInfo.commercialLicenses" :key="detail.type">
                        <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ detail.type }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ detail.count.toLocaleString() }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">
                            <a :href="detail.url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-200">
                            <a :href="detail.checklist_url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="py-2 text-center" v-else>
                <div class="flex justify-center items-center mb-2">
                    <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" />
                </div>
                <p class="text-xs italic">We didn't detect any commercial licenses in your third party packages.</p>
            </div>
        </DataSlideout>
        <DataSlideout title="Open source licenses" v-if="showOpenSourceTable">
            <table class="min-w-full divide-y divide-gray-400 text-white" v-if="licenseInfo && licenseInfo.openSourceLicenses.length">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold sm:pl-0">Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Count</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">More info</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold">Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-400">
                    <tr v-for="detail in licenseInfo.openSourceLicenses" :key="detail.type">
                        <td class="py-2 pl-4 pr-3 text-xs font-medium sm:pl-0">{{ detail.type }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">{{ detail.count.toLocaleString() }}</td>
                        <td class="px-3 py-2 text-xs text-gray-200">
                            <a :href="detail.url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-200">
                            <a :href="detail.checklist_url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="py-2 text-center" v-else>
                <div class="flex justify-center items-center mb-2">
                    <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" />
                </div>
                <p class="text-xs italic">We didn't detect any open source licenses in your third party packages.</p>
            </div>
        </DataSlideout>
    </div>
</template>
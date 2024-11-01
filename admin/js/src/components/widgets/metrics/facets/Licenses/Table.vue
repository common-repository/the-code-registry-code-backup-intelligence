<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const { proxy } = getCurrentInstance()

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});

function isLikelyOpenSource(licenseType) {
    const openSourceRegex = /MIT|Apache|GPL|AGPL|LGPL|BSD|Eclipse|Mozilla|CDDL|Public Domain|Creative Commons|CC-BY|OSL|ISC|X11|Zlib|AFL|BSL|CC0|OFL|ZPL|ODbL|Python|EPL|FSFAP|ICU|NAIST|MPL|Unlicense|WTFPL/i;
    return openSourceRegex.test(licenseType);
}

const licenseInfo = computed(() => {
    const components = props.data.components ?? {};
    const licenseTypeDetails = {};

    Object.values(components).forEach(component => {
        Object.entries(component.licenses).forEach(([type, { url, checklist_url }]) => {
            const isCommercial = !isLikelyOpenSource(type);
            const category = isCommercial ? 'Commercial' : 'Open Source';

            if (!licenseTypeDetails[type]) {
                licenseTypeDetails[type] = { type, count: 0, url, checklist_url, category };
            }
            licenseTypeDetails[type].count += 1;
        });
    });

    const licenseDetails = Object.values(licenseTypeDetails)
        .sort((a, b) => b.count - a.count)  // Sort by count in descending order
        .slice(0, 20);  // Take only the top 20

    return {
        licenseDetails
    };
});
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Third Party Licenses (#)
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('components')">
                    More info
                </a>
                <Popper arrow placement="right" content="These are the 20 most common licenses we've detected in your third party packages and dependencies">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">License type</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Count</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">More info</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Checklist</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-400">
                        <tr v-for="detail in licenseInfo.licenseDetails" :key="detail.type">
                            <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ detail.type }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ detail.count.toLocaleString() }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                <a :href="detail.url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                <a :href="detail.checklist_url" target="_blank" class="underline hover:text-brand-purple">Link</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
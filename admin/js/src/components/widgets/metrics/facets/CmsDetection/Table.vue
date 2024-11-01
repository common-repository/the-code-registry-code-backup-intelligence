<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});

const { proxy } = getCurrentInstance()

const sortedComponents = computed(() => {
    return Object.entries(props.data.components)
        .map(([name, component]) => ({ name, ...component }))
        .sort((a, b) => b.total_lines_of_code - a.total_lines_of_code)
        .slice(0, 10);
});
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Open Source Components
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('components')">
                    More info
                </a>
                <Popper arrow placement="right" content="These are the 10 largest open source components we detected in your code, how many lines of code they are in total, and what their versions are. A lot more information can be found by clicking 'More info'.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center" v-if="sortedComponents && sortedComponents.length">
                <table class="min-w-full divide-y divide-gray-400">
                    <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">Component</th>
                            <!-- <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Total Files</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Latest Version</th> -->
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Current Version</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Total Lines of Code</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-400">
                        <tr v-for="component in sortedComponents" :key="name">
                            <td class=" py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ component.name }}</td>
                            <!-- <td class=" px-3 py-2 text-xs text-gray-500">{{ component.total_files.toLocaleString() }}</td>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ component.current_version }}</td> -->
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ component.latest_version }}</td>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ component.total_lines_of_code.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="py-2 text-center" v-else>
                <div class="flex justify-center items-center mb-2">
                    <img :src="`${proxy.$wpData.pluginUrl}admin/img/no-results.gif`" class="mb-2" loading="lazy" />
                </div>
                <p class="text-xs italic">We didn't detect any Open Source Software components in your code. This isn't a bad thing! Click "More info" for further details.</p>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="cms-detection" />
        </div>
    </div>
</template>
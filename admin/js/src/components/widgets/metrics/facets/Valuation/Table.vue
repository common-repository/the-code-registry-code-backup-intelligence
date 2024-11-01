<script setup>
import { defineProps, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

const { proxy } = getCurrentInstance()

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                "Cost to Replicate" Estimate
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('valuation')">
                    More info
                </a>
                <Popper arrow placement="right" content="This is our code replication valuation for your IP based on our proprietary algorythm.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5">
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Total lines of code</th>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ props.data.factors.total_lines_of_code.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Lines of code discounted</th>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ props.data.factors.total_lines_discounted.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Languages multiple</th>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ props.data.factors.language_multiple }}</td>
                        </tr>
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-xs font-semibold text-gray-900 sm:pl-0">Complexity multiple</th>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ props.data.factors.complexity_multiple }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center">
                <table class="min-w-full divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-md font-bold text-gray-900 sm:pl-0 text-center">Total "Cost to Replicate" Value</td>
                            <td class="px-3 py-2 text-md font-bold text-blue-500">{{ props.data.total_range }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="valuation" />
        </div>
    </div>
</template>
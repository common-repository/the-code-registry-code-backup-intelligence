<script setup>
import { defineProps, computed, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

const { proxy } = getCurrentInstance()

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  data: Object
});

// we want to remove any language from props.data.by_language starting with _ and sort sourceCount descending
const languages = computed(() => {
  if (props.data && Array.isArray(props.data.by_language)) {
    return props.data.by_language
      .filter(language => !language.language.startsWith('_'))
      .sort((a, b) => b.sourceCount - a.sourceCount)
      .slice(0, 10);
  }
  return [];
});
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Most Common Languages (#)
            </div>
            <div>
                <Popper arrow placement="right" content="These are the 10 most used programming languages we detected in your codebase.">
                    <button type="button" class="font-medium text-white hover:text-blue-400">
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
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">Language</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900"># files</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900"># lines of code</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-400">
                        <tr v-for="language in languages" :key="language.language">
                            <td class=" py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ language.language }}</td>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ language.fileCount.toLocaleString() }}</td>
                            <td class=" px-3 py-2 text-xs text-gray-500">{{ language.sourceCount.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="languages" />
        </div>
    </div>
</template>
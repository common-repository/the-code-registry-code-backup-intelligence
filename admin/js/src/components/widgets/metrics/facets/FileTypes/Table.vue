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

// we only want 20 file types and we want file_size sorted descending
const file_types = computed(() => {
  if (props.data && props.data.file_types && typeof props.data.file_types === 'object') {
    return Object.entries(props.data.file_types)
      .map(([type, data]) => ({
        type,
        ...data,
        formattedFileSize: formatFileSize(data.file_size) // Add a formatted file size
      }))
      .sort((a, b) => b.file_size - a.file_size)
      .slice(0, 20);
  }
  return [];
});

function formatFileSize(bytes) {
    const MB = 1024 * 1024;
    const GB = 1024 * MB;

    if (bytes > GB) {
        return (bytes / GB).toFixed(2) + ' GB';
    } else {
        return (bytes / MB).toFixed(2) + ' MB';
    }
}
</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Most Common File Types (#)
            </div>
            <div>
                <Popper arrow placement="right" content="These are 20 of the most used file types in your codebase, ordered by largest file size first.">
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
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-0">File type</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">File size</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">File #</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-400">
                        <tr v-for="(type_data) in file_types" :key="type_data.type">
                            <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">{{ type_data.type }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ type_data.formattedFileSize }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ type_data.file_count.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="file-types" />
        </div>
    </div>
</template>
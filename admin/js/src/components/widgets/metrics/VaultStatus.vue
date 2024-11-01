<script setup>
import { computed, getCurrentInstance } from 'vue';

const props = defineProps({
    value: null,
});

const { proxy } = getCurrentInstance()

const vaultData = computed(() => {
    if (!props.value) {
        return {
            size: '(analyzing...)',
            code_last_synced: '(syncing...)',
            status: '...',
        };
    }

    return {
        size: props.value.size == '(analyzing...)' ? '(analyzing...)' : formatFileSize(props.value.size),
        code_last_synced: props.value.code_last_synced,
        status: props.value.status,
    };
});

function formatFileSize(bytes) {
    if (bytes === '(analyzing...)') return bytes;

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
    <div class="vault-status-widget">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Code Vault Status
            </div>
            <div>
                <Popper arrow placement="right" content="This is the current status of your Code Vault and it's job queue.">
                    <button type="button" class="font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="py-2 px-5 flex flex-row flex-nowrap gap-5">
            <div class="align-middle text-center">
                <img :src="`${proxy.$wpData.pluginUrl}admin/img/ip-vault-status-icon.png`" alt="Vault status" class="m-auto w-[100px]" />
            </div>
            <div class="">
                <table class="w-full border-collapse divide-y divide-gray-400">
                    <tbody class="divide-y divide-gray-400">
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">Vault size</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ vaultData.size }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">Code synced</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ vaultData.code_last_synced }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4 pr-3 text-xs font-medium text-gray-900 sm:pl-0">Status</td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-x-1.5 rounded-full bg-white px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                    <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true" v-if="vaultData.status == 'Secured'">
                                        <circle cx="3" cy="3" r="3" />
                                    </svg>
                                    <svg class="h-1.5 w-1.5 fill-orange-500 animate-pulse" viewBox="0 0 6 6" aria-hidden="true" v-else>
                                        <circle cx="3" cy="3" r="3" />
                                    </svg>
                                    {{ vaultData.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
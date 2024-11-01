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

const emit = defineEmits(['changeView', 'previewWebAppDialog'])
const changeView = (view) => {
  emit('changeView', view)
}
const previewWebAppDialog = (context) => {
  emit('previewWebAppDialog', context)
}
</script>

<template>
    <div class="vault-status-widget">
        <p class="mt-6 leading-tight text-black text-base md:text-md">
            You're viewing our summary report for your WordPress site "{{ proxy.$wpData.siteName }}". You can also view the <a href="#" class="font-semibold text-brand-purple hover:text-brand-blue" @click.stop="changeView('ShowProjectMetrics')">full metrics dashboard</a>, but for much more detail (including our live chat AI code assistant Ada) you'll need to <a href="#" class="font-semibold text-brand-purple hover:text-brand-blue" @click.stop="previewWebAppDialog('main')">view our main web app</a>.
        </p>
        <p class="mt-4 text-xs leading-tight text-black hidden md:block">
            Overall file size: {{ vaultData.size }}. Code last synced: {{ vaultData.code_last_synced }}. 
            <span class="block md:inline"> Current status: <span class="inline-flex items-center gap-x-1.5 rounded-full bg-white px-2 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true" v-if="vaultData.status == 'Secured'">
                    <circle cx="3" cy="3" r="3" />
                </svg>
                <svg class="h-1.5 w-1.5 fill-orange-500 animate-pulse" viewBox="0 0 6 6" aria-hidden="true" v-else>
                    <circle cx="3" cy="3" r="3" />
                </svg>
                {{ vaultData.status }}
            </span></span>
        </p>
    </div>
</template>
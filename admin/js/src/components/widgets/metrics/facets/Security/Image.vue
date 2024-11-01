<script setup>
import { defineProps, watchEffect, getCurrentInstance } from 'vue';
import ChangesOverTimeButton from '@/components/partials/ChangesOverTimeButton.vue';

import {
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: Object,
    previewWebAppDialog: Function
});

const { proxy } = getCurrentInstance()

// calculate the overall count
watchEffect(() => {
    if (!props.data.severity_count.OTHER) {
        props.data.severity_count.OTHER = 0;
    }
    if (!props.data.severity_count.INFO) {
        props.data.severity_count.INFO = 0;
    }
    if (!props.data.severity_count.WARNING) {
        props.data.severity_count.WARNING = 0;
    }
    if (!props.data.severity_count.ERROR) {
        props.data.severity_count.ERROR = 0;
    }
});

</script>

<template>
    <div v-if="props.data">
        <div class="rounded-t-lg bg-brand-blue px-5 py-3 flex flex-wrap items-center justify-between sm:flex-nowrap">
            <div class="text-sm font-medium text-white">
                Security Status
            </div>
            <div class="text-center flex items-center">
                <a href="#" class="rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" @click.stop="previewWebAppDialog('security')">
                    More info
                </a>
                <Popper arrow placement="right" content="Your snapshot security vulnerability status based on our analysis.">
                    <button type="button" class="ml-3 font-medium text-white hover:text-blue-400">
                        <QuestionMarkCircleIcon class="h-6 w-6 -mb-[8px]" aria-hidden="true" />
                    </button>
                </Popper>
            </div>
        </div>
        <div class="p-5" v-if="data.severity_count.ERROR > 0">
            <img :src="`${proxy.$wpData.pluginUrl}admin/img/traffic-lights-red.png`" alt="Security status" class="m-auto w-[100px]" />
            <p class="mt-4 text-xs">We've given your IP code a red traffic light in it's current version. This is because there is at least one urgent issue found in our scans. You can see more details by clicking "More info" and review these with your development team.</p>
        </div>
        <div class="p-5" v-else-if="data.severity_count.WARNING > 0">
            <img :src="`${proxy.$wpData.pluginUrl}admin/img/traffic-lights-orange.png`" alt="Security status" class="m-auto w-[100px]" />
            <p class="mt-4 text-xs">We've given your IP code an orange traffic light in it's current version. This is because there is at least one medium level issue found in our scans. You can see more details by clicking "More info" and review these with your development team.</p>
        </div>
        <div class="p-5" v-else>
            <img :src="`${proxy.$wpData.pluginUrl}admin/img/traffic-lights-green.png`" alt="Security status" class="m-auto w-[100px]" />
            <p class="mt-4 text-xs">We've given your IP code a green traffic light! We've found no urgent or medium level issues in your codebase. Keep it up!</p>
        </div>
        <div class="rounded-b-lg bg-gray-800 px-5 py-3 flex flex-wrap items-center justify-center sm:flex-nowrap" v-if="proxy.$wpData.codeVaultVersion != '1.0.0'">
            <ChangesOverTimeButton context="security" />
        </div>
    </div>
</template>
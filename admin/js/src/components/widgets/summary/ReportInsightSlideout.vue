<script setup>
import AdaAvatar from '@/components/partials/AdaAvatar.vue';

const props = defineProps({
    title: String,
    ai_insights: Array
});

const shouldDisplayInsight = (insight) => {
    return props.title.indexOf('languages') === -1 || (props.title.indexOf('languages') !== -1 && insight.title === 'Programming languages');
}
</script>
<template>
    <div class="mt-2 bg-violet-800 rounded-md">
        <div class="relative text-white bg-violet-950 px-4 py-3 text-xs justify-between rounded-t-md sm:flex sm:items-center">
            <span>{{ title }}</span>
        </div>
        <div class="p-4 text-white relative text-xs">
            <template v-for="insight in ai_insights">
                <div class="cxo-report-single-insight mb-2" v-if="shouldDisplayInsight(insight)">
                    <p class="font-bold mb-2">{{ insight.title }}</p>
                    <div v-html="insight.insight" class="break-all sm:break-normal sm:break-words"></div>
                </div>
            </template>
            <AdaAvatar class="w-[30px] sm:w-[40px]" />
        </div>
    </div>
</template>
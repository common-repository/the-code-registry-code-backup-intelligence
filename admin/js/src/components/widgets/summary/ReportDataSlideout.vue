<script setup>
const props = defineProps({
    title: String
});
</script>
<template>
    <div class="mt-2 bg-black rounded-md">
        <div class="flex items-center relative text-white bg-slate-700 px-4 py-3 text-xs justify-between rounded-t-md">
            <span>{{ title }}</span>
        </div>
        <div class="p-4 text-white">
            <slot></slot>
        </div>
    </div>
</template>
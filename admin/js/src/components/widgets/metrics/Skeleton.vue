<script setup>
import { defineProps, getCurrentInstance } from 'vue';

const props = defineProps({
  message: {
    type: String,
    default: 'Feel free to leave this page and check back in a few minutes...'
  }
});

const { proxy } = getCurrentInstance()
</script>

<template>
  <div role="status" class="py-2 text-center">
    <div class="flex justify-center items-center mb-2">
      <img :src="`${proxy.$wpData.pluginUrl}admin/img/analysing.gif`"  class="mb-2" loading="lazy" />
    </div>
    <p class="text-xs italic">{{ message }}</p>
  </div>
</template>
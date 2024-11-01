<script setup>
import { ref, getCurrentInstance } from 'vue';
const { proxy } = getCurrentInstance()
const avatarCount = 10; // Total number of avatars
const randomIndex = Math.floor(Math.random() * avatarCount) + 1; // +1 because your avatars start at 1
const avatarSrc = ref(`${proxy.$wpData.pluginUrl}admin/img/ada/avatar${randomIndex}.svg`);

// change the avatar every 5 seconds
setInterval(() => {
    const newIndex = Math.floor(Math.random() * avatarCount) + 1;
    avatarSrc.value = `${proxy.$wpData.pluginUrl}admin/img/ada/avatar${newIndex}.svg`;
}, 5000);
</script>

<template>
    <div>
        <img :src="avatarSrc" alt="Ada's avatar" />
    </div>
</template>

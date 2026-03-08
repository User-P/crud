<template>
    <AppLoading
        v-if="isLoading"
        overlay
        full-screen
        :message="message"
    />
</template>

<script setup lang="ts">
import AppLoading from '@/Components/AppLoading.vue'
import { useGlobalLoading } from '@/composables/useGlobalLoading'

const { isLoading, message } = useGlobalLoading()
</script>

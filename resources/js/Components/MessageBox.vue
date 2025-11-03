<script setup lang="ts">
import { Button } from 'primevue';

</script>

<template>
    <div class="bg-white p-4 flex items-center">
        <input type="text" placeholder="Type your message..."
            class="flex-1 border rounded-full px-4 py-2 focus:outline-none" />
        <Button icon="pi pi-send" aria-label="Save" severity="info" />
    </div>
</template>

<template>
    <div class="bg-white p-4 flex items-center">
        <input class="text-black flex-1 border rounded-full px-4 py-2 m-2" type="text" v-model="message"
            @keypress.enter="sendMessage" placeholder="Type your message ..." />
        <Button @click="sendMessage" icon="pi pi-send" aria-label="Save" severity="info" />
    </div>
</template>

<script setup lang="ts">
import { Button } from 'primevue';
import { ref } from 'vue';

const emits = defineEmits<{ sendMessage: [text: string] }>()

const message = ref('')

const sendMessage = () => {
    if (!message.value) return
    emits('sendMessage', message.value)
    message.value = ''

}

</script>

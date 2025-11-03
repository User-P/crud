<template>
    <AdminLayout title="Chat">
        <div class="bg-gray-100 h-screen flex flex-col max-w-lg mx-auto">
            <div class="bg-blue-500 p-4 text-white flex justify-between items-center">
                <span>La mera mera</span>
            </div>
            <ChatMessages :messages="messages" />
            <MessageBox @send-message="onMessage" />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ChatMessages from '@/Components/ChatMessages.vue';
import MessageBox from '@/Components/MessageBox.vue';
import { ChatMessage } from '@/interfaces/chat-message.interface';
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue';

const messages = ref<ChatMessage[]>([{
    id: new Date().getTime(),
    message: 'hola mundo',
    itsMine: true
}, {
    id: new Date().getTime(),
    message: 'hola mundo',
    itsMine: false
}, {
    id: new Date().getTime(),
    message: 'hola mundo',
    itsMine: true
}])

const onMessage = (text: string) => {
    messages.value.push({
        id: new Date().getTime(),
        itsMine: true,
        message: text
    })
}

</script>

<style scoped>
code {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
</style>

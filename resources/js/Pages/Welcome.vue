<template>
    <AdminLayout title="Chat">
        <!-- Fuente: https://tailwindcomponents.com/component/chat-layout -->
        <div class="bg-gray-100 h-screen flex flex-col max-w-lg mx-auto">
            <div class="bg-blue-500 p-4 text-white flex justify-between items-center">
                <span>Mi esposa</span>
            </div>

            <ChatMessages />

            <MessageBox />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import ChatMessages from '@/Components/ChatMessages.vue';
import MessageBox from '@/Components/MessageBox.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue'

</script>

<style scoped>
code {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
</style>

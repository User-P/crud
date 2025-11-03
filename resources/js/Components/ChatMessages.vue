<template>
    <div class="flex-1 overflow-y-auto p-4">
        <ChatBubble :its-mine="true" message="hola" />
        <ChatBubble :its-mine="false" message="hola" />
        <ChatBubble :its-mine="true" message="hola" />
    </div>
</template>

<script setup lang="ts">
import ChatBubble from './ChatBubble.vue';

</script>

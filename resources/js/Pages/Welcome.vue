<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 flex items-center justify-center p-6">
   <!-- mostrar quotes con vfor -->
    <ol class="space-y-4 max-w-2xl bg-white p-6 rounded-xl shadow-lg">
      <li v-for="(item, index) in quotes" :key="index" class="border-b pb-4 last:border-0 last:pb-0">
        <p class="text-gray-800 italic">"{{ item.quote }}"</p>
        <p class="text-gray-600 text-right mt-2">- {{ item.author }}</p>
      </li>
    </ol>
  </div>
</template>

<script setup lang="ts">

const quotes = [
    { quote: 'The night is darkest just before the dawn. And I promise you, the dawn is coming.', author: 'Harvey Dent, The Dark Knight' },
    { quote: 'I believe what doesn’t kill you simply makes you, stranger.', author: 'The Joker, The Dark Knight' },
    { quote: 'Your anger gives you great power. But if you let it, it will destroy you… As it almost did me', author: 'Henri Ducard, Batman Begins' },
    { quote: 'You either die a hero or live long enough to see yourself become the villain.', author: 'Harvey Dent, The Dark Knight' },
    { quote: 'If you’re good at something, never do it for free.', author: 'The Joker, The Dark Knight' },
    { quote: 'Yes, father. I shall become a bat.', author: 'Bruce Wayne/Batman, Batman: Year One' },
]

</script>

<style scoped>
code {
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}
</style>

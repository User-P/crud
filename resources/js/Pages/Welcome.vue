<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 flex items-center justify-center p-6">
    <div class="max-w-4xl w-full bg-white rounded-2xl shadow-2xl p-8">
      <div class="bg-gray-50 rounded-xl p-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">✅ Próximos pasos:</h3>
        <ul class="space-y-3 text-gray-700">
          <li class="flex items-start">
            <span class="text-2xl mr-3">📁</span>
            <span>Crea tus componentes Vue en <code class="bg-gray-200 px-2 py-1 rounded">resources/js/Pages/</code></span>
          </li>
          <li class="flex items-start">
            <span class="text-2xl mr-3">🔄</span>
            <span>Usa <code class="bg-gray-200 px-2 py-1 rounded">Inertia::render('ComponentName')</code> en tus controladores</span>
          </li>
        </ul>
      </div>

      <div class="mt-8 text-center">
        <button
          @click="incrementCounter"
          class="bg-gradient-to-r from-purple-500 to-blue-500 text-white font-bold py-3 px-8 rounded-lg hover:from-purple-600 hover:to-blue-600 transition-all transform hover:scale-105"
        >
          Contador: {{ counter }}
        </button>
        <p class="text-sm text-gray-500 mt-3">¡Haz clic para probar la reactividad de Vue!</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
// Estado reactivo de Vue
const counter = ref<number>(0)

const incrementCounter = (): void => {
  counter.value++
}
</script>

<style scoped>
code {
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}
</style>

<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 flex items-center justify-center p-6">
    <div class="max-w-4xl w-full bg-white rounded-2xl shadow-2xl p-8">
      <div class="text-center mb-8">
        <h1 class="text-5xl font-bold text-gray-800 mb-4">
          🎉 ¡Inertia.js + Vue 3 está funcionando!
        </h1>
        <p class="text-xl text-gray-600">
          Laravel {{ laravelVersion }} con Inertia y Vue 3
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
          <h2 class="text-2xl font-bold mb-2">🚀 Backend</h2>
          <p class="text-purple-100">Laravel + Inertia.js</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
          <h2 class="text-2xl font-bold mb-2">⚡ Frontend</h2>
          <p class="text-blue-100">Vue 3 + Vite</p>
        </div>
      </div>

      <div class="bg-gray-50 rounded-xl p-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">✅ Próximos pasos:</h3>
        <ul class="space-y-3 text-gray-700">
          <li class="flex items-start">
            <span class="text-2xl mr-3">📁</span>
            <span>Crea tus componentes Vue en <code class="bg-gray-200 px-2 py-1 rounded">resources/js/Pages/</code></span>
          </li>
          <li class="flex items-start">
            <span class="text-2xl mr-3">🎨</span>
            <span>Agrega Tailwind CSS (opcional pero recomendado)</span>
          </li>
          <li class="flex items-start">
            <span class="text-2xl mr-3">🔄</span>
            <span>Usa <code class="bg-gray-200 px-2 py-1 rounded">Inertia::render('ComponentName')</code> en tus controladores</span>
          </li>
          <li class="flex items-start">
            <span class="text-2xl mr-3">⚡</span>
            <span>Ejecuta <code class="bg-gray-200 px-2 py-1 rounded">npm run dev</code> para desarrollo en vivo</span>
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

<script setup>
import { ref } from 'vue'

// Props desde Laravel
defineProps({
  laravelVersion: {
    type: String,
    default: '11.x'
  }
})

// Estado reactivo de Vue
const counter = ref(0)

const incrementCounter = () => {
  counter.value++
}
</script>

<style scoped>
code {
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}
</style>

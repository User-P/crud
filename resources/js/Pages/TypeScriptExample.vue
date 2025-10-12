<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl p-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-6">
        🎯 TypeScript + Vue 3 + Inertia.js
      </h1>

      <div class="space-y-6">
        <!-- User Info Card -->
        <div class="bg-blue-50 rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-blue-900 mb-4">👤 Usuario</h2>
          <div class="space-y-2 text-gray-700">
            <p><strong>Nombre:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Edad:</strong> {{ user.age }}</p>
          </div>
        </div>

        <!-- Counter with TypeScript -->
        <div class="bg-purple-50 rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-purple-900 mb-4">🔢 Contador Tipado</h2>
          <p class="text-gray-700 mb-4">Contador: <span class="font-bold text-2xl text-purple-600">{{ count }}</span></p>
          <div class="space-x-2">
            <button
              @click="increment"
              class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition"
            >
              Incrementar
            </button>
            <button
              @click="decrement"
              class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition"
            >
              Decrementar
            </button>
            <button
              @click="reset"
              class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition"
            >
              Reiniciar
            </button>
          </div>
        </div>

        <!-- Computed Property -->
        <div class="bg-green-50 rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-green-900 mb-4">🧮 Propiedad Computada</h2>
          <p class="text-gray-700">
            El doble del contador es: <span class="font-bold text-2xl text-green-600">{{ doubleCount }}</span>
          </p>
        </div>

        <!-- Array with Types -->
        <div class="bg-orange-50 rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-orange-900 mb-4">📋 Lista de Tareas</h2>
          <ul class="space-y-2">
            <li
              v-for="task in tasks"
              :key="task.id"
              class="flex items-center space-x-3 text-gray-700"
            >
              <input
                type="checkbox"
                :checked="task.completed"
                @change="toggleTask(task.id)"
                class="w-5 h-5"
              />
              <span :class="{ 'line-through text-gray-400': task.completed }">
                {{ task.title }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

// ============ INTERFACES ============
interface User {
  name: string
  email: string
  age: number
}

interface Task {
  id: number
  title: string
  completed: boolean
}

interface Props {
  initialCount?: number
  user?: User
}

// ============ PROPS ============
const props = withDefaults(defineProps<Props>(), {
  initialCount: 0,
  user: () => ({
    name: 'Juan Pérez',
    email: 'juan@example.com',
    age: 28
  })
})

// ============ ESTADO REACTIVO ============
const count = ref<number>(props.initialCount)

const tasks = ref<Task[]>([
  { id: 1, title: 'Configurar TypeScript', completed: true },
  { id: 2, title: 'Instalar Vue 3', completed: true },
  { id: 3, title: 'Integrar Inertia.js', completed: true },
  { id: 4, title: 'Crear componentes tipados', completed: false },
  { id: 5, title: 'Disfrutar el desarrollo', completed: false }
])

// ============ COMPUTED PROPERTIES ============
const doubleCount = computed<number>(() => count.value * 2)

// ============ MÉTODOS CON TIPOS ============
const increment = (): void => {
  count.value++
}

const decrement = (): void => {
  count.value--
}

const reset = (): void => {
  count.value = 0
}

const toggleTask = (taskId: number): void => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (task) {
    task.completed = !task.completed
  }
}

// ============ EJEMPLO DE FUNCIÓN CON PARÁMETROS TIPADOS ============
const greetUser = (user: User): string => {
  return `Hola ${user.name}, tienes ${user.age} años`
}

// Ejemplo de uso (solo para demostración)
console.log(greetUser(props.user))
</script>

<style scoped>
/* Estilos adicionales si necesitas */
</style>

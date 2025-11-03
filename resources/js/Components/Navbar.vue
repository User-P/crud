<template>
  <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    <!-- Mobile menu button -->
    <button
      type="button"
      class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
      @click="$emit('toggle-sidebar')"
    >
      <span class="sr-only">Abrir sidebar</span>
      <Bars3Icon class="h-6 w-6" aria-hidden="true" />
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true" />

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
      <!-- Search -->
      <form class="relative flex flex-1" action="#" method="GET">
        <label for="search-field" class="sr-only">Buscar</label>
        <MagnifyingGlassIcon
          class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400"
          aria-hidden="true"
        />
        <input
          id="search-field"
          class="block h-full w-full border-0 py-0 pl-8 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
          placeholder="Buscar..."
          type="search"
          name="search"
        />
      </form>

      <div class="flex items-center gap-x-4 lg:gap-x-6">
        <!-- Notifications button -->
        <button
          type="button"
          class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500"
        >
          <span class="sr-only">Ver notificaciones</span>
          <BellIcon class="h-6 w-6" aria-hidden="true" />
        </button>

        <!-- Separator -->
        <div
          class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"
          aria-hidden="true"
        />

        <!-- Profile dropdown -->
        <div class="relative">
          <button class="-m-1.5 flex items-center p-1.5" type="button" @click="toggleProfileMenu">
            <span class="sr-only">Abrir menú de usuario</span>
            <img
              class="h-8 w-8 rounded-full bg-gray-50"
              src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff"
              alt=""
            />
            <span class="hidden lg:flex lg:items-center">
              <span
                class="ml-4 text-sm font-semibold leading-6 text-gray-900"
                aria-hidden="true"
              >
                {{ user?.name || 'Usuario' }}
              </span>
              <ChevronDownIcon
                class="ml-2 h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </span>
          </button>
          <PrimeMenu ref="profileMenu" :model="profileMenuItems" popup />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import PrimeMenu, { type MenuMethods } from 'primevue/menu'
import {
  Bars3Icon,
  BellIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

defineEmits<{
  'toggle-sidebar': []
}>()

const page = usePage<{ auth?: { user: any } }>()
const user = computed(() => page.props.auth?.user)

const profileMenu = ref<MenuMethods | null>(null)
const profileMenuItems = [
  { label: 'Tu Perfil', icon: 'pi pi-user', command: () => navigate('/profile') },
  { separator: true },
  { label: 'Cerrar Sesión', icon: 'pi pi-sign-out', command: () => logout() },
]

const toggleProfileMenu = (event: MouseEvent): void => {
  profileMenu.value?.toggle(event)
}

const navigate = (href: string): void => {
  router.visit(href)
}

const logout = (): void => {
  router.post('/logout')
}
</script>

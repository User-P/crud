<template>
  <div class="flex h-screen flex-col bg-gray-900">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center border-b border-gray-800 px-6">
      <h1 class="text-2xl font-bold text-white">
        <span class="text-indigo-400">Admin</span> Panel
      </h1>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col overflow-y-auto px-3 py-4">
      <ul role="list" class="flex flex-1 flex-col gap-y-1">
        <li v-for="item in navigation" :key="item.name">
          <a
            :href="item.href"
            :class="[
              isCurrentRoute(item.href)
                ? 'bg-gray-800 text-white'
                : 'text-gray-400 hover:bg-gray-800 hover:text-white',
              'group flex gap-x-3 rounded-md p-3 text-sm font-semibold leading-6 transition-colors'
            ]"
            @click.prevent="navigate(item.href)"
          >
            <component
              :is="item.icon"
              class="h-6 w-6 shrink-0"
              aria-hidden="true"
            />
            {{ item.name }}
          </a>
        </li>
      </ul>
    </nav>

    <!-- User Profile -->
    <div class="border-t border-gray-800 p-4">
      <Menu as="div" class="relative">
        <MenuButton class="flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
          <img
            class="h-8 w-8 rounded-full bg-gray-800"
            src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff"
            alt="User avatar"
          />
          <span class="flex-1 text-left">
            <span class="block">{{ user?.name || 'Usuario' }}</span>
            <span class="block text-xs text-gray-500">{{ user?.email || 'admin@example.com' }}</span>
          </span>
          <ChevronUpDownIcon class="h-5 w-5" aria-hidden="true" />
        </MenuButton>
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <MenuItems class="absolute bottom-full left-0 mb-2 w-full origin-bottom rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <MenuItem v-slot="{ active }">
              <a
                href="/profile"
                :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                @click.prevent="navigate('/profile')"
              >
                Tu Perfil
              </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
              <a
                href="/settings"
                :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                @click.prevent="navigate('/settings')"
              >
                Configuración
              </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
              <a
                href="/logout"
                :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                @click.prevent="logout"
              >
                Cerrar Sesión
              </a>
            </MenuItem>
          </MenuItems>
        </transition>
      </Menu>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import {
  HomeIcon,
  UsersIcon,
  FolderIcon,
  CalendarIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  ChevronUpDownIcon,
} from '@heroicons/vue/24/outline'

interface NavigationItem {
  name: string
  href: string
  icon: any
}

const navigation: NavigationItem[] = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'Usuarios', href: '/users', icon: UsersIcon },
  { name: 'Países', href: '/countries', icon: FolderIcon },
  { name: 'Eventos', href: '/events', icon: CalendarIcon },
  { name: 'Estadísticas', href: '/statistics', icon: ChartBarIcon },
  { name: 'Configuración', href: '/settings', icon: Cog6ToothIcon },
]

const page = usePage<{ auth?: { user: any } }>()
const user = computed(() => page.props.auth?.user)

const isCurrentRoute = (href: string): boolean => {
  return window.location.pathname === href
}

const navigate = (href: string): void => {
  router.visit(href)
}

const logout = (): void => {
  router.post('/logout')
}
</script>

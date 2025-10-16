<template>
  <div class="min-h-screen bg-gray-100">
    <div class="flex">
      <!-- Sidebar para desktop -->
      <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <Sidebar />
      </div>

      <!-- Sidebar móvil -->
      <TransitionRoot as="template" :show="sidebarOpen">
        <Dialog
          as="div"
          class="relative z-50 lg:hidden"
          @close="sidebarOpen = false"
        >
          <TransitionChild
            as="template"
            enter="transition-opacity ease-linear duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="transition-opacity ease-linear duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-gray-900/80" />
          </TransitionChild>

          <div class="fixed inset-0 flex">
            <TransitionChild
              as="template"
              enter="transition ease-in-out duration-300 transform"
              enter-from="-translate-x-full"
              enter-to="translate-x-0"
              leave="transition ease-in-out duration-300 transform"
              leave-from="translate-x-0"
              leave-to="-translate-x-full"
            >
              <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
                <TransitionChild
                  as="template"
                  enter="ease-in-out duration-300"
                  enter-from="opacity-0"
                  enter-to="opacity-100"
                  leave="ease-in-out duration-300"
                  leave-from="opacity-100"
                  leave-to="opacity-0"
                >
                  <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                    <button
                      type="button"
                      class="-m-2.5 p-2.5"
                      @click="sidebarOpen = false"
                    >
                      <span class="sr-only">Cerrar sidebar</span>
                      <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </button>
                  </div>
                </TransitionChild>
                <Sidebar />
              </DialogPanel>
            </TransitionChild>
          </div>
        </Dialog>
      </TransitionRoot>

      <!-- Contenido principal -->
      <div class="lg:pl-72 flex-1">
        <Navbar @toggle-sidebar="sidebarOpen = true" />

        <main class="py-10">
          <div class="px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs (opcional) -->
            <nav
              v-if="breadcrumbs && breadcrumbs.length > 0"
              class="mb-6"
              aria-label="Breadcrumb"
            >
              <ol role="list" class="flex items-center space-x-2">
                <li v-for="(crumb, index) in breadcrumbs" :key="crumb.name">
                  <div class="flex items-center">
                    <ChevronRightIcon
                      v-if="index > 0"
                      class="h-4 w-4 text-gray-400 mx-2"
                      aria-hidden="true"
                    />
                    <a
                      v-if="crumb.href"
                      :href="crumb.href"
                      class="text-sm font-medium text-gray-500 hover:text-gray-700"
                      @click.prevent="router.visit(crumb.href)"
                    >
                      {{ crumb.name }}
                    </a>
                    <span
                      v-else
                      class="text-sm font-medium text-gray-900"
                    >
                      {{ crumb.name }}
                    </span>
                  </div>
                </li>
              </ol>
            </nav>

            <!-- Page header (opcional) -->
            <div v-if="title" class="mb-8">
              <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                {{ title }}
              </h1>
              <p v-if="subtitle" class="mt-2 text-sm text-gray-700">
                {{ subtitle }}
              </p>
            </div>

            <!-- Contenido de la página -->
            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { XMarkIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'

interface Breadcrumb {
  name: string
  href?: string
}

interface Props {
  title?: string
  subtitle?: string
  breadcrumbs?: Breadcrumb[]
}

defineProps<Props>()

const sidebarOpen = ref(false)
</script>

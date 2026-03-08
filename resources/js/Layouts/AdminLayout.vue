<template>
    <div class="glass-app-bg min-h-screen">
        <div class="relative z-10 flex">
            <!-- Sidebar desktop -->
            <template v-if="!sidebarHidden">
                <div
                    class="hidden shrink-0 transition-[width] duration-300 ease-in-out lg:flex lg:flex-col lg:z-30"
                    :class="(sidebarCollapsed && !sidebarHoverExpanded) ? 'lg:w-[4.5rem]' : 'lg:w-64'"
                >
                    <Sidebar
                        :collapsed="sidebarCollapsed"
                        @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
                        @close="sidebarHidden = true"
                        @hover-expand="sidebarHoverExpanded = $event"
                        @collapse="onSidebarCollapse"
                    />
                </div>
            </template>

            <!-- Sidebar móvil (drawer) -->
            <PrimeSidebar
                v-model:visible="sidebarOpen"
                position="left"
                class="w-64 lg:hidden"
                modal
                blockScroll
            >
                <div class="flex h-full flex-col">
                    <Sidebar
                        :collapsed="false"
                        @toggle-collapse="() => {}"
                        @close="sidebarOpen = false"
                    />
                </div>
            </PrimeSidebar>

            <!-- Área principal -->
            <div
                class="relative z-10 min-w-0 flex-1 overflow-x-hidden transition-[margin] duration-200"
                :class="sidebarHidden ? 'lg:ml-0' : (sidebarCollapsed ? 'lg:ml-0' : 'lg:ml-0')"
            >
                <Navbar
                    :sidebar-hidden="sidebarHidden"
                    :sidebar-collapsed="sidebarCollapsed"
                    @toggle-sidebar="sidebarOpen = true"
                    @show-sidebar="sidebarHidden = false"
                    @toggle-sidebar-collapse="sidebarCollapsed = !sidebarCollapsed"
                />

                <main class="min-w-0 py-8">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <!-- Breadcrumbs -->
                        <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-6" aria-label="Breadcrumb">
                            <ol role="list" class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm">
                                <li v-for="(crumb, index) in breadcrumbs" :key="crumb.name" class="flex items-center gap-x-1.5">
                                    <Icon
                                        v-if="index > 0"
                                        icon="heroicons:chevron-right"
                                        class="h-4 w-4 shrink-0 text-slate-400/80"
                                        aria-hidden="true"
                                    />
                                    <a
                                        v-if="crumb.href"
                                        :href="crumb.href"
                                        class="font-medium text-slate-600 transition hover:text-slate-800"
                                        @click.prevent="router.visit(crumb.href)"
                                    >
                                        {{ crumb.name }}
                                    </a>
                                    <span v-else class="font-medium text-slate-800">
                                        {{ crumb.name }}
                                    </span>
                                </li>
                            </ol>
                        </nav>

                        <!-- Título de página -->
                        <div v-if="title" class="mb-8">
                            <h1 class="text-2xl font-semibold tracking-tight text-slate-800 sm:text-3xl">
                                {{ title }}
                            </h1>
                            <p v-if="subtitle" class="mt-1.5 text-sm text-slate-600">
                                {{ subtitle }}
                            </p>
                        </div>

                        <!-- Contenido -->
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
import PrimeSidebar from 'primevue/sidebar'
import { Icon } from '@iconify/vue'
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
const sidebarHidden = ref(false)
const sidebarCollapsed = ref(false)
const sidebarHoverExpanded = ref(false)

function onSidebarCollapse() {
    sidebarCollapsed.value = true
    sidebarHoverExpanded.value = false
}
</script>

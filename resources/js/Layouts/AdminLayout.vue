<template>
    <div class="cosmos-app">
        <!-- Blobs de fondo (solo dark) -->
        <div class="cosmos-blob cosmos-blob-1" aria-hidden="true" />
        <div class="cosmos-blob cosmos-blob-2" aria-hidden="true" />
        <div class="cosmos-blob cosmos-blob-3" aria-hidden="true" />

        <div class="relative z-10 flex min-h-screen">
            <!-- ── Sidebar desktop: sticky mientras scrollea el contenido ── -->
            <div
                v-if="!sidebarHidden"
                class="sticky top-0 hidden h-screen shrink-0 self-start transition-[width] duration-300 ease-in-out lg:block lg:z-30"
                :class="(sidebarCollapsed && !sidebarHoverExpanded) ? 'w-[4.5rem]' : 'w-64'"
            >
                <Sidebar
                    :collapsed="sidebarCollapsed"
                    @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
                    @close="sidebarHidden = true"
                    @hover-expand="sidebarHoverExpanded = $event"
                    @collapse="onSidebarCollapse"
                />
            </div>

            <!-- ── Sidebar móvil (drawer) ── -->
            <PrimeSidebar
                v-model:visible="sidebarOpen"
                position="left"
                :style="{ width: '16rem' }"
                class="lg:hidden"
                :show-close-icon="false"
                modal
                blockScroll
            >
                <Sidebar
                    :collapsed="false"
                    @toggle-collapse="() => {}"
                    @close="sidebarOpen = false"
                />
            </PrimeSidebar>

            <!-- ── Área principal ── -->
            <div class="flex min-w-0 flex-1 flex-col">
                <Navbar
                    :sidebar-hidden="sidebarHidden"
                    :sidebar-collapsed="sidebarCollapsed"
                    @toggle-sidebar="sidebarOpen = true"
                    @show-sidebar="sidebarHidden = false"
                    @toggle-sidebar-collapse="sidebarCollapsed = !sidebarCollapsed"
                />

                <main class="flex-1 py-8">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <!-- Breadcrumbs -->
                        <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-6" aria-label="Breadcrumb">
                            <ol role="list" class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm">
                                <li
                                    v-for="(crumb, index) in breadcrumbs"
                                    :key="crumb.name"
                                    class="flex items-center gap-x-1.5"
                                >
                                    <Icon
                                        v-if="index > 0"
                                        icon="heroicons:chevron-right"
                                        class="cosmos-crumb-separator h-3.5 w-3.5 shrink-0"
                                        aria-hidden="true"
                                    />
                                    <a
                                        v-if="crumb.href"
                                        :href="crumb.href"
                                        class="cosmos-crumb-link transition"
                                        @click.prevent="router.visit(crumb.href)"
                                    >
                                        {{ crumb.name }}
                                    </a>
                                    <span v-else class="cosmos-crumb-current font-medium">
                                        {{ crumb.name }}
                                    </span>
                                </li>
                            </ol>
                        </nav>

                        <!-- Título -->
                        <div v-if="title" class="mb-8">
                            <h1 class="cosmos-gradient-text text-2xl font-bold tracking-tight sm:text-3xl">
                                {{ title }}
                            </h1>
                            <p v-if="subtitle" class="cosmos-subtitle mt-1.5 text-sm">
                                {{ subtitle }}
                            </p>
                        </div>

                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PrimeSidebar from 'primevue/sidebar'
import { Icon } from '@iconify/vue'
import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'
import { useTheme } from '@/composables/useTheme'

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

const { init } = useTheme()
onMounted(() => init())

const sidebarOpen = ref(false)
const sidebarHidden = ref(false)
const sidebarCollapsed = ref(false)
const sidebarHoverExpanded = ref(false)

function onSidebarCollapse() {
    sidebarCollapsed.value = true
    sidebarHoverExpanded.value = false
}
</script>

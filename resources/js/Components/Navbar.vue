<template>
    <!-- Mobile search overlay -->
    <div
        v-if="mobileSearchOpen"
        class="cosmos-nav fixed inset-x-0 top-0 z-50 flex h-14 items-center gap-3 px-4"
    >
        <Icon icon="heroicons:magnifying-glass" class="cosmos-crumb-separator h-4 w-4 shrink-0" aria-hidden="true" />
        <input
            ref="mobileSearchInput"
            type="search"
            placeholder="Buscar..."
            class="cosmos-input min-w-0 flex-1 rounded-xl py-2 px-3 text-sm placeholder:italic focus:ring-0"
            @keydown.escape="mobileSearchOpen = false"
        />
        <button
            type="button"
            class="cosmos-btn rounded-xl p-2"
            aria-label="Cerrar búsqueda"
            @click="mobileSearchOpen = false"
        >
            <Icon icon="heroicons:x-mark" class="h-5 w-5" aria-hidden="true" />
        </button>
    </div>

    <header
        class="cosmos-nav sticky top-0 z-40 flex h-14 shrink-0 items-center justify-between gap-2 px-3 sm:gap-4 sm:px-6 lg:px-8"
        :class="{ 'invisible': mobileSearchOpen }"
    >
        <!-- Izquierda: menú + logo -->
        <div class="flex min-w-0 shrink-0 items-center gap-1 sm:gap-2">
            <!-- Hamburger móvil -->
            <button
                type="button"
                class="cosmos-btn rounded-xl p-2"
                aria-label="Abrir menú"
                @click="$emit('toggle-sidebar')"
            >
                <Icon icon="heroicons:bars-3" class="h-5 w-5 lg:hidden" aria-hidden="true" />
                <!-- Desktop: toggle collapse -->
                <Icon
                    v-if="!sidebarHidden"
                    :icon="sidebarCollapsed ? 'heroicons:chevron-right' : 'heroicons:chevron-left'"
                    class="hidden h-5 w-5 lg:block"
                    aria-hidden="true"
                    @click.stop="$emit('toggle-sidebar-collapse')"
                />
                <Icon
                    v-else
                    icon="heroicons:bars-3"
                    class="hidden h-5 w-5 lg:block"
                    aria-hidden="true"
                    @click.stop="$emit('show-sidebar')"
                />
            </button>

            <a
                href="/dashboard"
                class="flex min-w-0 items-center gap-2 rounded-xl py-1 pr-1 transition hover:opacity-80 sm:pr-2"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/25 sm:h-8 sm:w-8">
                    <Icon icon="heroicons:chart-bar-square" class="h-3.5 w-3.5 sm:h-4 sm:w-4" aria-hidden="true" />
                </div>
                <span class="cosmos-logo-text hidden truncate text-sm font-semibold sm:block">Analytics</span>
            </a>
        </div>

        <!-- Centro: búsqueda (sólo sm+) -->
        <div class="relative hidden min-w-0 flex-1 sm:flex sm:max-w-xs md:max-w-sm lg:max-w-xl xl:max-w-2xl mx-2 sm:mx-4">
            <form action="#" method="GET" class="relative w-full">
                <label for="search-field" class="sr-only">Buscar</label>
                <Icon
                    icon="heroicons:magnifying-glass"
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2"
                    style="color: var(--th-text-muted)"
                    aria-hidden="true"
                />
                <input
                    id="search-field"
                    type="search"
                    name="search"
                    placeholder="Buscar..."
                    class="cosmos-input block w-full rounded-xl py-2 pl-9 pr-14 text-sm placeholder:italic transition focus:ring-0 sm:py-2.5"
                />
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                    <kbd class="cosmos-kbd rounded border px-1.5 py-0.5 text-[10px] font-medium">⌘K</kbd>
                </div>
            </form>
        </div>

        <!-- Derecha -->
        <div class="flex shrink-0 items-center gap-0.5 sm:gap-1">
            <!-- Búsqueda móvil (solo en xs) -->
            <button
                type="button"
                class="cosmos-btn rounded-xl p-2 sm:hidden"
                aria-label="Buscar"
                @click="openMobileSearch"
            >
                <Icon icon="heroicons:magnifying-glass" class="h-5 w-5" aria-hidden="true" />
            </button>

            <!-- Toggle tema -->
            <button
                type="button"
                class="cosmos-btn rounded-xl p-2"
                :title="themeLabel"
                :aria-label="themeLabel"
                @click="cycleTheme"
            >
                <Icon :icon="themeIcon" class="h-5 w-5" aria-hidden="true" />
            </button>

            <!-- Notificaciones -->
            <button
                type="button"
                class="cosmos-btn relative rounded-xl p-2"
                aria-label="Notificaciones"
            >
                <Icon icon="heroicons:bell" class="h-5 w-5" aria-hidden="true" />
                <span
                    class="cosmos-notification-dot absolute right-2 top-2 h-2 w-2 animate-pulse rounded-full ring-2"
                    style="--tw-ring-color: var(--th-ring-offset)"
                    aria-hidden="true"
                />
            </button>

            <div class="cosmos-separator mx-0.5 h-5 w-px sm:mx-1" aria-hidden="true" />

            <!-- Usuario -->
            <div class="relative">
                <button
                    type="button"
                    class="cosmos-btn flex items-center gap-1.5 rounded-xl py-1.5 pl-1 pr-2 sm:gap-2 sm:pl-1.5 sm:pr-2.5"
                    aria-label="Menú de cuenta"
                    aria-haspopup="true"
                    @click="toggleProfileMenu"
                >
                    <div class="relative shrink-0">
                        <img
                            class="h-7 w-7 rounded-full object-cover ring-1 ring-violet-500/40 sm:h-8 sm:w-8"
                            :src="userAvatar"
                            :alt="user?.name || 'Usuario'"
                        />
                        <span
                            class="absolute -bottom-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-400 ring-1 sm:h-2.5 sm:w-2.5 sm:ring-2"
                            :style="`--tw-ring-color: var(--th-status-ring)`"
                            aria-hidden="true"
                        />
                    </div>
                    <span class="hidden min-w-0 text-left lg:block">
                        <span class="cosmos-user-name block truncate text-sm font-medium">{{ user?.name || 'Usuario' }}</span>
                        <span class="cosmos-user-sub block truncate text-xs">Admin</span>
                    </span>
                    <Icon icon="heroicons:chevron-down" class="h-3.5 w-3.5 shrink-0 hidden sm:block" style="color: var(--th-text-muted)" aria-hidden="true" />
                </button>
                <PrimeMenu ref="profileMenu" :model="profileMenuItems" popup />
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { computed, ref, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import PrimeMenu, { type MenuMethods } from 'primevue/menu'
import { Icon } from '@iconify/vue'
import { useTheme } from '@/composables/useTheme'

defineProps<{
    sidebarHidden?: boolean
    sidebarCollapsed?: boolean
}>()

defineEmits<{
    'toggle-sidebar': []
    'show-sidebar': []
    'toggle-sidebar-collapse': []
}>()

const { mode, cycleTheme } = useTheme()

const themeIcon = computed(() => {
    if (mode.value === 'dark') return 'heroicons:moon'
    if (mode.value === 'light') return 'heroicons:sun'
    return 'heroicons:computer-desktop'
})

const themeLabel = computed(() => {
    if (mode.value === 'dark') return 'Modo oscuro — cambiar'
    if (mode.value === 'light') return 'Modo claro — cambiar'
    return 'Modo sistema — cambiar'
})

const mobileSearchOpen = ref(false)
const mobileSearchInput = ref<HTMLInputElement | null>(null)

async function openMobileSearch() {
    mobileSearchOpen.value = true
    await nextTick()
    mobileSearchInput.value?.focus()
}

const page = usePage<{ auth?: { user: any } }>()
const user = computed(() => page.props.auth?.user)
const userAvatar = computed(() => {
    if (user.value?.profile_photo_url) return user.value.profile_photo_url
    const initials = encodeURIComponent(user.value?.name || 'Usuario')
    return `https://ui-avatars.com/api/?name=${initials}&background=6366f1&color=fff&size=64`
})

const profileMenu = ref<MenuMethods | null>(null)
const profileMenuItems = [
    { label: 'Tu Perfil', icon: 'pi pi-user', command: () => navigate('/profile') },
    { label: 'Configuración', icon: 'pi pi-cog', command: () => navigate('/settings') },
    { separator: true },
    { label: 'Cerrar Sesión', icon: 'pi pi-sign-out', command: () => logout() },
]

function toggleProfileMenu(event: MouseEvent) {
    profileMenu.value?.toggle(event)
}

function navigate(href: string) {
    router.visit(href)
}

function logout() {
    router.post('/logout')
}
</script>

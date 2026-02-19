<template>
    <header class="sticky top-0 z-40 flex h-14 shrink-0 items-center gap-x-3 border-b border-slate-200/80 bg-white px-4 shadow-sm shadow-slate-200/50 sm:gap-x-4 sm:px-6 lg:px-8">
        <!-- Menú: móvil = abrir drawer; desktop = mostrar/contraer según estado -->
        <div class="flex items-center gap-x-1">
            <button
                type="button"
                class="rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                aria-label="Abrir menú"
                @click="$emit('toggle-sidebar')"
            >
                <Bars3Icon class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
                v-if="sidebarHidden"
                type="button"
                class="hidden items-center gap-x-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 lg:flex"
                aria-label="Mostrar menú lateral"
                @click="$emit('show-sidebar')"
            >
                <Bars3Icon class="h-5 w-5" aria-hidden="true" />
                <span>Menú</span>
            </button>
            <button
                v-else
                type="button"
                class="hidden rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 lg:block"
                :aria-label="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                @click="$emit('toggle-sidebar-collapse')"
            >
                <ChevronRightIcon v-if="sidebarCollapsed" class="h-5 w-5" aria-hidden="true" />
                <ChevronLeftIcon v-else class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>

        <div class="h-5 w-px bg-slate-200" aria-hidden="true" />

        <!-- Búsqueda -->
        <form class="relative flex flex-1 max-w-xl" action="#" method="GET">
            <label for="search-field" class="sr-only">Buscar</label>
            <MagnifyingGlassIcon
                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                aria-hidden="true"
            />
            <input
                id="search-field"
                type="search"
                name="search"
                placeholder="Buscar..."
                class="block w-full rounded-xl border-0 bg-slate-50/80 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 ring-1 ring-slate-200/80 transition focus:ring-2 focus:ring-indigo-500/20 sm:leading-6"
            />
        </form>

        <div class="flex items-center gap-x-1 sm:gap-x-2">
            <!-- Notificaciones -->
            <button
                type="button"
                class="rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Notificaciones"
            >
                <BellIcon class="h-5 w-5" aria-hidden="true" />
            </button>

            <div class="h-6 w-px bg-slate-200" aria-hidden="true" />

            <!-- Usuario -->
            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-x-2 rounded-xl py-1.5 pl-1.5 pr-2 transition hover:bg-slate-100"
                    aria-label="Abrir menú de cuenta"
                    @click="toggleProfileMenu"
                >
                    <img
                        class="h-8 w-8 rounded-full border border-slate-200 object-cover"
                        :src="userAvatar"
                        :alt="user?.name || 'Usuario'"
                    />
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-medium text-slate-900">{{ user?.name || 'Usuario' }}</span>
                        <span class="block text-xs text-slate-500">Cuenta</span>
                    </span>
                    <ChevronDownIcon class="hidden h-4 w-4 text-slate-400 sm:block" aria-hidden="true" />
                </button>
                <PrimeMenu ref="profileMenu" :model="profileMenuItems" popup />
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
    ChevronLeftIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

defineProps<{
    sidebarHidden?: boolean
    sidebarCollapsed?: boolean
}>()

defineEmits<{
    'toggle-sidebar': []
    'show-sidebar': []
    'toggle-sidebar-collapse': []
}>()

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

<template>
    <header class="sticky top-0 z-40 flex h-14 shrink-0 items-center justify-between gap-4 border-b border-slate-200/80 bg-white px-4 shadow-sm sm:px-6 lg:px-8">
        <!-- Izquierda: menú + logo -->
        <div class="flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
            <button
                type="button"
                class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                aria-label="Abrir menú"
                @click="$emit('toggle-sidebar')"
            >
                <Icon icon="heroicons:bars-3" class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
                v-if="sidebarHidden"
                type="button"
                class="hidden items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 lg:flex"
                aria-label="Mostrar menú lateral"
                @click="$emit('show-sidebar')"
            >
                <Icon icon="heroicons:bars-3" class="h-5 w-5" aria-hidden="true" />
                <span>Menú</span>
            </button>
            <button
                v-else
                type="button"
                class="hidden rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 lg:block"
                :aria-label="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                @click="$emit('toggle-sidebar-collapse')"
            >
                <Icon v-if="sidebarCollapsed" icon="heroicons:chevron-right" class="h-5 w-5" aria-hidden="true" />
                <Icon v-else icon="heroicons:chevron-left" class="h-5 w-5" aria-hidden="true" />
            </button>

            <a
                href="/dashboard"
                class="flex items-center gap-2 truncate rounded-lg py-1.5 pr-2 transition hover:opacity-90"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm">
                    <Icon icon="heroicons:chart-bar-square" class="h-4 w-4" aria-hidden="true" />
                </div>
                <span class="hidden truncate text-sm font-semibold text-slate-800 sm:block">Analytics</span>
            </a>
        </div>

        <!-- Centro: búsqueda -->
        <div class="relative min-w-0 flex-1 max-w-xl mx-4">
            <form action="#" method="GET" class="relative">
                <label for="search-field" class="sr-only">Buscar</label>
                <Icon
                    icon="heroicons:magnifying-glass"
                    class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                    aria-hidden="true"
                />
                <input
                    id="search-field"
                    type="search"
                    name="search"
                    placeholder="Buscar..."
                    class="block w-full rounded-lg border-0 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 ring-1 ring-slate-200/80 transition placeholder:italic focus:ring-2 focus:ring-indigo-500/20"
                />
            </form>
        </div>

        <!-- Derecha: notificaciones + usuario -->
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            <button
                type="button"
                class="rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Notificaciones"
            >
                <Icon icon="heroicons:bell" class="h-5 w-5" aria-hidden="true" />
            </button>

            <div class="h-6 w-px bg-slate-200" aria-hidden="true" />

            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg py-1.5 pl-1 pr-2.5 transition hover:bg-slate-100 sm:gap-2.5 sm:pl-2"
                    aria-label="Menú de cuenta"
                    aria-haspopup="true"
                    aria-expanded="false"
                    @click="toggleProfileMenu"
                >
                    <img
                        class="h-8 w-8 shrink-0 rounded-full border border-slate-200 object-cover"
                        :src="userAvatar"
                        :alt="user?.name || 'Usuario'"
                    />
                    <span class="hidden min-w-0 text-left md:block">
                        <span class="block truncate text-sm font-medium text-slate-900">{{ user?.name || 'Usuario' }}</span>
                        <span class="block truncate text-xs text-slate-500">Cuenta</span>
                    </span>
                    <Icon icon="heroicons:chevron-down" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
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
import { Icon } from '@iconify/vue'

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

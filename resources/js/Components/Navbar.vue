<template>
    <header class="cosmos-nav sticky top-0 z-40 flex h-14 shrink-0 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <!-- Izquierda: menú + logo -->
        <div class="flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
            <button
                type="button"
                class="rounded-xl p-2 text-slate-400 transition hover:bg-white/[0.07] hover:text-slate-200 lg:hidden"
                aria-label="Abrir menú"
                @click="$emit('toggle-sidebar')"
            >
                <Icon icon="heroicons:bars-3" class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
                v-if="sidebarHidden"
                type="button"
                class="hidden items-center gap-2 rounded-xl px-2.5 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/[0.07] hover:text-slate-200 lg:flex"
                aria-label="Mostrar menú lateral"
                @click="$emit('show-sidebar')"
            >
                <Icon icon="heroicons:bars-3" class="h-5 w-5" aria-hidden="true" />
                <span>Menú</span>
            </button>
            <button
                v-else
                type="button"
                class="hidden rounded-xl p-2 text-slate-400 transition hover:bg-white/[0.07] hover:text-slate-200 lg:block"
                :aria-label="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                @click="$emit('toggle-sidebar-collapse')"
            >
                <Icon v-if="sidebarCollapsed" icon="heroicons:chevron-right" class="h-5 w-5" aria-hidden="true" />
                <Icon v-else icon="heroicons:chevron-left" class="h-5 w-5" aria-hidden="true" />
            </button>

            <a
                href="/dashboard"
                class="flex items-center gap-2 truncate rounded-xl py-1.5 pr-2 transition hover:opacity-80"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/25">
                    <Icon icon="heroicons:chart-bar-square" class="h-4 w-4" aria-hidden="true" />
                </div>
                <span class="hidden truncate text-sm font-semibold text-slate-100 sm:block">Analytics</span>
            </a>
        </div>

        <!-- Centro: búsqueda -->
        <div class="relative min-w-0 flex-1 max-w-xl mx-4">
            <form action="#" method="GET" class="relative">
                <label for="search-field" class="sr-only">Buscar</label>
                <Icon
                    icon="heroicons:magnifying-glass"
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500"
                    aria-hidden="true"
                />
                <input
                    id="search-field"
                    type="search"
                    name="search"
                    placeholder="Buscar..."
                    class="cosmos-input block w-full rounded-xl py-2.5 pl-9 pr-16 text-sm placeholder:italic transition focus:ring-0"
                />
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-0.5">
                    <kbd class="rounded border border-white/10 bg-white/4 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">⌘K</kbd>
                </div>
            </form>
        </div>

        <!-- Derecha: notificaciones + usuario -->
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            <!-- Notificaciones con badge pulsante -->
            <button
                type="button"
                class="relative rounded-xl p-2.5 text-slate-400 transition hover:bg-white/[0.07] hover:text-slate-200"
                aria-label="Notificaciones"
            >
                <Icon icon="heroicons:bell" class="h-5 w-5" aria-hidden="true" />
                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-violet-500 ring-2 ring-[#060611] animate-pulse" aria-hidden="true" />
            </button>

            <div class="h-6 w-px bg-white/9" aria-hidden="true" />

            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-xl py-1.5 pl-1 pr-2.5 transition hover:bg-white/[0.07] sm:gap-2.5 sm:pl-2"
                    aria-label="Menú de cuenta"
                    aria-haspopup="true"
                    aria-expanded="false"
                    @click="toggleProfileMenu"
                >
                    <div class="relative shrink-0">
                        <img
                            class="h-8 w-8 rounded-full object-cover ring-2 ring-violet-500/30 ring-offset-1 ring-offset-[#060611]"
                            :src="userAvatar"
                            :alt="user?.name || 'Usuario'"
                        />
                        <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#060611]" aria-hidden="true" />
                    </div>
                    <span class="hidden min-w-0 text-left md:block">
                        <span class="block truncate text-sm font-medium text-slate-200">{{ user?.name || 'Usuario' }}</span>
                        <span class="block truncate text-xs text-slate-500">Administrador</span>
                    </span>
                    <Icon icon="heroicons:chevron-down" class="h-4 w-4 shrink-0 text-slate-600" aria-hidden="true" />
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

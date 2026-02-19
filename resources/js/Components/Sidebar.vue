<template>
    <aside
        class="flex h-screen flex-col border-r border-slate-200/60 bg-white shadow-lg shadow-slate-200/50 transition-[width] duration-200 ease-out"
        :class="collapsed ? 'w-[4.5rem]' : 'w-64'"
    >
        <!-- Logo + contraer/cerrar -->
        <div
            class="flex h-14 shrink-0 items-center border-b border-slate-100"
            :class="collapsed ? 'justify-center px-0' : 'justify-between px-4'"
        >
            <a
                v-if="!collapsed"
                href="/dashboard"
                class="flex items-center gap-3 rounded-lg transition hover:opacity-90"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                    <ChartBarSquareIcon class="h-5 w-5" aria-hidden="true" />
                </div>
                <div class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-slate-900">Analytics</span>
                    <span class="block truncate text-[10px] font-medium uppercase tracking-wider text-slate-500">Panel</span>
                </div>
            </a>
            <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                <ChartBarSquareIcon class="h-5 w-5" aria-hidden="true" />
            </div>
            <div v-if="!collapsed" class="flex items-center gap-0.5">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
                    aria-label="Contraer menú"
                    @click="$emit('toggle-collapse')"
                >
                    <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    title="Ocultar menú (usar toda la pantalla)"
                    aria-label="Ocultar menú"
                    @click="$emit('close')"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3">
            <ul role="list" class="space-y-1 px-2">
                <template v-for="group in navGroups" :key="group.label">
                    <li v-if="!collapsed" class="px-3 pt-4 first:pt-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            {{ group.label }}
                        </p>
                    </li>
                    <template v-for="item in group.items" :key="item.name">
                        <!-- Item con submenú -->
                        <li v-if="item.children?.length">
                            <div class="relative">
                                <button
                                    type="button"
                                    :title="collapsed ? item.name : undefined"
                                    :class="[
                                        isGroupActive(item)
                                            ? 'bg-indigo-50 text-indigo-700'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                        'flex w-full items-center gap-3 rounded-lg py-2.5 text-left text-sm font-medium transition',
                                        collapsed ? 'justify-center px-2' : 'px-3',
                                    ]"
                                    @click="collapsed ? goFirstChild(item) : toggleExpand(item)"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-5 w-5 shrink-0"
                                        :class="isGroupActive(item) ? 'text-indigo-600' : 'text-slate-500'"
                                        aria-hidden="true"
                                    />
                                    <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                                    <ChevronDownIcon
                                        v-if="!collapsed"
                                        class="ml-auto h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                        :class="{ 'rotate-180': expandedItems.includes(item.name) }"
                                        aria-hidden="true"
                                    />
                                </button>
                                <ul
                                    v-if="!collapsed && expandedItems.includes(item.name)"
                                    class="mt-0.5 space-y-0.5 border-l-2 border-slate-200 pl-4 ml-5"
                                    role="list"
                                >
                                    <li v-for="child in item.children" :key="child.href">
                                        <a
                                            :href="child.href"
                                            :class="[
                                                isCurrentRoute(child.href)
                                                    ? 'font-medium text-indigo-600'
                                                    : 'text-slate-600 hover:text-slate-900',
                                                'block rounded-md py-2 px-2 text-sm transition',
                                            ]"
                                            @click.prevent="navigate(child.href)"
                                        >
                                            {{ child.name }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- Item simple -->
                        <li v-else>
                            <a
                                v-if="item.href"
                                :href="item.href"
                                :title="collapsed ? item.name : undefined"
                                :class="[
                                    isCurrentRoute(item.href)
                                        ? 'bg-indigo-50 text-indigo-700'
                                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                    'flex items-center gap-3 rounded-lg py-2.5 text-sm font-medium transition',
                                    collapsed ? 'justify-center px-2' : 'px-3',
                                ]"
                                @click.prevent="item.href && navigate(item.href)"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-5 w-5 shrink-0"
                                    :class="isCurrentRoute(item.href) ? 'text-indigo-600' : 'text-slate-500'"
                                    aria-hidden="true"
                                />
                                <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                            </a>
                        </li>
                    </template>
                </template>
            </ul>
        </nav>

        <!-- Usuario + expandir (cuando está contraído) -->
        <div class="border-t border-slate-100 p-2">
            <div v-if="collapsed" class="flex flex-col items-center gap-1">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    title="Expandir menú"
                    aria-label="Expandir menú"
                    @click="$emit('toggle-collapse')"
                >
                    <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
            <button
                type="button"
                :title="collapsed ? (user?.name || 'Usuario') : undefined"
                class="flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-slate-50"
                @click="toggleProfileMenu"
            >
                <img
                    class="h-9 w-9 shrink-0 rounded-full border-2 border-slate-200 object-cover"
                    :src="userAvatar"
                    :alt="user?.name || 'Usuario'"
                />
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-slate-900">{{ user?.name || 'Usuario' }}</span>
                    <span class="block truncate text-xs text-slate-500">{{ user?.email || 'admin@example.com' }}</span>
                </div>
                <ChevronUpDownIcon v-if="!collapsed" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
            </button>
            <PrimeMenu ref="profileMenu" :model="profileMenuItems" popup />
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import PrimeMenu, { type MenuMethods } from 'primevue/menu'
import {
    HomeIcon,
    UsersIcon,
    FolderIcon,
    CalendarIcon,
    ChartBarIcon,
    ChartPieIcon,
    Cog6ToothIcon,
    ChevronUpDownIcon,
    ChevronDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
    PresentationChartBarIcon,
    ChartBarSquareIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'

interface NavChild {
    name: string
    href: string
}

interface NavigationItem {
    name: string
    href?: string
    icon: any
    children?: NavChild[]
}

interface NavGroup {
    label: string
    items: NavigationItem[]
}

const props = defineProps<{
    collapsed: boolean
}>()

defineEmits<{
    'toggle-collapse': []
    'close': []
}>()

const navGroups: NavGroup[] = [
    {
        label: 'Principal',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
            { name: 'Chat', href: '/chat', icon: ChatBubbleLeftRightIcon },
        ],
    },
    {
        label: 'Datos',
        items: [
            { name: 'Usuarios', href: '/users', icon: UsersIcon },
            { name: 'Países', href: '/countries', icon: FolderIcon },
            { name: 'Eventos', href: '/events', icon: CalendarIcon },
        ],
    },
    {
        label: 'Análisis',
        items: [
            { name: 'Estadísticas', href: '/statistics', icon: ChartBarIcon },
            {
                name: 'Dashboards de métricas',
                icon: PresentationChartBarIcon,
                children: [
                    { name: 'Índice', href: '/dashboards' },
                    { name: 'Vista general', href: '/dashboards/vista-general' },
                    { name: 'Usuarios activos/inactivos', href: '/dashboards/usuarios-activos-inactivos' },
                    { name: 'Días suspendidos', href: '/dashboards/dias-suspendidos' },
                ],
            },
            { name: 'Tablas', href: '/tables', icon: FolderIcon },
            { name: 'Charts', href: '/charts', icon: ChartPieIcon },
        ],
    },
    {
        label: 'Sistema',
        items: [
            { name: 'Configuración', href: '/settings', icon: Cog6ToothIcon },
        ],
    },
]

const expandedItems = ref<string[]>(['Dashboards de métricas'])

function toggleExpand(item: NavigationItem) {
    const name = item.name
    const idx = expandedItems.value.indexOf(name)
    if (!item.children?.length) return
    if (idx >= 0) {
        expandedItems.value = expandedItems.value.filter((n) => n !== name)
    } else {
        expandedItems.value = [...expandedItems.value, name]
    }
}

function goFirstChild(item: NavigationItem) {
    const first = item.children?.[0]
    if (first?.href) navigate(first.href)
}

function isGroupActive(item: NavigationItem): boolean {
    if (item.href) return isCurrentRoute(item.href)
    if (!item.children) return false
    return item.children.some((c) => window.location.pathname === c.href)
}

const page = usePage<{ auth?: { user: any } }>()
const user = computed(() => page.props.auth?.user)
const userAvatar = computed(() => {
    if (user.value?.profile_photo_url) return user.value.profile_photo_url
    const initials = encodeURIComponent(user.value?.name || 'Usuario')
    return `https://ui-avatars.com/api/?name=${initials}&background=6366f1&color=fff&size=72`
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

function isCurrentRoute(href: string): boolean {
    return window.location.pathname === href
}

function navigate(href: string): void {
    router.visit(href)
}

function logout(): void {
    router.post('/logout')
}
</script>

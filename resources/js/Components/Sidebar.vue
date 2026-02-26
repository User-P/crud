<template>
    <aside
        class="flex h-screen flex-col border-r border-slate-200/60 bg-white shadow-lg shadow-slate-200/50 transition-[width] duration-300 ease-in-out"
        :class="effectiveExpanded ? 'w-64' : 'w-[4.5rem]'"
        @mouseenter="onMouseEnter"
        @mouseleave="onMouseLeave"
    >
        <!-- Logo + contraer/cerrar -->
        <div
            class="flex h-14 shrink-0 items-center border-b border-slate-100"
            :class="effectiveExpanded ? 'justify-between px-4' : 'justify-center px-0'"
        >
            <a
                v-if="effectiveExpanded"
                href="/dashboard"
                class="flex items-center gap-3 rounded-lg transition hover:opacity-90"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                    <Icon icon="heroicons:chart-bar-square" class="h-5 w-5" aria-hidden="true" />
                </div>
                <div class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-slate-900">Analytics</span>
                    <span class="block truncate text-[10px] font-medium uppercase tracking-wider text-slate-500">Panel</span>
                </div>
            </a>
            <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                <Icon icon="heroicons:chart-bar-square" class="h-5 w-5" aria-hidden="true" />
            </div>
            <div v-if="effectiveExpanded" class="flex items-center gap-0.5">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
                    aria-label="Contraer menú"
                    @click="$emit('toggle-collapse')"
                >
                    <Icon icon="heroicons:chevron-left" class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    title="Ocultar menú (usar toda la pantalla)"
                    aria-label="Ocultar menú"
                    @click="$emit('close')"
                >
                    <Icon icon="heroicons:x-mark" class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3">
            <ul role="list" class="space-y-1 px-2">
                <template v-for="group in navGroups" :key="group.label">
                    <li v-if="effectiveExpanded" class="px-3 pt-4 first:pt-0">
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
                                    :title="!effectiveExpanded ? item.name : undefined"
                                    :class="[
                                        isGroupActive(item)
                                            ? 'bg-indigo-50 text-indigo-700'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                        'flex w-full items-center gap-3 rounded-lg py-2.5 text-left text-sm font-medium transition',
                                        effectiveExpanded ? 'px-3' : 'justify-center px-2',
                                    ]"
                                    @click="!effectiveExpanded ? goFirstChild(item) : toggleExpand(item)"
                                >
                                    <Icon
                                        :icon="item.icon"
                                        class="h-5 w-5 shrink-0"
                                        :class="isGroupActive(item) ? 'text-indigo-600' : 'text-slate-500'"
                                        aria-hidden="true"
                                    />
                                    <span v-if="effectiveExpanded" class="truncate">{{ item.name }}</span>
                                    <Icon
                                        v-if="effectiveExpanded"
                                        icon="heroicons:chevron-down"
                                        class="ml-auto h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                        :class="{ 'rotate-180': expandedItems.includes(item.name) }"
                                        aria-hidden="true"
                                    />
                                </button>
                                <ul
                                    v-if="effectiveExpanded && expandedItems.includes(item.name)"
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
                                :title="!effectiveExpanded ? item.name : undefined"
                                :class="[
                                    isCurrentRoute(item.href)
                                        ? 'bg-indigo-50 text-indigo-700'
                                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                    'flex items-center gap-3 rounded-lg py-2.5 text-sm font-medium transition',
                                    effectiveExpanded ? 'px-3' : 'justify-center px-2',
                                ]"
                                @click.prevent="item.href && navigate(item.href)"
                            >
                                <Icon
                                    :icon="item.icon"
                                    class="h-5 w-5 shrink-0"
                                    :class="isCurrentRoute(item.href) ? 'text-indigo-600' : 'text-slate-500'"
                                    aria-hidden="true"
                                />
                                <span v-if="effectiveExpanded" class="truncate">{{ item.name }}</span>
                            </a>
                        </li>
                    </template>
                </template>
            </ul>
        </nav>

        <!-- Solo botón expandir cuando está contraído (cuenta y menú están en la barra superior) -->
        <div class="border-t border-slate-100 p-2">
            <div v-if="!effectiveExpanded" class="flex justify-center">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    title="Expandir menú"
                    aria-label="Expandir menú"
                    @click="$emit('toggle-collapse')"
                >
                    <Icon icon="heroicons:chevron-right" class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

interface NavChild {
    name: string
    href: string
}

interface NavigationItem {
    name: string
    href?: string
    icon: string
    children?: NavChild[]
}

interface NavGroup {
    label: string
    items: NavigationItem[]
}

const props = defineProps<{
    collapsed: boolean
}>()

const emit = defineEmits<{
    'toggle-collapse': []
    'close': []
    'hover-expand': [value: boolean]
    'collapse': []
}>()

const isHovered = ref(false)
const effectiveExpanded = computed(() => !props.collapsed || isHovered.value)

function onMouseEnter() {
    if (props.collapsed) {
        isHovered.value = true
        emit('hover-expand', true)
    }
}

function onMouseLeave() {
    isHovered.value = false
    emit('hover-expand', false)
}

const navGroups: NavGroup[] = [
    {
        label: 'Principal',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: 'heroicons:home' },
            { name: 'Chat', href: '/chat', icon: 'heroicons:chat-bubble-left-right' },
        ],
    },
    {
        label: 'Datos',
        items: [
            { name: 'Usuarios', href: '/users', icon: 'heroicons:users' },
            { name: 'Países', href: '/countries', icon: 'heroicons:folder' },
            { name: 'Eventos', href: '/events', icon: 'heroicons:calendar-days' },
        ],
    },
    {
        label: 'Análisis',
        items: [
            { name: 'Estadísticas', href: '/statistics', icon: 'heroicons:chart-bar' },
            {
                name: 'Dashboards de métricas',
                icon: 'heroicons:presentation-chart-bar',
                children: [
                    { name: 'Índice', href: '/dashboards' },
                    { name: 'Vista general', href: '/dashboards/vista-general' },
                    { name: 'Usuarios activos/inactivos', href: '/dashboards/usuarios-activos-inactivos' },
                    { name: 'Días suspendidos', href: '/dashboards/dias-suspendidos' },
                ],
            },
            { name: 'Tablas', href: '/tables', icon: 'heroicons:folder' },
            { name: 'Charts', href: '/charts', icon: 'heroicons:chart-pie' },
        ],
    },
    {
        label: 'Sistema',
        items: [
            { name: 'Configuración', href: '/settings', icon: 'heroicons:cog-6-tooth' },
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

function isCurrentRoute(href: string): boolean {
    return window.location.pathname === href
}

function navigate(href: string): void {
    router.visit(href)
    isHovered.value = false
    emit('hover-expand', false)
    emit('collapse')
}
</script>

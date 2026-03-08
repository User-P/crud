<template>
    <aside
        class="cosmos-sidebar flex h-screen flex-col transition-[width] duration-300 ease-in-out"
        :class="effectiveExpanded ? 'w-64' : 'w-[4.5rem]'"
        @mouseenter="onMouseEnter"
        @mouseleave="onMouseLeave"
    >
        <!-- Logo + contraer/cerrar -->
        <div
            class="flex h-14 shrink-0 items-center border-b border-white/[0.07]"
            :class="effectiveExpanded ? 'justify-between px-4' : 'justify-center px-0'"
        >
            <a
                v-if="effectiveExpanded"
                href="/dashboard"
                class="flex items-center gap-3 rounded-xl transition hover:opacity-80"
                @click.prevent="navigate('/dashboard')"
            >
                <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/30">
                    <Icon icon="heroicons:chart-bar-square" class="h-5 w-5" aria-hidden="true" />
                    <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#06060f] shadow shadow-emerald-400/60" />
                </div>
                <div class="min-w-0">
                    <span class="cosmos-logo-text block truncate text-sm font-semibold">Analytics</span>
                    <span class="cosmos-logo-sub block truncate text-[10px] font-medium uppercase tracking-widest">Panel</span>
                </div>
            </a>
            <div v-else class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/30">
                <Icon icon="heroicons:chart-bar-square" class="h-5 w-5" aria-hidden="true" />
                <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#06060f] shadow shadow-emerald-400/60" />
            </div>
            <div v-if="effectiveExpanded" class="flex items-center gap-0.5">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-white/[0.06] hover:text-slate-300"
                    :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
                    aria-label="Contraer menú"
                    @click="$emit('toggle-collapse')"
                >
                    <Icon icon="heroicons:chevron-left" class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-white/[0.06] hover:text-slate-300"
                    title="Ocultar menú"
                    aria-label="Ocultar menú"
                    @click="$emit('close')"
                >
                    <Icon icon="heroicons:x-mark" class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3">
            <ul role="list" class="space-y-0.5 px-2">
                <template v-for="group in navGroups" :key="group.label">
                    <!-- Separador de grupo -->
                    <li v-if="effectiveExpanded" class="px-3 pb-1 pt-5 first:pt-2">
                        <p class="cosmos-group-label flex items-center gap-2 text-[10px] font-semibold uppercase tracking-widest">
                            <span class="cosmos-group-line h-px flex-1" />
                            {{ group.label }}
                            <span class="cosmos-group-line h-px flex-1" />
                        </p>
                    </li>
                    <li v-else class="py-1.5">
                        <div class="cosmos-group-line mx-auto h-px w-8" />
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
                                            ? 'cosmos-item-active text-violet-300'
                                            : 'text-slate-400 hover:bg-white/[0.05] hover:text-slate-200',
                                        'flex w-full items-center gap-3 rounded-xl py-2.5 text-left text-sm font-medium transition duration-150',
                                        effectiveExpanded ? 'px-3' : 'justify-center px-2',
                                    ]"
                                    @click="!effectiveExpanded ? goFirstChild(item) : toggleExpand(item)"
                                >
                                    <Icon
                                        :icon="item.icon"
                                        class="h-[18px] w-[18px] shrink-0 transition-colors"
                                        :class="isGroupActive(item) ? 'text-violet-400' : 'text-slate-500'"
                                        aria-hidden="true"
                                    />
                                    <span v-if="effectiveExpanded" class="truncate">{{ item.name }}</span>
                                    <Icon
                                        v-if="effectiveExpanded"
                                        icon="heroicons:chevron-down"
                                        class="ml-auto h-4 w-4 shrink-0 text-slate-600 transition-transform duration-200"
                                        :class="{ 'rotate-180': expandedItems.includes(item.name) }"
                                        aria-hidden="true"
                                    />
                                </button>
                                <ul
                                    v-if="effectiveExpanded && expandedItems.includes(item.name)"
                                    class="mt-0.5 ml-5 space-y-0.5 border-l border-white/[0.07] pl-4"
                                    role="list"
                                >
                                    <li v-for="child in item.children" :key="child.href">
                                        <a
                                            :href="child.href"
                                            :class="[
                                                isCurrentRoute(child.href)
                                                    ? 'font-medium text-violet-400'
                                                    : 'text-slate-500 hover:text-slate-200',
                                                'block rounded-lg py-2 px-2 text-sm transition duration-150',
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
                                        ? 'cosmos-item-active text-violet-300'
                                        : 'text-slate-400 hover:bg-white/[0.05] hover:text-slate-200',
                                    'flex items-center gap-3 rounded-xl py-2.5 text-sm font-medium transition duration-150',
                                    effectiveExpanded ? 'px-3' : 'justify-center px-2',
                                ]"
                                @click.prevent="item.href && navigate(item.href)"
                            >
                                <Icon
                                    :icon="item.icon"
                                    class="h-[18px] w-[18px] shrink-0 transition-colors"
                                    :class="isCurrentRoute(item.href) ? 'text-violet-400' : 'text-slate-500'"
                                    aria-hidden="true"
                                />
                                <span v-if="effectiveExpanded" class="truncate">{{ item.name }}</span>
                            </a>
                        </li>
                    </template>
                </template>
            </ul>
        </nav>

        <!-- Footer / expandir cuando está contraído -->
        <div class="border-t border-white/[0.07] p-2">
            <div v-if="!effectiveExpanded" class="flex justify-center">
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-white/[0.06] hover:text-slate-300"
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

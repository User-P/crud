<template>
    <AdminLayout title="Dashboards de métricas" subtitle="Elige un tema o una unidad de negocio" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Dashboards de métricas' },
    ]">
        <div class="space-y-12">

            <!-- ── Quick stats strip ── -->
            <section>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        v-for="stat in quickStats"
                        :key="stat.label"
                        class="group relative overflow-hidden rounded-2xl p-4"
                    >
                        <!-- Glass -->
                        <span
                            class="glass-panel absolute inset-0 rounded-2xl transition-all duration-300"
                            aria-hidden="true"
                        />
                        <!-- Colour dot -->
                        <span
                            class="absolute right-3 top-3 h-2 w-2 rounded-full"
                            :class="stat.dot"
                            aria-hidden="true"
                        />
                        <div class="relative z-10">
                            <p class="text-2xl font-bold tabular-nums tracking-tight text-[color:var(--th-text-primary)]">
                                {{ stat.value }}
                            </p>
                            <p class="mt-0.5 text-xs font-medium text-[color:var(--th-text-muted)]">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Vista consolidada CTA ── -->
            <section>
                <a
                    href="/dashboards/vista-consolidada"
                    class="group relative flex items-center justify-between overflow-hidden rounded-2xl px-6 py-5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[var(--p-focus-ring-color)] focus:ring-offset-2 focus:ring-offset-[var(--th-ring-offset)]"
                    @click.prevent="navigate('/dashboards/vista-consolidada')"
                >
                    <!-- Glass -->
                    <span
                        class="glass-panel absolute inset-0 rounded-2xl transition-all duration-300"
                        aria-hidden="true"
                    />
                    <!-- Gradient ring on hover -->
                    <span
                        class="pointer-events-none absolute -inset-px rounded-2xl opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        style="background: linear-gradient(135deg, rgba(11,66,97,0.25), rgba(91,181,106,0.15), transparent)"
                        aria-hidden="true"
                    />
                    <!-- Corner orb -->
                    <span
                        class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-[#0b4261]/20 blur-3xl opacity-0 transition-opacity duration-500 group-hover:opacity-100 dark:bg-[#5bb56a]/15"
                        aria-hidden="true"
                    />

                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0b4261]/12 dark:bg-[#5bb56a]/20">
                            <Icon
                                icon="heroicons:squares-2x2"
                                class="h-5 w-5 text-[#0b4261] dark:text-[#5bb56a]"
                                aria-hidden="true"
                            />
                        </div>
                        <div>
                            <p class="font-semibold text-[color:var(--th-text-primary)]">Vista consolidada</p>
                            <p class="text-sm text-[color:var(--th-text-secondary)]">Todos los indicadores en una sola pantalla</p>
                        </div>
                    </div>

                    <div class="relative z-10 flex items-center gap-1.5 text-[color:var(--th-item-active-color)]">
                        <span class="text-sm font-semibold">Abrir</span>
                        <Icon
                            icon="heroicons:arrow-right"
                            class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </div>
                </a>
            </section>

            <!-- ── Bento: por tema ── -->
            <section>
                <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-[color:var(--th-group-label)]">
                    Explorar por tema
                </p>
                <DashboardBentoGrid :cards="dashboardCards" mode="navigate" @navigate="navigate" />
            </section>

            <!-- ── Unidades: strip horizontal ── -->
            <section>
                <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-[color:var(--th-group-label)]">
                    Por unidad de negocio
                </p>
                <div class="flex flex-wrap gap-3">
                    <a
                        v-for="card in byUnits"
                        :key="card.name"
                        :href="card.href"
                        class="unit-pill glass-panel group flex items-center gap-3 rounded-2xl px-5 py-3.5 transition-all duration-300 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[var(--p-focus-ring-color)] focus:ring-offset-2 focus:ring-offset-[var(--th-ring-offset)]"
                        @click.prevent="navigate(card.href)"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                            :class="card.iconBg"
                        >
                            <Icon :icon="card.icon" :class="card.iconColor" class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <span class="block truncate font-semibold text-[color:var(--th-text-primary)]">{{ card.name }}</span>
                            <span class="text-xs text-[color:var(--th-text-muted)]">{{ card.badge }}</span>
                        </div>
                        <Icon
                            icon="heroicons:arrow-right"
                            class="h-4 w-4 shrink-0 text-[color:var(--th-item-active-color)] transition-transform group-hover:translate-x-0.5"
                            aria-hidden="true"
                        />
                    </a>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useUsers } from './composables/useUsers'
import DashboardBentoGrid from './components/DashboardBentoGrid.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const navigate = (href: string) => router.visit(href)

const { dashboardCards, byUnits } = useUsers()

const quickStats = [
    { value: '4',    label: 'Dashboards disponibles',   dot: 'bg-[#0b4261] dark:bg-[#5bb56a]' },
    { value: '4',    label: 'Unidades de negocio',       dot: 'bg-emerald-500 dark:bg-emerald-400' },
    { value: '142K', label: 'Usuarios registrados',      dot: 'bg-blue-500 dark:bg-blue-400' },
    { value: 'Hoy',  label: 'Última sincronización',     dot: 'bg-amber-500 dark:bg-amber-400' },
]
</script>

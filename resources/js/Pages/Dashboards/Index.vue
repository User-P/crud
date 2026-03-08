<template>
    <AdminLayout title="Dashboards de métricas" subtitle="Elige un tema o una unidad de negocio" :breadcrumbs="[
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Dashboards de métricas' },
    ]">

        <div class="space-y-12">
            <!-- Acceso a vista consolidada -->
            <a
                href="/dashboards/vista-consolidada"
                class="inline-flex items-center gap-2 rounded-xl border border-(--th-border) bg-(--th-input-bg) px-4 py-2.5 text-sm font-medium text-(--th-text-primary) shadow-sm transition-colors hover:bg-(--th-item-hover-bg) hover:text-(--th-item-active-color) focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset)"
                @click.prevent="navigate('/dashboards/vista-consolidada')"
            >
                <Icon icon="heroicons:squares-2x2" class="h-5 w-5" aria-hidden="true" />
                Ver todo en una sola vista
            </a>

            <!-- Bento: por tema -->
            <section >
                <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                    Explorar por tema
                </p>
                <DashboardBentoGrid :cards="dashboardCards" mode="navigate" @navigate="navigate" />
            </section>

            <!-- Unidades: strip horizontal -->
            <section>
                <p class="mb-5 text-xs font-semibold uppercase tracking-widest text-(--th-group-label)">
                    Por unidad de negocio
                </p>
                <div class="flex flex-wrap gap-3">
                    <a
                        v-for="card in byUnits"
                        :key="card.name"
                        :href="card.href"
                        class="unit-pill group flex items-center gap-3 rounded-2xl border border-white/20 bg-white/70 px-5 py-3.5 shadow-md backdrop-blur-xl transition-all duration-300 hover:-translate-y-0.5 hover:border-white/30 hover:bg-white/90 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-(--p-focus-ring-color) focus:ring-offset-2 focus:ring-offset-(--th-ring-offset) dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                        @click.prevent="navigate(card.href)"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                            :class="card.iconBg"
                        >
                            <Icon :icon="card.icon" :class="card.iconColor" class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <span class="block truncate font-semibold text-(--th-text-primary)">{{ card.name }}</span>
                            <span class="text-xs text-(--th-text-muted)">{{ card.badge }}</span>
                        </div>
                        <Icon
                            icon="heroicons:arrow-right"
                            class="h-4 w-4 shrink-0 text-(--th-item-active-color) transition-transform group-hover:translate-x-0.5"
                            aria-hidden="true"
                        />
                    </a>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useUsers } from './composables/useUsers'
import DashboardBentoGrid from './components/DashboardBentoGrid.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const navigate = (href: string) => {
    router.visit(href)
}

const { getResumen, dashboardCards, byUnits } = useUsers()

onMounted(() => {
    getResumen()
})
</script>

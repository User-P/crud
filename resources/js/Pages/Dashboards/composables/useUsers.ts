import { ref } from "vue"

/** Datos dummy para visualizar la interfaz sin depender de la API. */

// Respuesta de /resumen (no se usa en template actualmente)
const DUMMY_RESUMEN = { ok: true, message: "Datos de demostración" }

// Respuesta de /cards/:date (VistaGeneral: hero, primary, secondary)
const DUMMY_USERS_CARDS = {
    primary: [
        {
            id: "total",
            label: "Usuarios totales",
            value: 142000,
            variant: "blue",
            iconKey: "heroicons:globe-alt",
        },
        { id: "activos", label: "Usuarios activos", value: 132000, variant: "green", iconKey: "heroicons:check-circle" },
        { id: "inactivos", label: "Usuarios inactivos", value: 10000, variant: "red", iconKey: "heroicons:x-circle" },
    ],
    secondary: [
        { id: "locked", label: "Bloqueados", value: 120, variant: "red", iconKey: "heroicons:no-symbol" },
        { id: "password", label: "Password expirado", value: 85, variant: "red", iconKey: "heroicons:lock-open" },
        { id: "provisioned", label: "Provisionados", value: 131500, variant: "red", iconKey: "heroicons:pause-circle" },
        { id: "suspendidos", label: "Suspendidos", value: 2700, variant: "red", iconKey: "heroicons:user-minus" },
        { id: "desactivados", label: "Desactivados", value: 605, variant: "red", iconKey: "heroicons:minus-circle" },
    ],
}

// Respuesta de /charts/:date
const DUMMY_CHARTS = {
    cards: [
        { id: "total", label: "Usuarios totales", value: 142000, variant: "blue", iconKey: "heroicons:globe-alt" },
        { id: "activos", label: "Usuarios activos", value: 132000, variant: "green", iconKey: "heroicons:check-circle" },
        { id: "inactivos", label: "Usuarios inactivos", value: 10000, variant: "red", iconKey: "heroicons:x-circle" },
    ],
    charts: {
        categories: ["Bloqueados", "Contraseña expirado", "Provisionado", "Suspendidos", "Desactivados"],
        pie: [
            { name: "Activos", value: 132000 },
            { name: "Inactivos", value: 10000 },
        ],
        semaphore: {
            labels: ["Bloqueados", "Contraseña expirado", "Provisionado", "Suspendidos", "Desactivados"],
            values: [120, 85, 131500, 2700, 605],
        },
    },
}

// Respuesta de /suspended/:date (Días suspendidos)
const DUMMY_SUSPENDED = {
    status: "success",
    chart: {
        labels: ["1-3 días", "4-6 días", "7+ días"],
        values: [600, 900, 1200],
    },
    groups: {
        MENOR: { label: "MENOR", count: 600, details: [] },
        MODERADO: { label: "MODERADO", count: 900, details: [] },
        ELEVADO: { label: "ELEVADO", count: 1200, details: [] },
    },
}

// Respuesta de /users-add/:date (Usuarios nuevos)
const DUMMY_USERS_ADD = {
    cards: [
        { id: "total_alta", label: "Altas totales de usuarios.", value: 18500, variant: "blue", iconKey: "heroicons:users" },
        { id: "mes_alta", label: "Altas del mes", value: 420, variant: "yellow", iconKey: "heroicons:calendar-days" },
        { id: "dia_alta", label: "Altas del día", value: 18, variant: "green", iconKey: "heroicons:calendar-date-range" },
    ],
    line: {
        labels: ["Ene 2025", "Feb 2025", "Mar 2025", "Abr 2025", "May 2025", "Jun 2025"],
        series: [{ name: "Usuarios Creados", data: [1200, 1450, 1320, 1580, 1420, 1530] }],
    },
    bar: {
        labels: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
        series: [{ name: "Altas Diarias", data: [12, 19, 15, 22, 18, 8, 5] }],
    },
}

// Respuesta de /details (detalle por tipo)
const DUMMY_DETAILS = [
    { concepto: "Total", valor: 132000 },
    { concepto: "PROVISIONED", valor: 131500 },
    { concepto: "PASSWORD_EXPIRED", valor: 85 },
    { concepto: "LOCKED", valor: 120 },
]

/** Detalle por métrica para la DataTable (tabla inline en VistaGeneral) */
export type DetailTableRow = { concepto: string; valor: number | string; porcentaje?: string; unidad?: string; actualizado?: string }

const DUMMY_DETAILS_BY_CARD: Record<string, DetailTableRow[]> = {
    total: [
        { concepto: "Total", valor: 142000, porcentaje: "100%", actualizado: "2025-01-31 08:00" },
        { concepto: "PROVISIONED", valor: 131500, porcentaje: "92,6%", actualizado: "2025-01-31 08:00" },
        { concepto: "PASSWORD_EXPIRED", valor: 85, porcentaje: "0,1%", actualizado: "2025-01-31 07:45" },
        { concepto: "LOCKED", valor: 120, porcentaje: "0,1%", actualizado: "2025-01-31 07:50" },
        { concepto: "Suspendidos", valor: 2700, porcentaje: "1,9%", actualizado: "2025-01-31 08:00" },
        { concepto: "Desactivados", valor: 605, porcentaje: "0,4%", actualizado: "2025-01-30 23:00" },
    ],
    activos: [
        { concepto: "EKT", valor: 45200, unidad: "usuarios", actualizado: "2025-01-31 08:00" },
        { concepto: "TPE", valor: 38100, unidad: "usuarios", actualizado: "2025-01-31 08:00" },
        { concepto: "TVA", valor: 28900, unidad: "usuarios", actualizado: "2025-01-31 08:00" },
        { concepto: "BACK OFFICE", valor: 19800, unidad: "usuarios", actualizado: "2025-01-31 08:00" },
    ],
    inactivos: [
        { concepto: "Por contraseña expirada", valor: 85, actualizado: "2025-01-31 07:45" },
        { concepto: "Por bloqueo", valor: 120, actualizado: "2025-01-31 07:50" },
        { concepto: "Provisionados (pendientes)", valor: 131500, actualizado: "2025-01-31 08:00" },
        { concepto: "Suspendidos", valor: 2700, actualizado: "2025-01-31 08:00" },
        { concepto: "Desactivados", valor: 605, actualizado: "2025-01-30 23:00" },
    ],
    locked: [
        { concepto: "EKT", valor: 42, actualizado: "2025-01-31 08:00" },
        { concepto: "TPE", valor: 28, actualizado: "2025-01-31 08:00" },
        { concepto: "TVA", valor: 35, actualizado: "2025-01-31 08:00" },
        { concepto: "BACK OFFICE", valor: 15, actualizado: "2025-01-31 08:00" },
    ],
    password: [
        { concepto: "Últimas 24 h", valor: 12, actualizado: "2025-01-31 08:00" },
        { concepto: "Últimos 7 días", valor: 45, actualizado: "2025-01-31 08:00" },
        { concepto: "Últimos 30 días", valor: 85, actualizado: "2025-01-31 08:00" },
    ],
    provisioned: [
        { concepto: "EKT", valor: 39800, actualizado: "2025-01-31 08:00" },
        { concepto: "TPE", valor: 32500, actualizado: "2025-01-31 08:00" },
        { concepto: "TVA", valor: 29200, actualizado: "2025-01-31 08:00" },
        { concepto: "BACK OFFICE", valor: 30000, actualizado: "2025-01-31 08:00" },
    ],
    suspendidos: [
        { concepto: "1-3 días", valor: 600, actualizado: "2025-01-31 08:00" },
        { concepto: "4-6 días", valor: 900, actualizado: "2025-01-31 08:00" },
        { concepto: "7+ días", valor: 1200, actualizado: "2025-01-31 08:00" },
    ],
    desactivados: [
        { concepto: "Enero 2025", valor: 205, actualizado: "2025-01-31 08:00" },
        { concepto: "Diciembre 2024", valor: 180, actualizado: "2024-12-31 23:59" },
        { concepto: "Noviembre 2024", valor: 220, actualizado: "2024-11-30 23:59" },
    ],
}

// Respuesta de /users-add-details
const DUMMY_USERS_ADD_DETAILS = [
    { mes_referencia: "2025-01", total: 420 },
    { mes_referencia: "2025-02", total: 380 },
    { dia_alta: "2025-02-26", total_alta: 18 },
]

export const useUsers = () => {
    const resumen = ref<typeof DUMMY_RESUMEN | undefined>()
    const users = ref<typeof DUMMY_USERS_CARDS | undefined>()
    const charts = ref<typeof DUMMY_CHARTS | undefined>()
    const categories = ref<typeof DUMMY_CHARTS.charts | undefined>()
    const suspended = ref<typeof DUMMY_SUSPENDED | undefined>()
    const details = ref<typeof DUMMY_DETAILS | undefined>()
    const isLoading = ref(false)

    const usersAdd = ref<typeof DUMMY_USERS_ADD | undefined>()
    const usersAddDetails = ref<typeof DUMMY_USERS_ADD_DETAILS | undefined>()

    const dashboardCards = [
        {
            name: "Vista general",
            description: "KPIs principales con tendencias, sparklines y drill-down al detalle.",
            href: "/dashboards/vista-general",
            icon: "heroicons:presentation-chart-bar",
            iconBg: "bg-indigo-100 dark:bg-indigo-400/25",
            iconColor: "text-indigo-600 dark:text-indigo-400",
            accentBar: "bg-indigo-500 dark:bg-indigo-400",
            badge: "KPIs",
        },
        {
            name: "Usuarios activos / inactivos",
            description: "Distribución y estatus con gráficos interactivos.",
            href: "/dashboards/usuarios-activos-inactivos",
            icon: "heroicons:user-group",
            iconBg: "bg-emerald-100 dark:bg-emerald-400/25",
            iconColor: "text-emerald-600 dark:text-emerald-400",
            accentBar: "bg-emerald-500 dark:bg-emerald-400",
            badge: "Gráficos",
        },
        {
            name: "Días suspendidos",
            description: "Semáforo de riesgo por tiempo de suspensión.",
            href: "/dashboards/dias-suspendidos",
            icon: "heroicons:exclamation-triangle",
            iconBg: "bg-amber-100 dark:bg-amber-400/25",
            iconColor: "text-amber-600 dark:text-amber-400",
            accentBar: "bg-amber-500 dark:bg-amber-400",
            badge: "Riesgo",
        },
        {
            name: "Usuarios nuevos",
            description: "Alta de usuarios",
            href: "/dashboards/usuarios-nuevos",
            icon: "heroicons:user-plus",
            iconBg: "bg-blue-100 dark:bg-blue-400/25",
            iconColor: "text-blue-600 dark:text-blue-400",
            accentBar: "bg-blue-500 dark:bg-blue-400",
            badge: "Riesgo",
        },
    ]

    const byUnits = [
        { name: "Usuarios de EKT", description: "Vista general filtrada por unidad de negocio EKT", href: "/dashboards/vista-general?unit=EKT", icon: "streamline-plump:user-pin", iconBg: "bg-indigo-100 dark:bg-indigo-400/25", iconColor: "text-indigo-600 dark:text-indigo-400", accentBar: "bg-indigo-500 dark:bg-indigo-400", badge: "EKT" },
        { name: "Usuarios de TPE", description: "Vista general filtrada por unidad de negocio TPE", href: "/dashboards/vista-general?unit=TPE", icon: "stash:user-id", iconBg: "bg-emerald-100 dark:bg-emerald-400/25", iconColor: "text-emerald-600 dark:text-emerald-400", accentBar: "bg-emerald-500 dark:bg-emerald-400", badge: "TPE" },
        { name: "Usuarios de TVA", description: "Vista general filtrada por unidad de negocio TVA", href: "/dashboards/vista-general?unit=TVA", icon: "hugeicons:ai-user", iconBg: "bg-amber-100 dark:bg-amber-400/25", iconColor: "text-amber-600 dark:text-amber-400", accentBar: "bg-amber-500 dark:bg-amber-400", badge: "TVA" },
        { name: "Usuarios de BACK OFFICE", description: "Vista general filtrada por unidad de negocio BACK OFFICE", href: "/dashboards/vista-general?unit=BACK_OFFICE", icon: "line-md:account", iconBg: "bg-blue-100 dark:bg-blue-400/25", iconColor: "text-blue-600 dark:text-blue-400", accentBar: "bg-blue-500 dark:bg-blue-400", badge: "BACK OFFICE" },
    ]

    const getResumen = async () => {
        resumen.value = DUMMY_RESUMEN
    }

    const getIndicadores = async (_date: string, _unit?: string) => {
        users.value = DUMMY_USERS_CARDS
    }

    const getCharts = async (_date: string, _unit?: string) => {
        charts.value = DUMMY_CHARTS
        categories.value = DUMMY_CHARTS.charts
    }

    const getSuspended = async (_date: string, _unit?: string) => {
        suspended.value = DUMMY_SUSPENDED
    }

    const getUsersAdd = async (_date: string, _unit?: string) => {
        usersAdd.value = DUMMY_USERS_ADD
    }

    const getDetails = async (_type: string, _date: string, _unit?: string) => {
        isLoading.value = true
        details.value = DUMMY_DETAILS
        isLoading.value = false
    }

    const getUsersAddDetails = async (_type: string, _date: string, _unit?: string) => {
        isLoading.value = true
        usersAddDetails.value = DUMMY_USERS_ADD_DETAILS
        isLoading.value = false
    }

    return {
        isLoading,
        resumen,
        users,
        charts,
        categories,
        suspended,
        details,
        detailsByCard: DUMMY_DETAILS_BY_CARD,
        usersAdd,
        usersAddDetails,
        dashboardCards,
        byUnits,
        getUsersAddDetails,
        getUsersAdd,
        getDetails,
        getCharts,
        getIndicadores,
        getResumen,
        getSuspended,
    }
}

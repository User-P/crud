import axios from "axios"
import { ref } from "vue"

const API_BASE = '/dashboard_cyaal'

export const useUsers = () => {
    const resumen = ref()
    const users = ref()
    const charts = ref()
    const categories = ref()
    const suspended = ref()
    const details = ref()
    const isLoading = ref(false)

    const usersAdd = ref()
    const usersAddDetails = ref()

    const dashboardCards = [
        {
            name: 'Vista general',
            description:
                'KPIs principales con tendencias, sparklines y drill-down al detalle.',
            href: 'dashboards/vista-general',
            icon: 'heroicons:presentation-chart-bar',
            iconBg: 'bg-indigo-100',
            iconColor: 'text-indigo-600',
            badge: 'KPIs',
        },
        {
            name: 'Usuarios activos / inactivos',
            description: 'Distribución y estatus con gráficos interactivos.',
            href: 'dashboard_cyaal/usuarios-activos-inactivos',
            icon: 'heroicons:user-group',
            iconBg: 'bg-emerald-100',
            iconColor: 'text-emerald-600',
            badge: 'Gráficos',
        },
        {
            name: 'Días suspendidos',
            description: 'Semáforo de riesgo por tiempo de suspensión.',
            href: 'dashboard_cyaal/dias-suspendidos',
            icon: 'heroicons:exclamation-triangle',
            iconBg: 'bg-amber-100',
            iconColor: 'text-amber-600',
            badge: 'Riesgo',
        },
        {
            name: 'Usuarios nuevos',
            description: 'Alta de usuarios',
            href: 'dashboard_cyaal/usuarios-nuevos',
            icon: 'heroicons:user-plus',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            badge: 'Riesgo',
        },
    ];

    // Misma vista general con filtro por unidad (?unit=XXX)
    const byUnits = [
        {
            name: 'Usuarios de EKT',
            description: 'Vista general filtrada por unidad de negocio EKT',
            href: '/dashboards/vista-general?unit=EKT',
            icon: 'streamline-plump:user-pin',
            iconBg: 'bg-indigo-100',
            iconColor: 'text-indigo-600',
            badge: 'EKT',
        },
        {
            name: 'Usuarios de TPE',
            description: 'Vista general filtrada por unidad de negocio TPE',
            href: '/dashboards/vista-general?unit=TPE',
            icon: 'stash:user-id',
            iconBg: 'bg-emerald-100',
            iconColor: 'text-emerald-600',
            badge: 'TPE',
        },
        {
            name: 'Usuarios de TVA',
            description: 'Vista general filtrada por unidad de negocio TVA',
            href: '/dashboards/vista-general?unit=TVA',
            icon: 'hugeicons:ai-user',
            iconBg: 'bg-amber-100',
            iconColor: 'text-amber-600',
            badge: 'TVA',
        },
        {
            name: 'Usuarios de BACK OFFICE',
            description: 'Vista general filtrada por unidad de negocio BACK OFFICE',
            href: '/dashboards/vista-general?unit=BACK_OFFICE',
            icon: 'line-md:account',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            badge: 'BACK OFFICE',
        },
    ];

    const getResumen = async () => {
        try {
            const { data } = await axios.get(`${API_BASE}/resumen`)
            resumen.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getIndicadores = async (date: string, unit?: string) => {
        try {
            const params = unit ? { unit } : {}
            const { data } = await axios.get(`${API_BASE}/users/cards/${date}`, { params })
            users.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getCharts = async (date: string, unit?: string) => {
        try {
            const params = unit ? { unit } : {}
            const { data } = await axios.get(`${API_BASE}/users/charts/${date}`, { params })
            charts.value = data
            categories.value = data.charts?.categories
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getSuspended = async (date: string, unit?: string) => {
        try {
            const params = unit ? { unit } : {}
            const { data } = await axios.get(`${API_BASE}/users/suspended/${date}`, { params })
            suspended.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getUsersAdd = async (date: string, unit?: string) => {
        try {
            const params = unit ? { unit } : {}
            const { data } = await axios.get(`${API_BASE}/users/users-add/${date}`, { params })
            usersAdd.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getDetails = async (type: string, date: string, unit?: string) => {
        try {
            isLoading.value = true
            const { data } = await axios.post(`${API_BASE}/users/details`, {
                type,
                date,
                ...(unit && { unit }),
            })
            details.value = data
        } catch (error) {
            console.log('error al cargar')
        } finally {
            isLoading.value = false
        }
    }

    const getUsersAddDetails = async (type: string, date: string, unit?: string) => {
        try {
            isLoading.value = true
            const { data } = await axios.post(`${API_BASE}/users/users-add-details`, {
                type,
                date,
                ...(unit && { unit }),
            })
            usersAddDetails.value = data
        } catch (error) {
            console.log('error al cargar')
        } finally {
            isLoading.value = false
        }
    }

    return {
        isLoading,
        resumen,
        users,
        charts,
        categories,
        suspended,
        details,
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
        getSuspended
    }
}

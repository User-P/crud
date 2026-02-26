import axios from "axios"
import { ref } from "vue"

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

    const byUnits = [
        {
            name: 'Visualizacion por unidad de negocio.',
            description: 'Unidad de negocios EKT',
            href: '/dashboard_cyaal/grafica-por-unidad',
            icon: 'heroicons:chart-bar',
            iconBg: 'bg-indigo-100',
            iconColor: 'text-indigo-600',
            badge: 'KPIs',
        },
        {
            name: 'Usuarios de EKT.',
            description: 'Unidad de negocios EKT',
            href: '/dashboard_cyaal/usuarios-ekt',
            icon: 'streamline-plump:user-pin',
            iconBg: 'bg-indigo-100',
            iconColor: 'text-indigo-600',
            badge: 'KPIs',
        },
        {
            name: 'Usuarios de TPE.',
            description: 'Unidad de negocios TPE.',
            href: '/dashboard_cyaal/usuarios-tpe',
            icon: 'stash:user-id',
            iconBg: 'bg-emerald-100',
            iconColor: 'text-emerald-600',
            badge: 'Gráficos',
        },
        {
            name: 'Usuarios de TVA.',
            description: 'Unidad de negocios TVA.',
            href: '/dashboard_cyaal/usuarios-tva',
            icon: 'hugeicons:ai-user',
            iconBg: 'bg-amber-100',
            iconColor: 'text-amber-600',
            badge: 'Riesgo',
        },
        {
            name: 'Usuarios de BACK OFFICE.',
            description: 'Unidad de negocio de BACK OFFICE',
            href: '/dashboard_cyaal/usuarios-back-office',
            icon: 'line-md:account',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            badge: 'Riesgo',
        },
        {
            name: 'Estatus de usuarios por país.',
            description: 'Unidad de negocio de BACK OFFICE',
            href: '/dashboard_cyaal/por-pais',
            icon: 'line-md:map-marker-loop',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600',
            badge: 'Riesgo',
        },
    ];

    const getResumen = async () => {
        try {
            const { data } = await axios.get('dashboard_cyaal/resumen')
            resumen.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getIndicadores = async (date: string) => {
        try {
            const { data } = await axios.get(`users/cards/${date}`)
            users.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getCharts = async (date: string) => {
        try {
            const { data } = await axios.get(`users/charts/${date}`)
            charts.value = data
            categories.value = data.charts.categories
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getSuspended = async (date: string) => {
        try {
            const { data } = await axios.get(`users/suspended/${date}`)
            suspended.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getUsersAdd = async (date: string) => {
        try {
            const { data } = await axios.get(`users/users-add/${date}`)
            usersAdd.value = data
        } catch (error) {
            console.log('error al cargar')
        }
    }

    const getDetails = async (type: string, date: string) => {
        try {
            isLoading.value = true
            const { data } = await axios.post(`users/details/`, {
                type,
                date
            })
            details.value = data
        } catch (error) {
            console.log('error al cargar')
        } finally {
            isLoading.value = false
        }
    }

    const getUsersAddDetails = async (type: string, date: string) => {
        try {
            isLoading.value = true
            const { data } = await axios.post(`users/users-add-details/`, {
                type,
                date
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

import type { DiagramNodeData, RowDefinition } from '../types'

export type DiagramApiResponse = {
    header: DiagramNodeData
    general: DiagramNodeData
    infinite: DiagramNodeData
    score: DiagramNodeData
    sidebar: DiagramNodeData
    rows: RowDefinition[]
}

const baseRows: RowDefinition[] = [
    {
        key: 'tecleo',
        model: { variant: 'model', title: 'Tabla de Tecleo Alnova', badge: 'COMPLETADO' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '54,123',
            description: '20,009 prom del 15 al 19'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Tabla de Tecleo financiero | Plantilla'
        },
        outcome: {
            variant: 'detail',
            title: 'Transacciones',
            value: '3714 Total / 59 modelo'
        }
    },
    {
        key: 'empleo',
        model: { variant: 'model', title: 'Búsqueda de Empleo', badge: 'COMPLETADO' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '26,781 / 6,131'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Mónaco | Teramind | Accesos Físicos | Log Exchange | Plantilla | DLP | Teams'
        },
        outcome: {
            variant: 'detail',
            title: 'Preguntas Clave',
            value: '16'
        }
    },
    {
        key: 'sdt',
        model: { variant: 'model', title: 'SDT', badge: 'En Proceso', badgeTone: 'blue' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '- / -'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Mónaco | Teramind | Accesos Físicos | Log Exchange | Plantilla | DLP | Teams'
        },
        outcome: {
            variant: 'detail',
            title: 'Preguntas Clave',
            value: '2'
        }
    },
    {
        key: 'banca',
        model: { variant: 'model', title: 'Banca Digital', badge: 'En Proceso', badgeTone: 'blue' },
        users: {
            variant: 'detail',
            title: 'Usuarios Monitoreados',
            value: '- / -'
        },
        sources: {
            variant: 'detail',
            title: 'Fuentes',
            description: 'Flujos seleccionados de Banca Digital'
        },
        outcome: {
            variant: 'detail',
            title: 'Flujos',
            value: '4'
        }
    }
]

export const defaultDiagramData: DiagramApiResponse = {
    header: { variant: 'header', title: 'Datalake i3', subtitle: 'Modelos de Información' },
    general: {
        variant: 'primary',
        title: 'GENERAL',
        subtitle: 'Usuarios Monitoreados',
        value: '32,795 / 1,425',
        description: 'Prom del 15 al 19',
        footer: 'Plantilla | DLP | CASB | Cyberark | Colas Impresión<br/>Políticas DLP – 10 | Reglas DLP – 27'
    },
    infinite: {
        variant: 'primary',
        title: 'INFINITE',
        subtitle: 'Usuarios Monitoreados',
        value: '173 / 4',
        description: 'Prom del 15 al 19',
        footer: 'Modelo General<br/>Políticas DLP – 3 | Reglas DLP – 3'
    },
    score: {
        variant: 'model',
        title: 'Score Infractores DLP',
        badge: 'COMPLETADO'
    },
    sidebar: {
        variant: 'sidebar',
        title: 'MODELOS DE ML'
    },
    rows: baseRows
}

export async function fetchDiagramData(): Promise<DiagramApiResponse> {
    // Simula un fetch. En producción reemplaza con axios/fetch al backend.
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve(JSON.parse(JSON.stringify(defaultDiagramData)) as DiagramApiResponse)
        }, 150)
    })
}

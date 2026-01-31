/**
 * Utilidades para filtros de rango de fechas.
 * Usamos siempre strings YYYY-MM-DD para evitar problemas de zona horaria.
 */

export type DateRangeValue = { from?: string; to?: string }

/** Convierte cualquier valor a Date o undefined */
export function parseDate(input: string | Date | number | null | undefined): Date | undefined {
    if (input === undefined || input === null || input === '') return undefined
    if (input instanceof Date) return Number.isNaN(input.getTime()) ? undefined : input
    if (typeof input === 'number') {
        const d = new Date(input)
        return Number.isNaN(d.getTime()) ? undefined : d
    }
    if (typeof input === 'string') {
        const match = input.match(/^(\d{4})-(\d{2})-(\d{2})/)
        if (match) {
            const [, y, m, d] = match
            return new Date(Number(y), Number(m) - 1, Number(d))
        }
        const d = new Date(input)
        return Number.isNaN(d.getTime()) ? undefined : d
    }
    return undefined
}

/** Fecha solo día (local), sin hora, para comparaciones */
export function toDateOnly(input: string | Date | number | null | undefined): Date | undefined {
    const d = parseDate(input)
    if (!d) return undefined
    return new Date(d.getFullYear(), d.getMonth(), d.getDate())
}

/** Formatea Date a string YYYY-MM-DD (local) */
export function toYYYYMMDD(d: Date): string {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

/** Parsea string YYYY-MM-DD a Date local */
export function fromYYYYMMDD(s: string | undefined | null): Date | undefined {
    if (!s || typeof s !== 'string') return undefined
    const d = parseDate(s)
    return d ? toDateOnly(d) : undefined
}

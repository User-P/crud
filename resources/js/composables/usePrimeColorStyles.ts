import { computed, type ComputedRef } from 'vue'

type ColorInput = string | ComputedRef<string>

function normalizeColor(input: ColorInput): ComputedRef<string> {
    return computed(() => {
        const raw = typeof input === 'string' ? input : input.value
        return String(raw || 'blue').trim()
    })
}

function mix(base: string, percent: number) {
    return `color-mix(in srgb, ${base} ${percent}%, transparent)`
}

export function usePrimeColorStyles(color: ColorInput) {
    const colorName = normalizeColor(color)
    const base = computed(() => `var(--p-${colorName.value}-400)`)

    const iconStyle = computed(() => ({ color: base.value }))
    const dotStyle = computed(() => ({ backgroundColor: base.value }))
    const badgeStyle = computed(() => ({
        backgroundColor: mix(base.value, 12),
        borderColor: mix(base.value, 25),
    }))

    const barStyle = computed(() => ({ backgroundColor: base.value }))
    const blobStyle = computed(() => ({ backgroundColor: base.value }))

    // Glow suave sin depender de 2 colores (solo del base)
    const gradientRingStyle = computed(() => ({
        background: `linear-gradient(135deg, ${mix(base.value, 40)}, ${mix(base.value, 20)}, transparent)`,
    }))

    // Útil para animaciones tipo "ping"
    const pingStyle = computed(() => ({ backgroundColor: mix(base.value, 60) }))

    return {
        colorName,
        base,
        iconStyle,
        dotStyle,
        badgeStyle,
        barStyle,
        blobStyle,
        gradientRingStyle,
        pingStyle,
    }
}


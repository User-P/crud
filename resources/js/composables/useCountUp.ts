import { ref, watch, onMounted, onUnmounted } from 'vue'

/**
 * Animated counter that transitions smoothly to a target value.
 * Uses ease-out cubic easing for a natural deceleration feel.
 * Automatically re-animates when the target changes.
 *
 * @param getValue  Reactive getter for the target number
 * @param duration  Animation duration in ms (default 1300)
 */
export function useCountUp(getValue: () => number, duration = 1300) {
    const displayed = ref(0)
    let raf = 0

    function animate(from: number, to: number, startTime: number) {
        const elapsed = performance.now() - startTime
        const t = Math.min(elapsed / duration, 1)
        const eased = 1 - (1 - t) ** 3          // ease-out cubic
        displayed.value = Math.round(from + (to - from) * eased)
        if (t < 1) raf = requestAnimationFrame(() => animate(from, to, startTime))
    }

    function run() {
        cancelAnimationFrame(raf)
        const to = getValue()
        const from = displayed.value
        animate(from, to, performance.now())
    }

    onMounted(run)
    watch(getValue, run)
    onUnmounted(() => cancelAnimationFrame(raf))

    return { displayed }
}

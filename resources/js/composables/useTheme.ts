import { ref } from 'vue'

export type ThemeMode = 'light' | 'dark' | 'system'

const mode = ref<ThemeMode>('system')

function getSystemTheme(): 'light' | 'dark' {
    if (typeof window === 'undefined') return 'light'
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(m: ThemeMode) {
    const resolved = m === 'system' ? getSystemTheme() : m
    document.documentElement.setAttribute('data-theme', resolved)
}

export function useTheme() {
    function init() {
        const saved = (localStorage.getItem('admin-theme') ?? 'system') as ThemeMode
        mode.value = saved
        applyTheme(saved)
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (mode.value === 'system') applyTheme('system')
        })
    }

    function cycleTheme() {
        const next: Record<ThemeMode, ThemeMode> = { light: 'dark', dark: 'system', system: 'light' }
        setMode(next[mode.value])
    }

    function setMode(m: ThemeMode) {
        mode.value = m
        localStorage.setItem('admin-theme', m)
        applyTheme(m)
    }

    return { mode, init, cycleTheme, setMode }
}

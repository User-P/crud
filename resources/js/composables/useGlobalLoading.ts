import { ref } from 'vue'

const isLoading = ref(false)
const message = ref('')

export function useGlobalLoading() {
    function show(msg?: string) {
        message.value = msg ?? 'Cargando…'
        isLoading.value = true
    }

    function hide() {
        isLoading.value = false
    }

    return {
        isLoading,
        message,
        show,
        hide,
    }
}

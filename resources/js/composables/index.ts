import { ref, Ref } from 'vue'

/**
 * Composable para manejar contadores
 * Ejemplo de cómo crear composables tipados
 */
export function useCounter(initialValue: number = 0) {
  const count: Ref<number> = ref(initialValue)

  const increment = (): void => {
    count.value++
  }

  const decrement = (): void => {
    count.value--
  }

  const reset = (): void => {
    count.value = initialValue
  }

  const setValue = (value: number): void => {
    count.value = value
  }

  return {
    count,
    increment,
    decrement,
    reset,
    setValue
  }
}

/**
 * Composable para manejar toggle booleano
 */
export function useToggle(initialValue: boolean = false) {
  const value: Ref<boolean> = ref(initialValue)

  const toggle = (): void => {
    value.value = !value.value
  }

  const setTrue = (): void => {
    value.value = true
  }

  const setFalse = (): void => {
    value.value = false
  }

  return {
    value,
    toggle,
    setTrue,
    setFalse
  }
}

/**
 * Composable para manejar loading states
 */
export function useLoading(initialState: boolean = false) {
  const isLoading: Ref<boolean> = ref(initialState)

  const startLoading = (): void => {
    isLoading.value = true
  }

  const stopLoading = (): void => {
    isLoading.value = false
  }

  const executeWithLoading = async <T>(
    callback: () => Promise<T>
  ): Promise<T> => {
    startLoading()
    try {
      return await callback()
    } finally {
      stopLoading()
    }
  }

  return {
    isLoading,
    startLoading,
    stopLoading,
    executeWithLoading
  }
}

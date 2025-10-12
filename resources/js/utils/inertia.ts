import { router } from '@inertiajs/vue3'

/**
 * Tipos para Inertia
 */
type Method = 'get' | 'post' | 'put' | 'patch' | 'delete'

interface VisitOptions {
  method?: Method
  data?: Record<string, any>
  replace?: boolean
  preserveScroll?: boolean
  preserveState?: boolean
  only?: string[]
  headers?: Record<string, string>
  errorBag?: string
  forceFormData?: boolean
  onCancelToken?: (cancelToken: any) => void
  onBefore?: (visit: any) => boolean | void
  onStart?: (visit: any) => void
  onProgress?: (progress: any) => void
  onSuccess?: (page: any) => void
  onError?: (errors: Record<string, string>) => void
  onCancel?: () => void
  onFinish?: () => void
}

/**
 * Helper tipado para hacer navegación con Inertia
 */
export const navigate = {
  /**
   * Navegar a una URL
   */
  to(url: string, options?: VisitOptions): void {
    router.visit(url, options)
  },

  /**
   * Hacer una petición GET
   */
  get(url: string, data?: Record<string, any>, options?: VisitOptions): void {
    router.get(url, data, options)
  },

  /**
   * Hacer una petición POST
   */
  post(url: string, data?: Record<string, any>, options?: VisitOptions): void {
    router.post(url, data, options)
  },

  /**
   * Hacer una petición PUT
   */
  put(url: string, data?: Record<string, any>, options?: VisitOptions): void {
    router.put(url, data, options)
  },

  /**
   * Hacer una petición PATCH
   */
  patch(url: string, data?: Record<string, any>, options?: VisitOptions): void {
    router.patch(url, data, options)
  },

  /**
   * Hacer una petición DELETE
   */
  delete(url: string, options?: VisitOptions): void {
    router.delete(url, options)
  },

  /**
   * Recargar la página actual
   */
  reload(options?: VisitOptions): void {
    router.reload(options)
  },
}

/**
 * Helper para manejar formularios con tipos
 */
export interface FormData {
  [key: string]: any
}

export interface FormOptions {
  onSuccess?: (response: any) => void
  onError?: (errors: Record<string, string>) => void
  onFinish?: () => void
}

/**
 * Enviar formulario con tipos
 */
export const submitForm = async (
  method: Method,
  url: string,
  data: FormData,
  options?: FormOptions
): Promise<void> => {
  router.visit(url, {
    method,
    data,
    onSuccess: options?.onSuccess,
    onError: options?.onError,
    onFinish: options?.onFinish,
  })
}

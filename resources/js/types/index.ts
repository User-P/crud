// ============================================
// TIPOS GLOBALES DE LA APLICACIÓN
// ============================================

/**
 * Usuario de la aplicación
 */
export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
  role?: string
}

/**
 * Tipos de respuesta paginada de Laravel
 */
export interface PaginatedResponse<T> {
  data: T[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    from: number | null
    last_page: number
    path: string
    per_page: number
    to: number | null
    total: number
  }
}

/**
 * Props compartidas de Inertia
 */
export interface InertiaSharedProps {
  auth?: {
    user: User | null
  }
  flash?: {
    success?: string
    error?: string
    info?: string
  }
  errors?: Record<string, string>
}

/**
 * Modelo de País
 */
export interface Country {
  id: number
  name: string
  code: string
  created_at: string
  updated_at: string
}

/**
 * Modelo de EventRecord
 */
export interface EventRecord {
  id: number
  country_id: number
  event_date: string
  description: string
  created_at: string
  updated_at: string
  country?: Country
}

/**
 * Estadísticas diarias
 */
export interface DailyStatistic {
  id: number
  date: string
  total_events: number
  countries_count: number
  created_at: string
  updated_at: string
}

/**
 * Tipos de formularios
 */
export interface FormErrors {
  [key: string]: string
}

/**
 * Helper para tipar respuestas de API
 */
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: FormErrors
}

## 📁 Estructura de Archivos

```
resources/js/
├── app.ts                      # Punto de entrada principal (antes app.js)
├── bootstrap.js                # Configuración de Axios
├── env.d.ts                    # Declaraciones de tipos para .vue
├── types/
│   └── index.ts                # Tipos compartidos de la aplicación
├── composables/
│   └── index.ts                # Composables reutilizables tipados
└── Pages/
    ├── Welcome.vue             # Componente de ejemplo con TS
    └── TypeScriptExample.vue   # Ejemplo completo de TypeScript
```

## 🎯 Comandos Disponibles

### Desarrollo

```bash
npm run dev
```

Inicia el servidor de desarrollo de Vite.

### Build de Producción

```bash
npm run build
```

Compila TypeScript con `vue-tsc` y luego construye con Vite.

### Verificación de Tipos

```bash
npm run type-check
```

Verifica los tipos sin generar archivos de salida.

## ✍️ Cómo Usar TypeScript en Componentes Vue

### 1️⃣ Sintaxis Básica con `<script setup lang="ts">`

```vue
<script setup lang="ts">
import { ref } from "vue";

// Variables tipadas
const counter = ref<number>(0);
const name = ref<string>("John");

// Funciones tipadas
const increment = (): void => {
    counter.value++;
};
</script>
```

### 2️⃣ Props con TypeScript

```vue
<script setup lang="ts">
// Definir interfaz para props
interface Props {
    title: string;
    count?: number; // Opcional
    items: string[];
}

// Usar props tipadas
const props = defineProps<Props>();

// Con valores por defecto
const props = withDefaults(defineProps<Props>(), {
    count: 0,
    items: () => [],
});
</script>
```

### 3️⃣ Emits con TypeScript

```vue
<script setup lang="ts">
// Definir eventos tipados
const emit = defineEmits<{
    update: [value: string];
    delete: [id: number];
    submit: [data: { name: string; email: string }];
}>();

// Usar emits
const handleClick = () => {
    emit("update", "nuevo valor");
    emit("delete", 123);
};
</script>
```

### 4️⃣ Refs y Computed con Tipos

```vue
<script setup lang="ts">
import { ref, computed, Ref } from "vue";

// Ref tipado
const count: Ref<number> = ref(0);

// Computed tipado
const doubleCount = computed<number>(() => count.value * 2);

// Ref de objetos
interface User {
    name: string;
    email: string;
}

const user = ref<User>({
    name: "Juan",
    email: "juan@example.com",
});
</script>
```

### 5️⃣ Usar Tipos Compartidos

```vue
<script setup lang="ts">
import type { User, EventRecord } from "@/types";

interface Props {
    user: User;
    records: EventRecord[];
}

const props = defineProps<Props>();
</script>
```

### 6️⃣ Composables Tipados

```vue
<script setup lang="ts">
import { useCounter, useLoading } from "@/composables";

// Usar composable con tipos
const { count, increment, decrement } = useCounter(10);
const { isLoading, executeWithLoading } = useLoading();

const fetchData = async () => {
    await executeWithLoading(async () => {
        // Tu lógica aquí
        return fetch("/api/data");
    });
};
</script>
```

## 🔧 Configuración

### tsconfig.json

Configuración principal de TypeScript con:

-   ✅ Modo estricto activado
-   ✅ Resolución de módulos tipo bundler
-   ✅ Alias `@/` apuntando a `resources/js/`
-   ✅ Soporte para archivos .vue

### vite.config.ts

-   ✅ Convertido a TypeScript
-   ✅ Alias `@/` configurado
-   ✅ Soporte para Vue 3 + Tailwind 4

## 💡 Tips y Mejores Prácticas

### ✅ DO (Hacer)

```typescript
// ✅ Tipar props con interfaces
interface Props {
    userId: number;
    name: string;
}
const props = defineProps<Props>();

// ✅ Tipar valores de retorno
const calculateTotal = (items: number[]): number => {
    return items.reduce((sum, item) => sum + item, 0);
};

// ✅ Usar tipos importados
import type { User } from "@/types";
```

### ❌ DON'T (Evitar)

```typescript
// ❌ Usar 'any' (perdes los beneficios de TS)
const data: any = fetchData();

// ❌ No tipar funciones
const process = (data) => {
    return data.map((item) => item.value);
};
```

## 📚 Recursos

-   [Vue 3 + TypeScript Docs](https://vuejs.org/guide/typescript/overview.html)
-   [TypeScript Handbook](https://www.typescriptlang.org/docs/)
-   [Inertia.js TypeScript](https://inertiajs.com/client-side-setup#typescript)

<template>
    <div class="relative w-96">
        <AutoComplete ref="ac" v-model="model" :suggestions="employees" @complete="onComplete" @item-select="onSelect"
            @focus="onFocus" optionLabel="name" :loading="loading" :placeholder="placeholder" class="w-full"
            :inputClass="inputClass" scrollHeight="600px" appendTo="body" :forceSelection="forceSelection">
            <template #header v-if="isShowingRecent && employees.length > 0">
                <div class="flex items-center justify-between px-3 py-2 text-sm text-gray-600">
                    <span class="font-medium">Recientes</span>
                    <button type="button" class="text-xs text-blue-500 hover:underline"
                        @click.stop="clearRecents">Limpiar</button>
                </div>
            </template>
            <template #option="slotProps">
                <div class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded cursor-pointer">
                    <div
                        class="w-9 h-9 rounded-full bg-blue-500 text-white flex items-center justify-center font-semibold">
                        {{ slotProps.option.name?.charAt(0) ?? "?" }}
                    </div>
                    <span class="text-gray-800">{{
                        slotProps.option.name
                        }}</span>
                </div>
            </template>
            <template #empty>
                <div class="p-3 text-gray-500">{{ emptyText }}</div>
            </template>
        </AutoComplete>
        <button v-if="model" @click="clearInput" :class="[
            'absolute top-1/2 -translate-y-1/2 text-gray-400 hover:text-black z-10 transition-all',
            loading ? 'right-10' : 'right-3',
        ]" type="button" aria-label="Limpiar">
            <i class="pi pi-times text-lg"></i>
        </button>
    </div>
</template>
<script setup lang="ts">
import { ref, watch, nextTick } from "vue";
import AutoComplete from "primevue/autocomplete";
import { useEmployeeSearch, type Employee } from "@/composables/useEmployeeSearch";

const props = withDefaults(
    defineProps<{
        placeholder?: string;
        emptyText?: string;
        minChars?: number;
        debounceMs?: number;
        inputClass?: string;
        forceSelection?: boolean;
        // memoria de recientes
        recentMax?: number; // cantidad máxima de recientes a guardar
        recentTtlDays?: number; // caducidad en días
        memoryEnabled?: boolean; // activar/desactivar memoria
        memoryKey?: string; // clave de localStorage
    }>(),
    {
        placeholder: "Buscar empleado...",
        emptyText: "No se encontraron empleados",
        minChars: 2,
        debounceMs: 700,
        inputClass: "w-full pl-4 pr-10 py-3 text-base",
        forceSelection: false,
        recentMax: 5,
        recentTtlDays: 7,
        memoryEnabled: true,
        memoryKey: "recentEmployees",
    },
);
const emit = defineEmits<{
    (e: "select", value: Employee): void;
    (e: "clear"): void;
    (e: "update:modelValue", value: Employee | string | null): void;
}>();
const model = ref<Employee | string | null>(null);
const { employees, loading, search, clearEmployees, addRecent, clearRecent } = useEmployeeSearch({
    minChars: props.minChars,
    debounceMs: props.debounceMs,
    memory: {
        enabled: props.memoryEnabled,
        key: props.memoryKey,
        max: props.recentMax,
        ttlMs: (props.recentTtlDays ?? 0) * 24 * 60 * 60 * 1000,
    },
});


const ac = ref<any | null>(null);
const isShowingRecent = ref(false);

const onComplete = (event: { query: string }) => {
    // when user types, switch off the recent view
    isShowingRecent.value = false;
    search(event.query);
};

const onFocus = async () => {
    // When focused, show recent employees automatically
    isShowingRecent.value = true;
    search("");
    await nextTick();
    try {
        // call component method to force overlay open (PrimeVue AutoComplete exposes show())
        ac.value?.show?.();
    } catch (e) {
        // ignore if method not available
    }
};

const onSelect = (event: { value: Employee }) => {
    // add selection to recent memory
    try {
        addRecent(event.value);
    } catch (e) {
        // ignore
    }
    // hide recent view after select
    isShowingRecent.value = false;
    // clear suggestions and try to hide the overlay
    clearEmployees();
    try {
        ac.value?.hide?.();
        // attempt to blur the input to ensure overlay closes
        ac.value?.inputEl?.blur?.();
    } catch (e) {
        // ignore if methods/properties not present
    }
    emit("select", event.value);
};

const clearRecents = () => {
    try {
        clearRecent();
    } catch (e) {
        // ignore
    }
    clearEmployees();
    isShowingRecent.value = false;
    try {
        ac.value?.hide?.();
    } catch (e) {
        // ignore
    }
};

const clearInput = () => {
    model.value = null;
    clearEmployees();
    emit("clear");
};

watch(model, (val) => {
    emit("update:modelValue", val);
    if (!val) emit("clear");
});
</script>

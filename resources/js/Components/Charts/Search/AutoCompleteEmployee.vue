<template>
    <div class="relative w-96">
        <AutoComplete v-model="model" :suggestions="employees" @complete="onComplete" @item-select="onSelect"
            optionLabel="name" :loading="loading" :placeholder="placeholder" class="w-full" :inputClass="inputClass"
            scrollHeight="600px" appendTo="body" :forceSelection="forceSelection">
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
import { ref, watch } from "vue";
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
    }>(),
    {
        placeholder: "Buscar empleado...",
        emptyText: "No se encontraron empleados",
        minChars: 2,
        debounceMs: 700,
        inputClass: "w-full pl-4 pr-10 py-3 text-base",
        forceSelection: false,
    },
);
const emit = defineEmits<{
    (e: "select", value: Employee): void;
    (e: "clear"): void;
    (e: "update:modelValue", value: Employee | string | null): void;
}>();
const model = ref<Employee | string | null>(null);
const { employees, loading, search, clearEmployees } = useEmployeeSearch({
    minChars: props.minChars,
    debounceMs: props.debounceMs,
});
const onComplete = (event: { query: string }) => {
    search(event.query);
};
const onSelect = (event: { value: Employee }) => {
    emit("select", event.value);
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

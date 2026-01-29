import axios from "axios";
import { ref, onBeforeUnmount } from "vue";

export type Employee = { id: string; name: string };

export const useEmployeeSearch = (options?: { minChars?: number; debounceMs?: number }) => {
    const minChars = options?.minChars ?? 2;
    const debounceMs = options?.debounceMs ?? 400;

    const loading = ref(false);
    const employees = ref<Employee[]>([]);

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;
    let reqSeq = 0;

    const clearEmployees = () => {
        employees.value = [];
    };

    const search = (queryString: string) => {

        const q = (queryString ?? "").trim();
        if (q.length < minChars) {
            clearEmployees();
            return;
        }

        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            void fetchEmployees(q);
        }, debounceMs);
    };

    const fetchEmployees = async (queryString: string) => {
        const seq = ++reqSeq;
        try {
            loading.value = true;
            const { data } = await axios.post("/teams/employee", { queryString });

            if (seq !== reqSeq) return;
            employees.value = Array.isArray(data) ? data : [];
        } catch (e: any) {
            if (seq !== reqSeq) return;
            employees.value = [];
        } finally {
            if (seq === reqSeq) loading.value = false;
        }
    };

    onBeforeUnmount(() => {
        if (debounceTimer) clearTimeout(debounceTimer);
    });

    return {
        loading,
        employees,
        search,
        clearEmployees,
        fetchEmployees,
    };
};

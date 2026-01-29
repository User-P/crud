    import axios from "axios";
    import { ref, onBeforeUnmount } from "vue";
    import { useEmployeeRecentStore } from "@/stores/employeeRecent";

    export type Employee = { id: string; name: string };

    export const useEmployeeSearch = (options?: {
        minChars?: number;
        debounceMs?: number;
        memory?: { enabled?: boolean; key?: string; max?: number; ttlMs?: number };
    }) => {
        const minChars = options?.minChars ?? 2;
        const debounceMs = options?.debounceMs ?? 400;
        const memoryOptions = options?.memory ?? { enabled: true, key: "recentEmployees", max: 5, ttlMs: 7 * 24 * 60 * 60 * 1000 };

        const loading = ref(false);
        const employees = ref<Employee[]>([]);

        let debounceTimer: ReturnType<typeof setTimeout> | null = null;
        let reqSeq = 0;

        // Use Pinia store for recent employees when memory is enabled
        const recentStore = useEmployeeRecentStore();
        if (memoryOptions.enabled) {
            // configure/load store with provided options
            try {
                recentStore.load({ key: memoryOptions.key, max: memoryOptions.max, ttlMs: memoryOptions.ttlMs });
            } catch (e) {
                // ignore
            }
        }

        const getRecent = (): Employee[] => {
            if (!memoryOptions.enabled) return [];
            return (recentStore.items ?? []).map((i) => ({ id: i.id, name: i.name }));
        };

        const addRecent = (emp: Employee) => {
            if (!memoryOptions.enabled) return;
            try {
                recentStore.add(emp, { max: memoryOptions.max });
            } catch (e) {
                // ignore
            }
        };

        const clearRecent = () => {
            if (!memoryOptions.enabled) return;
            try {
                recentStore.clear();
            } catch (e) {
                // ignore
            }
        };

        const clearEmployees = () => {
            employees.value = [];
        };

        const search = (queryString: string) => {
            const q = (queryString ?? "").trim();

            // when empty query, return recent employees (if any)
            if (q.length === 0) {
                const recent = getRecent();
                employees.value = recent;
                return;
            }

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
            // memory helpers
            getRecent,
            addRecent,
            clearRecent,
        };
    };

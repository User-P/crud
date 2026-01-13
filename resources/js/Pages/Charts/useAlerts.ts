
import axios from "axios";
import { useToast } from "primevue";
import { ref } from "vue";

export const useAlerts = () => {

    const isLoading = ref(false);
    const series = ref([])
    const categories = ref([])
    const details = ref([])
    const toast = useToast();

    const getAlerts = async (start: string, end: string, cve: string, types: {}[]) => {
        isLoading.value = true;
        try {
            const { data } = await axios.post("seguimientos/api/usage-alerts", {
                cve,
                start,
                end,
                types
            });
            series.value = data.series
            categories.value = data.categories
            details.value = data.details
        } catch (error) {
            toast.add({
                severity: "info",
                detail: "No se obtuvieron datos",
                life: 3000,
            });
        } finally {
            isLoading.value = false;
        }
    };

    return {
        isLoading,
        series,
        categories,
        details,
        getAlerts
    }

}

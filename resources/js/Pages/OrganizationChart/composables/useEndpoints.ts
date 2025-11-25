import { ref } from 'vue';
export const useEndpoints = () => {

    const endpoints = ref([]);
    const isLoading = ref(false);

    const getEndpoints = async () => {
        isLoading.value = true;
        try {
            const response = await fetch('/api/organization-data/endpoints');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            endpoints.value = data;
        } catch (error) {
            console.error('Error fetching endpoints:', error);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        endpoints,
        isLoading,
        getEndpoints,
    };
};

<template>
    <AdminLayout title="Diagrama" subtitle="Uso de endpoints">
        <div class=" mt-10 ">
            <h1 class=" text-lg text-gray-400 font-bold uppercase">
                usuarios que usan el endpoint
            </h1>
        </div>

        <div class="card overflow-x-auto border-2 border-gray-950 rounded-xl mt-4">
            <!-- Estado de carga -->
            <div v-if="isLoading" class="p-4 text-center text-sm">
                Cargando información de uso...
            </div>

            <!-- Diagrama cuando hay datos -->
            <OrganizationChart v-else-if="data" :value="data" collapsible>
                <!-- NODO tipo person (root y usuarios) -->
                <template #person="slotProps">
                    <div class="flex flex-col">
                        <div class="flex flex-col items-center p-2">
                            <!-- <img :alt="slotProps.node.data.title" :src="slotProps.node.data.image" class="mb-4 w-12 h-12" /> -->
                            <i :class="slotProps.node.data.icon" style="font-size: 4rem"></i>
                            <span class="font-bold mt-2">
                                {{ slotProps.node.data.title }}
                            </span>
                            <span class="font-sm mb-2">
                                {{ slotProps.node.data.subtitle }}
                            </span>
                            <span class="font-sm mb-2">
                                {{ slotProps.node.data.date }}
                            </span>
                        </div>
                    </div>
                </template>

                <!-- NODO default (devices) -->
                <template #default="slotProps">
                    <div class="flex flex-col">
                        <div class="flex flex-col items-center p-2">
                            <i :class="slotProps.node.icon" style="font-size: 2rem"></i>
                            <span class="font-bold mt-2">
                                {{ slotProps.node.title }}
                            </span>
                            <span class="font-sm mb-2">
                                {{ slotProps.node.subtitle }}
                            </span>
                            <span class="font-sm mb-2">
                                {{ slotProps.node.date }}
                            </span>
                        </div>
                    </div>
                </template>
            </OrganizationChart>

            <!-- Mensaje si no hay info -->
            <div v-else class="p-4 text-center text-sm text-gray-500">
                No se encontró información de uso para este endpoint.
            </div>
        </div>
    </AdminLayout>

</template>

<script setup lang="ts">
import OrganizationChart, {
    type OrganizationChartNode,
} from 'primevue/organizationchart';
import { onMounted, ref, watch } from 'vue';
import { useEndpoints } from './composables/useEndpoints';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Dates {
    start: string;
    end: string;
}

interface Props {
    device: string | null;
    cve: string | null;
    dates: Dates;
}

const props = withDefaults(defineProps<Props>(), {
    device: null,
    cve: null,
    dates: () => ({
        start: '',
        end: '',
    }),
});

// composable
const { endpoints, isLoading, getEndpoints } = useEndpoints();

onMounted(() => {
    getEndpoints();
});

interface BackendDevice {
    uuid: string;
    name: string;
}

interface BackendUser {
    main: boolean;
    name: string;
    cve: string;
    devices: BackendDevice[];
}

type BackendResponse = BackendUser[];


interface OrgNode extends OrganizationChartNode {
    data?: {
        icon?: string;
        title?: string;
        subtitle?: string;
        date?: string;
    };
    icon?: string;
    title?: string;
    subtitle?: string;
    date?: string;
    children?: OrgNode[];
}

const data = ref<OrgNode | null>(null);

const buildChartFromUsage = (
    usage: BackendResponse | null | undefined
): OrgNode => {
    const root: OrgNode = {
        key: '0',
        type: 'person',
        styleClass: '!bg-gray-200 !rounded-full w-40 h-40 border-2 border-dark',
        data: {
            icon: 'pi pi-desktop',
            title: props.device ?? 'Sin dispositivo',
            // subtitle y date opcionales
        },
        children: [],
    };

    if (!usage || !usage.length) {
        return root;
    }

    const users = usage;

    root.children = users.map((user, userIndex): OrgNode => {
        const userNode: OrgNode = {
            key: `0_${userIndex}`,
            type: 'person',
            styleClass: user.main
                ? '!bg-green-100 !rounded-full w-60 h-60 border-2 border-dark flex justify-content-center align-content-center '
                : '!bg-gray-100 !rounded-full w-60 h-60 border-2 border-dark flex justify-content-center align-content-center ',
            data: {
                icon: 'pi pi-user',
                title: user.name,
                subtitle: `NE: ${user.cve}`,
                date: '', // cuando tengas fecha la pones aquí
            },
            children: [],
        };

        // devices como nodos "default"
        userNode.children = (user.devices || []).map(
            (device, deviceIndex): OrgNode => ({
                key: `0_${userIndex}_${deviceIndex}`,
                icon: 'pi pi-desktop',
                title: device.name,
                // subtitle: device.uuid, // si quieres mostrar el uuid
                date: '', // igual, cuando tengas fecha
                styleClass:
                    '!bg-gray-100 !rounded-full w-40 h-40 border-2 border-dark flex justify-content-center align-content-center ',
            })
        );

        return userNode;
    });

    return root;
};

watch(
    endpoints,
    (value) => {
        // value debe ser el array que mandas desde el backend
        data.value = buildChartFromUsage(value as BackendResponse);
    },
    { immediate: true, deep: true }
);

watch(
    () => [props.device, props.cve, props.dates?.start, props.dates?.end],
    () => {
        data.value = buildChartFromUsage(endpoints.value as BackendResponse);
    }
);
</script>

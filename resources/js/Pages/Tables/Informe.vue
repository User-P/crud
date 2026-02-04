<script setup>
import { ref, onMounted } from 'vue';
import { faker } from '@faker-js/faker';

const data = ref({
    folio: "DSI-UBA-AR-08-2024",
    usuario: {},
    escenario: {},
    analisis: [],
    hallazgos: [],
    accionables: []
});

const generateData = () => {
    // Datos de Usuario
    data.value.usuario = {
        nombre: faker.person.fullName().toUpperCase(),
        empleado: faker.number.int({ min: 1000000, max: 9999999 }),
        correo: faker.internet.email().toLowerCase(),
        usuario: faker.internet.username(),
        puesto: "Project Manager"
    };

    // Datos de Escenario
    data.value.escenario = {
        categoria: "Monitoreo de operación Cash In/Out",
        un: "BANCO AZTECA",
        vertical: "BANCO AZTECA",
        analista: faker.person.fullName(),
        proceso: "MONITOREO DE ATMS",
        metodo: "MONITOREO USA",
        personas: 1,
        entorno: "Ambiente Organizacional"
    };

    // Hallazgos (Sección central)
    data.value.hallazgos = Array.from({ length: 3 }, () => ({
        descripcion: faker.lorem.paragraph(2),
        tipo: "Préstamo de contraseñas"
    }));

    // Accionables (Tabla inferior)
    data.value.accionables = Array.from({ length: 4 }, () => ({
        id: "BL-01-UBA-SC",
        area: "Bloqueo de Puertos USB",
        accion: "Bloqueo de Puertos USB",
        estatus: "Solicitado",
        fechaSol: "10-08-24",
        fechaAp: "10-08-24",
        reverso: "10-08-24",
        fechaRep: "10-08-24"
    }));
};

onMounted(generateData);
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-8 font-sans text-[11px] uppercase text-gray-800">
        <div class="mx-auto max-w-5xl bg-white p-10 shadow-xl border border-gray-300">

            <div class="flex justify-between items-start mb-2">
                <span class="font-bold border-b border-gray-400">{{ data.folio }}</span>
                <div class="flex gap-1">
                    <div class="w-3 h-3 rounded-full border border-gray-400"></div>
                    <div class="w-3 h-3 rounded-full border border-gray-400"></div>
                    <div class="w-3 h-3 rounded-full border border-gray-400"></div>
                </div>
            </div>

            <h1 class="text-2xl text-center font-medium tracking-widest mb-6">INFORME DEL CASO</h1>

            <table class="w-full border-collapse border border-blue-900">
                <thead>
                    <tr class="bg-blue-50 text-blue-900 font-bold">
                        <th colspan="3" class="border border-blue-900 p-2 text-center w-1/2">CONTEXTO DEL USUARIO</th>
                        <th colspan="4" class="border border-blue-900 p-2 text-center w-1/2">ESCENARIO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="4" class="border border-blue-900 p-2 text-center w-24">
                            <div
                                class="w-16 h-16 mx-auto border border-gray-400 rounded-sm flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                        </td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold w-20">NOMBRE</td>
                        <td class="border border-blue-900 p-1 font-semibold">{{ data.usuario.nombre }}</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">CATEGORÍA</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">UN</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">VERTICAL</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">ANALISTA ASIGNADO</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold">EMPLEADO</td>
                        <td class="border border-blue-900 p-1">{{ data.usuario.empleado }}</td>
                        <td rowspan="2" class="border border-blue-900 p-2 text-center align-middle">{{
                            data.escenario.categoria }}</td>
                        <td rowspan="2" class="border border-blue-900 p-2 text-center align-middle">{{ data.escenario.un
                        }}</td>
                        <td rowspan="2" class="border border-blue-900 p-2 text-center align-middle">{{
                            data.escenario.vertical }}</td>
                        <td rowspan="2" class="border border-blue-900 p-2 text-center align-middle font-semibold">{{
                            data.escenario.analista }}</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold">CORREO</td>
                        <td class="border border-blue-900 p-1 lowercase">{{ data.usuario.correo }}</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold">USUARIO</td>
                        <td class="border border-blue-900 p-1">{{ data.usuario.usuario }}</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">PROCESO / PRODUCTO</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center text-[9px]">MÉTODO DE
                            DETECCIÓN</td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center text-[9px]"># PERSONAS
                        </td>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center">ESCENARIO</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-900 p-1 bg-gray-50 font-bold text-center uppercase">Puesto</td>
                        <td colspan="2" class="border border-blue-900 p-1 font-bold">{{ data.usuario.puesto }}</td>
                        <td class="border border-blue-900 p-1 text-center">{{ data.escenario.proceso }}</td>
                        <td class="border border-blue-900 p-1 text-center">{{ data.escenario.metodo }}</td>
                        <td class="border border-blue-900 p-1 text-center">{{ data.escenario.personas }}</td>
                        <td class="border border-blue-900 p-1 text-center">{{ data.escenario.entorno }}</td>
                    </tr>

                    <tr class="bg-blue-100 text-blue-900 font-bold">
                        <td colspan="7" class="border border-blue-900 p-1 text-center">ANÁLISIS INVESTIGATIVO</td>
                    </tr>
                    <tr class="bg-gray-50 text-[9px] text-center">
                        <td class="border border-blue-900 p-1">CLAVE</td>
                        <td class="border border-blue-900 p-1">PERIODO DE MONITOREO</td>
                        <td class="border border-blue-900 p-1">CASO</td>
                        <td class="border border-blue-900 p-1">ESTATUS</td>
                        <td class="border border-blue-900 p-1">TIPO DE HALLAZGO</td>
                        <td class="border border-blue-900 p-1">CATEGORÍA HALLAZGO</td>
                        <td class="border border-blue-900 p-1">NIVEL DE RIESGO / VALOR</td>
                    </tr>
                    <tr class="text-center h-10">
                        <td class="border border-blue-900 p-1">{{ data.folio }}</td>
                        <td class="border border-blue-900 p-1">26/06/2024 al 05/07/2024</td>
                        <td class="border border-blue-900 p-1 text-[9px]">MONITOREO DE OPERACIÓN CASH IN / CASH OUT</td>
                        <td class="border border-blue-900 p-1 text-green-700 font-bold text-[9px]">FOLIO MONITOREO</td>
                        <td class="border border-blue-900 p-1">Préstamo de contraseñas</td>
                        <td class="border border-blue-900 p-1">Ciberseguridad</td>
                        <td class="border border-blue-900 p-1 font-bold bg-gray-50">MUY ALTO / 25</td>
                    </tr>

                    <tr class="bg-gray-50 font-bold text-center">
                        <td colspan="4" class="border border-blue-900 p-1">HALLAZGO</td>
                        <td class="border border-blue-900 p-1">TIPO DE HALLAZGO</td>
                        <td colspan="2" class="border border-blue-900 p-1">EVIDENCIA</td>
                    </tr>
                    <tr v-for="(h, idx) in data.hallazgos" :key="idx">
                        <td colspan="4"
                            class="border border-blue-900 p-3 text-justify normal-case leading-tight text-gray-600">
                            {{ h.descripcion }}
                        </td>
                        <td class="border border-blue-900 p-2 text-center align-middle font-bold">{{ h.tipo }}</td>
                        <td colspan="2" class="border border-blue-900 p-2">
                            <div class="flex justify-center gap-2">
                                <div class="w-12 h-12 border border-gray-400 relative overflow-hidden">
                                    <div
                                        class="absolute inset-0 border-t border-gray-400 rotate-45 origin-top-left scale-150">
                                    </div>
                                    <div
                                        class="absolute inset-0 border-t border-gray-400 -rotate-45 origin-top-right scale-150">
                                    </div>
                                </div>
                                <div class="w-12 h-12 border border-gray-400 relative overflow-hidden">
                                    <div
                                        class="absolute inset-0 border-t border-gray-400 rotate-45 origin-top-left scale-150">
                                    </div>
                                    <div
                                        class="absolute inset-0 border-t border-gray-400 -rotate-45 origin-top-right scale-150">
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="my-4 border-y-4 border-blue-900 py-1 text-center font-bold text-blue-900">
                A QUIEN SE INFORMÓ
            </div>

            <table class="w-full border-collapse border border-blue-900">
                <thead>
                    <tr class="bg-blue-100 text-blue-900 font-bold text-center">
                        <td colspan="8" class="border border-blue-900 p-1">ACCIONABLES</td>
                    </tr>
                    <tr class="bg-gray-50 text-[9px] text-center font-bold">
                        <th class="border border-blue-900 p-1">ID</th>
                        <th class="border border-blue-900 p-1">ÁREA DE EJECUCIÓN</th>
                        <th class="border border-blue-900 p-1">ACCIONABLES</th>
                        <th class="border border-blue-900 p-1">ESTATUS</th>
                        <th class="border border-blue-900 p-1 leading-none">FECHA SOLICITUD</th>
                        <th class="border border-blue-900 p-1 leading-none">FECHA APROBACIÓN</th>
                        <th class="border border-blue-900 p-1">REVERSO</th>
                        <th class="border border-blue-900 p-1 leading-none">FECHA REVERSO</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr v-for="(item, idx) in data.accionables" :key="idx" class="even:bg-gray-50">
                        <td class="border border-blue-900 p-1 font-bold">{{ item.id }}</td>
                        <td class="border border-blue-900 p-1">{{ item.area }}</td>
                        <td class="border border-blue-900 p-1">{{ item.accion }}</td>
                        <td class="border border-blue-900 p-1 font-semibold text-blue-700">{{ item.estatus }}</td>
                        <td class="border border-blue-900 p-1">{{ item.fechaSol }}</td>
                        <td class="border border-blue-900 p-1">{{ item.fechaAp }}</td>
                        <td class="border border-blue-900 p-1">{{ item.reverso }}</td>
                        <td class="border border-blue-900 p-1">{{ item.fechaRep }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 flex items-center text-[10px] text-gray-500 font-bold">
                <div class="flex items-center gap-1 cursor-pointer hover:text-blue-600">
                    <div class="w-4 h-4 rounded-full border border-gray-500 flex items-center justify-center">+</div>
                    AGREGAR HALLAZGO
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Ajustes específicos para que parezca una tabla de Excel/PDF impreso */
td,
th {
    height: 24px;
}
</style>

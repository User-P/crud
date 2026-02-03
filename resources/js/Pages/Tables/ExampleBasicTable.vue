<template>
  <div>
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium">Basic table example</span>
      <Button type="button" class="p-button-sm p-button-secondary" label="Regenerar datos"
        @click="emit('regenerate')" />
    </div>

    <TanStackTable :data="data" :columns="columns" :loading="loading" enable-sorting enable-global-filter
      skeleton-loading :skeleton-rows="6" show-sticky-header @update:cell="(e) => emit('update:cell', e)" />
  </div>
</template>

<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import TanStackTable from '@/Components/Tables/TanStackTable.vue'
import Button from 'primevue/button'

const props = withDefaults(defineProps<{ data: any[]; columns: ColumnDef<any>[]; loading?: boolean }>(), {
  loading: false,
})

const emit = defineEmits<{
  (e: 'update:cell', payload: { rowId: string; columnId: string; value: unknown; oldValue: unknown; original: any }): void
  (e: 'regenerate'): void
}>()
</script>

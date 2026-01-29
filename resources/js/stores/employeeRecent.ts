import { defineStore } from 'pinia';
import { ref } from 'vue';

type StoredItem = { id: string; name: string; ts: number };

export const useEmployeeRecentStore = defineStore('employeeRecent', () => {
  const items = ref<StoredItem[]>([]);
  let key = 'recentEmployees';
  let max = 5;
  let ttlMs = 7 * 24 * 60 * 60 * 1000;

  const storageAvailable = () => typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';

  const load = (opts?: { key?: string; max?: number; ttlMs?: number }) => {
    if (opts?.key) key = opts.key;
    if (opts?.max) max = opts.max;
    if (opts?.ttlMs) ttlMs = opts.ttlMs;

    if (!storageAvailable()) {
      items.value = [];
      return;
    }

    try {
      const raw = localStorage.getItem(key);
      if (!raw) {
        items.value = [];
        return;
      }
      const parsed = JSON.parse(raw) as StoredItem[];
      const now = Date.now();
      const filtered = parsed.filter((i) => i.ts + ttlMs > now);
      filtered.sort((a, b) => b.ts - a.ts);
      items.value = filtered.slice(0, max);
    } catch (e) {
      items.value = [];
    }
  };

  const persist = () => {
    if (!storageAvailable()) return;
    try {
      localStorage.setItem(key, JSON.stringify(items.value));
    } catch (e) {
      // ignore
    }
  };

  const add = (emp: { id: string; name: string }, opts?: { max?: number }) => {
    // ensure single copy and newest first
    items.value = items.value.filter((i) => i.id !== emp.id);
    items.value.unshift({ id: emp.id, name: emp.name, ts: Date.now() });
    const finalMax = opts?.max ?? max;
    items.value = items.value.slice(0, finalMax);
    persist();
  };

  const clear = () => {
    items.value = [];
    if (!storageAvailable()) return;
    try {
      localStorage.removeItem(key);
    } catch (e) {
      // ignore
    }
  };

  return { items, load, add, clear };
});

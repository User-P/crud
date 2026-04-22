export const WORKER_SOURCE = `
  let dataset = [];
  let columnKeys = [];
  let activeDatasetVersion = 0;

  const parseMaybeNumber = (value) => {
    if (typeof value === 'number' && Number.isFinite(value)) return value;
    if (typeof value !== 'string') return null;
    const trimmed = value.replace(/[%,$\\s]/g, '');
    if (!trimmed) return null;

    const dotCount = (trimmed.match(/\\./g) || []).length;
    const commaCount = (trimmed.match(/,/g) || []).length;

    let normalized;
    if (dotCount === 1 && commaCount === 0) {
      normalized = trimmed;
    } else if (dotCount === 1 && commaCount === 1 && trimmed.indexOf(',') < trimmed.indexOf('.')) {
      normalized = trimmed.replace(/,/g, '');
    } else {
      normalized = trimmed.replace(/\\./g, '').replace(',', '.');
    }

    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : null;
  };

  const parseMaybeDate = (value) => {
    if (value instanceof Date) return value.getTime();
    if (typeof value !== 'string') return null;
    const ts = Date.parse(value);
    return Number.isFinite(ts) ? ts : null;
  };

  self.onmessage = (event) => {
    const payload = event.data;

    if (payload.type === 'init') {
      dataset = Array.isArray(payload.rows) ? payload.rows : [];
      columnKeys = Array.isArray(payload.columnKeys) ? payload.columnKeys : [];
      activeDatasetVersion = Number(payload.datasetVersion || 0);
      return;
    }

    if (payload.type !== 'process') return;

    const q = (payload.query || '').toLowerCase();
    const sort = payload.sorting;
    const pageIndex = Number(payload.pageIndex ?? 0);
    const pageSize = Number(payload.pageSize || 10);
    const exportAll = !!payload.exportAll;

    let indexes = [];
    for (let i = 0; i < dataset.length; i++) {
      if (!q) { indexes.push(i); continue; }
      const row = dataset[i] || {};
      let match = false;
      for (let j = 0; j < columnKeys.length; j++) {
        const v = row[columnKeys[j]];
        if (String(v == null ? '' : v).toLowerCase().includes(q)) {
          match = true;
          break;
        }
      }
      if (match) indexes.push(i);
    }

    if (sort && sort.key) {
      indexes.sort((a, b) => {
        const av = dataset[a]?.[sort.key];
        const bv = dataset[b]?.[sort.key];
        if (av == null && bv == null) return 0;
        if (av == null) return sort.desc ? 1 : -1;
        if (bv == null) return sort.desc ? -1 : 1;

        const nav = parseMaybeNumber(av);
        const nbv = parseMaybeNumber(bv);
        if (nav !== null && nbv !== null) return sort.desc ? nbv - nav : nav - nbv;

        const dav = parseMaybeDate(av);
        const dbv = parseMaybeDate(bv);
        if (dav !== null && dbv !== null) return sort.desc ? dbv - dav : dav - dbv;

        const cmp = String(av ?? '').localeCompare(String(bv ?? ''), 'es', {
          numeric: true, sensitivity: 'base', ignorePunctuation: true,
        });
        return sort.desc ? -cmp : cmp;
      });
    }

    const total = indexes.length;
    let pageIndexes;
    if (exportAll) {
      pageIndexes = indexes;
    } else {
      const start = pageIndex * pageSize;
      const end = Math.min(start + pageSize, total);
      pageIndexes = start >= total ? [] : indexes.slice(start, end);
    }

    self.postMessage({
      reqId: payload.reqId,
      datasetVersion: activeDatasetVersion,
      total,
      pageIndexes,
      exportAll,
    });
  };
`;

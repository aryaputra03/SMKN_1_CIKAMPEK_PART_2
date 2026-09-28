/* Client MySQL/PHP. Semua komunikasi data menggunakan fetch() ke /api. */
(() => {
  const request = async (url, options = {}) => {
    const response = await fetch(url, { credentials: 'same-origin', ...options });
    let payload;
    try { payload = await response.json(); } catch { payload = { error: 'Respons server tidak valid.' }; }
    if (!response.ok && !payload.error) payload.error = `HTTP ${response.status}`;
    if (typeof payload.error === 'string') payload.error = { message: payload.error };
    return payload;
  };

  class Query {
    constructor(table) { this.body = { table, action: 'select', filters: [] }; }
    select(columns = '*', options = {}) { this.body.action = 'select'; this.body.columns = columns; this.body.count = options.count === 'exact'; return this; }
    insert(values) { this.body.action = 'insert'; this.body.values = values; return this; }
    update(values) { this.body.action = 'update'; this.body.values = values; return this; }
    delete() { this.body.action = 'delete'; return this; }
    eq(column, value) { this.body.filters.push({ column, operator: 'eq', value }); return this; }
    neq(column, value) { this.body.filters.push({ column, operator: 'neq', value }); return this; }
    order(column, options = {}) { this.body.order = { column, ascending: options.ascending !== false }; return this; }
    limit(value) { this.body.limit = value; return this; }
    single() { this.body.single = true; this.body.limit = 1; return this; }
    maybeSingle() { this.body.single = true; this.body.maybeSingle = true; this.body.limit = 1; return this; }
    then(resolve, reject) { return request('/api/data.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(this.body) }).then(resolve, reject); }
  }

  const storage = {
    from(bucket) {
      let lastPublicUrl = null;
      return {
        async upload(_name, file) {
          const form = new FormData();
          form.append('bucket', bucket);
          form.append('file', file);
          const result = await request('/api/upload.php', { method: 'POST', body: form });
          lastPublicUrl = result.data?.publicUrl || null;
          return result;
        },
        getPublicUrl(path) {
          return { data: { publicUrl: lastPublicUrl || `/uploads/${encodeURIComponent(bucket)}/${encodeURIComponent(path)}` } };
        },
        async remove(paths) {
          const form = new FormData();
          form.append('bucket', bucket);
          form.append('action', 'delete');
          (paths || []).forEach(path => form.append('paths[]', path));
          return request('/api/upload.php', { method: 'POST', body: form });
        },
      };
    },
  };

  window.supabase = {
    from: table => new Query(table),
    storage,
    auth: {
      async getSession() { return request('/api/session.php'); },
      async signInWithPassword({ email, password }) { return request('/api/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }) }); },
      async signOut() { return request('/api/logout.php', { method: 'POST' }); },
    },
  };
  window.supabaseClient = window.supabase;
})();

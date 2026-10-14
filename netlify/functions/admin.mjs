import { getStore } from '@netlify/blobs';
import { adminCookie, config, createAdminToken, getCookie, isValidAdminToken, json, parseBody } from '../shared.mjs';

function unauthorized() { return json({ error: 'Unauthorized.' }, 401, { 'www-authenticate': 'Cookie' }); }
function equal(left, right) { const a = new TextEncoder().encode(String(left)); const b = new TextEncoder().encode(String(right)); if (a.length !== b.length) return false; let result = 0; for (let i = 0; i < a.length; i += 1) result |= a[i] ^ b[i]; return result === 0; }

export default async (request) => {
  try {
    if (request.method === 'POST') {
      const body = await parseBody(request);
      if (!body.password || !equal(body.password, config.adminPassword)) return unauthorized();
      return json({ ok: true }, 200, { 'set-cookie': adminCookie(createAdminToken()) });
    }
    if (request.method === 'DELETE') return json({ ok: true }, 200, { 'set-cookie': adminCookie('', 0) });
    if (request.method !== 'GET' || !isValidAdminToken(getCookie(request, 'aurelia_admin'))) return unauthorized();
    const store = getStore('aurelia-leads');
    const { blobs } = await store.list();
    const leads = await Promise.all(blobs.map(async (blob) => {
      const value = await store.get(blob.key, { type: 'json' });
      return value ? { key: blob.key, ...value } : null;
    }));
    leads.sort((a, b) => String(b?.createdAt || '').localeCompare(String(a?.createdAt || '')));
    return json({ leads: leads.filter(Boolean) });
  } catch (error) {
    console.error('Admin function failed:', error);
    return json({ error: 'Admin service is unavailable.' }, 500);
  }
};

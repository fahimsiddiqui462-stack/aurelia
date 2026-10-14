import crypto from 'node:crypto';
import { getStore } from '@netlify/blobs';
import { clean, config, json, parseBody, redirect, sendEmail } from '../shared.mjs';

export default async (request) => {
  if (request.method !== 'POST') return json({ error: 'Method not allowed.' }, 405);
  try {
    const body = await parseBody(request);
    if (body.website) return redirect('/contact.html?status=received');
    const name = clean(body.name, 120); const email = clean(body.email, 160); const interest = clean(body.interest || 'General enquiry', 120); const message = clean(body.message || 'None', 800);
    if (!name || !email) return json({ error: 'Name and email are required.' }, 400);
    const createdAt = new Date().toISOString();
    const lead = { createdAt, name, email, interest, message };
    const store = getStore('aurelia-leads'); const key = `${createdAt.replace(/[^0-9]/g, '')}-${crypto.randomUUID()}`;
    await store.setJSON(key, lead);
    const bodyText = [`New enquiry for ${config.clubName}`, '', `Name: ${name}`, `Email: ${email}`, `Interest: ${interest}`, `Message: ${message}`, `Received: ${createdAt}`].join('\n');
    await sendEmail({ subject: `New club enquiry — ${name}`, text: bodyText });
    const whatsappText = [`Hello ${config.clubName}, I would like to learn more about the club.`, '', `Name: ${name}`, `Email: ${email}`, `Interest: ${interest}`, `Message: ${message}`].join('\n');
    return redirect(`https://wa.me/${config.whatsappNumber}?text=${encodeURIComponent(whatsappText)}`);
  } catch (error) { console.error('Lead function failed:', error); return json({ error: 'Could not save the enquiry.' }, 500); }
};

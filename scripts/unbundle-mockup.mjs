#!/usr/bin/env node
// Unpack a "Bundled Page" HTML export (manifest + template + base64 assets) into
// a plain folder: <out>/index.html (asset uuids -> images/<uuid>.<ext>), <out>/images/*,
// <out>/inventory.json. Usage: node scripts/unbundle-mockup.mjs <bundle.html> [outDir=.mockup]
import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';

const [, , src, out = '.mockup'] = process.argv;
if (!src) { console.error('usage: unbundle-mockup.mjs <bundle.html> [outDir]'); process.exit(2); }
const html = fs.readFileSync(src, 'utf8');
const grab = (type) => {
  const m = html.match(new RegExp(`<script[^>]*type="__bundler/${type}"[^>]*>([\\s\\S]*?)</script>`));
  return m ? JSON.parse(m[1]) : null;
};
const manifest = grab('manifest');
let template = grab('template');
if (!manifest || !template) {
  // Not a bundle: already plain HTML.
  fs.mkdirSync(out, { recursive: true });
  fs.copyFileSync(src, path.join(out, 'index.html'));
  fs.writeFileSync(path.join(out, 'inventory.json'), JSON.stringify({ bundled: false }, null, 1));
  console.log('plain html copied to', out); process.exit(0);
}
const pageOrder = grab('page_order') || [];
const EXT = { 'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp', 'image/gif': 'gif', 'image/svg+xml': 'svg',
  'image/avif': 'avif', 'font/woff2': 'woff2', 'font/woff': 'woff', 'font/ttf': 'ttf', 'font/otf': 'otf',
  'text/css': 'css', 'text/javascript': 'js', 'application/javascript': 'js', 'application/json': 'json' };
fs.mkdirSync(path.join(out, 'images'), { recursive: true });
fs.mkdirSync(path.join(out, 'assets'), { recursive: true });
const files = {};
for (const [uuid, e] of Object.entries(manifest)) {
  if (pageOrder.includes(uuid)) continue;
  let buf = Buffer.from(e.data, 'base64');
  if (e.compressed) buf = zlib.gunzipSync(buf);
  const ext = EXT[e.mime] || (e.mime.split('/')[1] || 'bin').replace(/\+.*/, '');
  const dir = e.mime.startsWith('image/') ? 'images' : 'assets';
  const rel = `${dir}/${uuid.slice(0, 8)}.${ext}`;
  fs.writeFileSync(path.join(out, rel), buf);
  files[uuid] = { rel, mime: e.mime, bytes: buf.length };
  template = template.split(uuid).join(rel);
}
const resources = {};
for (const r of grab('ext_resources') || []) if (files[r.uuid]) resources[r.id] = files[r.uuid].rel;
template = template.replace(/\s+integrity="[^"]*"/gi, '').replace(/\s+crossorigin="[^"]*"/gi, '');
const head = template.match(/<head[^>]*>/i);
const runnable = head
  ? template.slice(0, head.index + head[0].length) + `<script>window.__resources=${JSON.stringify(resources)};</script>` + template.slice(head.index + head[0].length)
  : template;
fs.writeFileSync(path.join(out, 'index.html'), runnable); // opens in a browser like the original bundle
fs.writeFileSync(path.join(out, 'resources.json'), JSON.stringify(resources, null, 1)); // logic.js key (img_<id>) -> file

// Agent-friendly views: styles.css, logic.js (x-dc script) and slim.html (no styles/scripts, one tag per line).
const styles = [...template.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/gi)].map((m) => m[1].trim()).join('\n\n');
const logic = [...template.matchAll(/<script type="text\/x-dc"[^>]*>([\s\S]*?)<\/script>/gi)].map((m) => m[1].trim()).join('\n\n');
const slim = template
  .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '<style/>')
  .replace(/<script type="text\/x-dc"[^>]*>[\s\S]*?<\/script>/gi, '<!-- logic: logic.js -->')
  .replace(/>\s+</g, '><')
  .replace(/<(?=[a-zA-Z/!])/g, '\n<')
  .trim() + '\n';
fs.writeFileSync(path.join(out, 'styles.css'), styles);
fs.writeFileSync(path.join(out, 'logic.js'), logic);
fs.writeFileSync(path.join(out, 'slim.html'), slim);
fs.writeFileSync(path.join(out, 'inventory.json'), JSON.stringify({
  bundled: true, template_bytes: template.length, resources: Object.keys(resources).length,
  assets: Object.values(files).map((f) => ({ file: f.rel, mime: f.mime, bytes: f.bytes })),
}, null, 1));
console.log(`unpacked ${Object.keys(files).length} assets, index.html ${template.length} bytes -> ${out}`);

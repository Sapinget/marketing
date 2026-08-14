/**
 * Worker proxy: marketing.purapuraponsel.workers.dev -> ORIGIN_URL (Cloudflare Tunnel)
 *
 * ORIGIN_URL diset lewat secret, contoh:
 *   wrangler secret put ORIGIN_URL
 *   -> isi: https://xxxxx.trycloudflare.com
 *
 * Kalau pakai Named Tunnel dengan hostname sendiri, ORIGIN_URL bisa
 * langsung diarahkan ke hostname tunnel tersebut.
 */
export default {
  async fetch(request, env) {
    if (!env.ORIGIN_URL) {
      return new Response('ORIGIN_URL belum diset. Jalankan: wrangler secret put ORIGIN_URL', {
        status: 500,
      });
    }

    const origin = new URL(env.ORIGIN_URL);
    const incoming = new URL(request.url);

    const target = new URL(incoming.pathname + incoming.search, origin);

    const proxyRequest = new Request(target, request);
    proxyRequest.headers.set('X-Forwarded-Host', incoming.hostname);
    proxyRequest.headers.set('X-Forwarded-Proto', incoming.protocol.replace(':', ''));

    return fetch(proxyRequest);
  },
};

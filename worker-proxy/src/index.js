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
    const originUrl = 'https://followed-grow-rosa-greene.trycloudflare.com';
    const origin = new URL(originUrl);
    const incoming = new URL(request.url);

    const target = new URL(incoming.pathname + incoming.search, origin);

    const proxyRequest = new Request(target, request);
    proxyRequest.headers.set('X-Forwarded-Host', incoming.hostname);
    proxyRequest.headers.set('X-Forwarded-Proto', incoming.protocol.replace(':', ''));

    return fetch(proxyRequest);
  },
};

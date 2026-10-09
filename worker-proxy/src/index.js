/**
 * Worker proxy: marketing.purapuraponsel.workers.dev -> ORIGIN_URL -> Laravel lokal (laptop).
 *
 * ORIGIN_URL = URL quick-tunnel (*.trycloudflare.com), di-set otomatis oleh PM2
 * process `marketing-worker-monitor` (~/LaunchScripts/tunnel-worker-sync.sh).
 */
const TUNNEL_DOWN = new Set([502, 503, 504, 521, 522, 523, 525, 530]);

export default {
  async fetch(request, env) {
    const originUrl = String(env.ORIGIN_URL || "").trim();
    const incoming = new URL(request.url);

    if (incoming.pathname === "/__health") {
      return Response.json({
        ok: Boolean(originUrl),
        worker: "marketing",
        upstream: originUrl || null,
        note: originUrl ? undefined : "ORIGIN_URL belum di-set (marketing-worker-monitor belum jalan?)",
      });
    }

    if (!originUrl) {
      return new Response("Bad Gateway: ORIGIN_URL belum di-set (marketing-worker-monitor belum jalan?)", {
        status: 502,
        headers: { "content-type": "text/plain; charset=utf-8" },
      });
    }

    const origin = new URL(originUrl);
    const target = new URL(incoming.pathname + incoming.search, origin);

    const proxyRequest = new Request(target, request);
    proxyRequest.headers.set("Host", origin.host);
    proxyRequest.headers.set("X-Forwarded-Host", incoming.hostname);
    proxyRequest.headers.set("X-Forwarded-Proto", incoming.protocol.replace(":", ""));

    let response;
    try {
      response = await fetch(proxyRequest);
    } catch (err) {
      return new Response(`Bad Gateway: upstream tunnel unreachable (${origin.host})\n${err}`, {
        status: 502,
        headers: { "content-type": "text/plain; charset=utf-8", "x-proxy-upstream": origin.host },
      });
    }
    if (TUNNEL_DOWN.has(response.status)) {
      return new Response(`Bad Gateway: upstream tunnel down (${origin.host}), edge status ${response.status}`, {
        status: 502,
        headers: { "content-type": "text/plain; charset=utf-8", "x-proxy-upstream": origin.host },
      });
    }

    return withCacheHeaders(incoming.pathname, response);
  },
};

// Origin (php -S di belakang tunnel) tidak mengirim Cache-Control untuk berkas statis.
// Aset Vite ber-hash aman di-cache selamanya; aset vendor/gambar cukup sehari.
function withCacheHeaders(pathname, response) {
  if (response.status !== 200 || response.headers.has("cache-control")) return response;
  let policy = null;
  if (pathname.startsWith("/build/assets/")) policy = "public, max-age=31536000, immutable";
  else if (pathname.startsWith("/vendor/dashboard/") || pathname.startsWith("/asset/")) policy = "public, max-age=86400";
  if (!policy) return response;

  const headers = new Headers(response.headers);
  headers.set("cache-control", policy);
  return new Response(response.body, { status: response.status, statusText: response.statusText, headers });
}

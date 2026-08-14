# Deploy Local Laravel via Cloudflare Worker Proxy

Arsitektur:

```text
https://marketing.purapuraponsel.workers.dev
        │  (Worker: worker-proxy/src/index.js)
        ▼
https://<random>.trycloudflare.com   <- Cloudflare Tunnel (quick tunnel)
        ▼
http://127.0.0.1:8090                <- Laravel (php artisan serve)
```

Worker meneruskan semua request ke `ORIGIN_URL` (URL tunnel), sehingga
domain publik tetap `marketing.purapuraponsel.workers.dev` walau backend
jalan di laptop lokal.

## 1. Jalankan Laravel lokal

```bash
php artisan serve --host=127.0.0.1 --port=8090
```

## 2. Jalankan Cloudflare Tunnel

```bash
bash scripts/launchers/start-cloudflare-quick-tunnel.command 8090
```

Copy URL yang muncul, contoh:

```text
https://random-words-1234.trycloudflare.com
https://argument-instruments-merry-francis.trycloudflare.com
```

> Quick tunnel URL berubah setiap kali dijalankan ulang. Untuk URL tetap,
> pakai Named Tunnel (`cloudflared tunnel login` lalu `cloudflared tunnel create`).

## 3. Login Wrangler (sekali saja)

```bash
cd worker-proxy
npx wrangler login
```

## 4. Set ORIGIN_URL sebagai secret

```bash
npx wrangler secret put ORIGIN_URL
# paste URL tunnel dari langkah 2, contoh:
# https://random-words-1234.trycloudflare.com
```

## 5. Deploy Worker

```bash
npx wrangler deploy
```

Setelah deploy, akses:

```text
https://marketing.purapuraponsel.workers.dev/
```

Worker akan meneruskan request ke tunnel -> Laravel lokal.

## 6. Update ORIGIN_URL saat tunnel restart

Setiap kali quick tunnel dijalankan ulang, URL berubah. Update secret lagi:

```bash
npx wrangler secret put ORIGIN_URL
npx wrangler deploy
```

## Catatan

- Laptop harus tetap menyala dan Laravel + tunnel harus tetap berjalan
  selama Worker diakses publik.
- Untuk produksi stabil, sebaiknya migrasi ke Named Tunnel + domain sendiri,
  atau host Laravel di VPS/Laravel Cloud.
- Jangan commit `ORIGIN_URL` secara plaintext ke repo; selalu pakai
  `wrangler secret put`.

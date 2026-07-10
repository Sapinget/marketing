# Ngrok Setup

## Start cepat

Jalankan launcher berikut:

```bash
bash scripts/launchers/start-ngrok-dashboard.command
```

Atau kalau port dashboard beda:

```bash
bash scripts/launchers/start-ngrok-dashboard.command 8090
```

## Syarat

- `ngrok` sudah terpasang dan sudah login
- server dashboard lokal sudah hidup

## Output penting

- URL publik akan tampil di terminal `ngrok`
- inspector lokal: `http://127.0.0.1:4040`

## Jalan di background dengan PM2

Start:

```bash
pm2 start ecosystem.ngrok.config.cjs
```

Lihat status:

```bash
pm2 status
```

Lihat log:

```bash
pm2 logs marketing-dashboard-ngrok
```

Restart:

```bash
pm2 restart marketing-dashboard-ngrok
```

Stop:

```bash
pm2 stop marketing-dashboard-ngrok
```

Hapus dari PM2:

```bash
pm2 delete marketing-dashboard-ngrok
```

## Catatan

- tunnel aktif selama proses `ngrok` masih berjalan
- kalau terminal tertutup atau proses `zsh` terminate, tunnel ikut mati

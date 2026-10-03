# CMS Bumi — Frontend (MA Bustanul Muta'allimin)

Frontend website madrasah: landing page + panel admin.

## Stack
- Vue 3 + Vite
- Tailwind CSS v4
- Vue Router + Pinia

## Halaman
- **Publik**: Beranda, Profil, Berita (+detail), Jurusan (+detail), Layanan.
- **Admin** (`/admin/login`): kelola semua konten (pengaturan, profil, guru,
  berita, umpan balik, jurusan, fasilitas, ekstrakurikuler, galeri).

## Setup lokal
```bash
npm install
cp .env.example .env   # arahkan VITE_API_BASE_URL ke API lokal
npm run dev
```
Default `VITE_API_BASE_URL=http://127.0.0.1:8011/api`.

## Build produksi
```bash
npm run build
```
Hasil di folder `dist/` (static SPA, deploy ke document root subdomain).

## Deploy otomatis (push → produksi)
GitHub Actions (`deploy.yml`) build SPA lalu upload `dist/` ke cPanel via SSH.

### Setup sekali
1. Buat subdomain `cms-bumi.ingintau.my.id` di cPanel.
2. **SSH key untuk GitHub Actions** (sekali, sama dengan repo backend):
   - Generate key tanpa passphrase: `ssh-keygen -t ed25519 -f ~/.ssh/deploy -N ""`
   - `cat ~/.ssh/deploy.pub >> ~/.ssh/authorized_keys`
   - `cat ~/.ssh/deploy` → salin **private key**.
3. Di GitHub → repo → **Settings → Secrets and variables → Actions**:
   - `SSH_HOST` = host cPanel
   - `SSH_USER` = username cPanel
   - `SSH_KEY` = private key
   - `SSH_PORT` = `22` (opsional)
   - `DEPLOY_PATH` = document root subdomain (mis. `~/cms-bumi`)
   - `VITE_API_BASE_URL` = URL API produksi (mis.
     `https://api-cms-bumi.ingintau.my.id/api`)

Setelah itu, setiap `git push` ke `main` otomatis build + deploy.

<?php

/**
 * Front controller situs depan (SPA) — PENYUNTIK META SEO.
 *
 * Situs depan adalah aplikasi Vue (hasil build) yang seluruh isinya dibuat oleh
 * JavaScript. Akibatnya HTML yang diterima crawler hanya berisi kerangka kosong:
 * judul sama untuk semua halaman, tanpa deskripsi, tanpa Open Graph. Pratinjau
 * tautan di WhatsApp/Facebook pun kosong.
 *
 * Berkas ini mengerjakan hal yang sama seperti yang dilakukan Yoast di WordPress:
 * meta (title, description, canonical, Open Graph, Twitter, structured data)
 * disusun di SERVER lalu disuntikkan ke HTML sebelum dikirim ke pengunjung.
 * Jadi Google, WhatsApp, Facebook, dan mesin pencari lain membaca meta yang benar
 * tanpa perlu menjalankan JavaScript.
 *
 * Prinsip keselamatan: apa pun kegagalannya (API mati, berkas hilang), halaman
 * tetap tampil. Bila ada masalah, kerangka asli dikirim apa adanya.
 *
 * Konfigurasi (seo.config.php) dibuat otomatis saat deploy dan TIDAK disimpan di
 * repo publik, supaya nama domain dan alamat API tidak ikut tersebar.
 */

declare(strict_types=1);

// ---------------------------------------------------------------- konfigurasi
$konfig = [
    'api' => 'http://127.0.0.1:8011/api',  // bawaan untuk pengembangan lokal
    'situs' => '',                          // kosong = pakai domain dari permintaan
];

if (is_file(__DIR__ . '/seo.config.php')) {
    $kustom = require __DIR__ . '/seo.config.php';
    if (is_array($kustom)) {
        $konfig = array_merge($konfig, $kustom);
    }
}

$skema = ((($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
$asal = rtrim($konfig['situs'] !== '' ? $konfig['situs'] : $skema.'://'.($_SERVER['HTTP_HOST'] ?? 'localhost'), '/');

$jalur = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$jalur = '/'.trim($jalur, '/');
// buang akhiran .html dan garis miring ganda
$jalur = preg_replace('#/+#', '/', $jalur) ?: '/';

// ------------------------------------------------------------------- bantuan
function e(?string $teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ambil JSON dari API, dengan cache berkas sederhana. */
function api_get(string $url, int $ttl): ?array
{
    $dir = __DIR__.'/.seo-cache';
    $berkas = $dir.'/'.sha1($url).'.json';

    if (is_file($berkas) && (time() - filemtime($berkas)) < $ttl) {
        $isi = @file_get_contents($berkas);
        if ($isi !== false) {
            $data = json_decode($isi, true);
            if (is_array($data)) {
                return $data;
            }
        }
    }

    $konteks = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\nUser-Agent: cms-bumi-seo/1.0\r\n",
        ],
    ]);

    $mentah = @file_get_contents($url, false, $konteks);
    if ($mentah === false || trim($mentah) === '') {
        return null;
    }

    $data = json_decode($mentah, true);
    if (! is_array($data)) {
        return null;
    }

    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    @file_put_contents($berkas, $mentah);

    return $data;
}

// --------------------------------------------------------------- robots.txt
if ($jalur === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Disallow: /admin\n";
    echo "Disallow: /admin/\n";
    echo "\n";
    echo 'Sitemap: '.$asal."/sitemap.xml\n";
    exit;
}

// -------------------------------------------------------------- sitemap.xml
if ($jalur === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=1800');

    $tetap = ['/', '/profil', '/berita', '/jurusan', '/layanan', '/kontak'];
    $uri = [];

    foreach ($tetap as $u) {
        $uri[] = ['loc' => $asal.$u, 'lastmod' => null, 'prioritas' => $u === '/' ? '1.0' : '0.7'];
    }

    $seo = api_get($konfig['api'].'/seo/url', 900);
    if (is_array($seo['data'] ?? null)) {
        foreach (($seo['data']['berita'] ?? []) as $p) {
            if (! empty($p['slug'])) {
                $uri[] = [
                    'loc' => $asal.'/berita/'.$p['slug'],
                    'lastmod' => $p['updated_at'] ?? $p['tanggal'] ?? null,
                    'prioritas' => '0.8',
                ];
            }
        }
        foreach (($seo['data']['jurusan'] ?? []) as $j) {
            if (! empty($j['slug'])) {
                $uri[] = ['loc' => $asal.'/jurusan/'.$j['slug'], 'lastmod' => $j['updated_at'] ?? null, 'prioritas' => '0.6'];
            }
        }
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($uri as $u) {
        echo "  <url>\n";
        echo '    <loc>'.e($u['loc'])."</loc>\n";
        if (! empty($u['lastmod'])) {
            $ts = strtotime((string) $u['lastmod']);
            if ($ts) {
                echo '    <lastmod>'.date('Y-m-d', $ts)."</lastmod>\n";
            }
        }
        echo '    <priority>'.$u['prioritas']."</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// =========================================================== META PER HALAMAN
$pengaturan = api_get($konfig['api'].'/settings', 600)['data'] ?? [];
$profil = api_get($konfig['api'].'/profil', 600)['data'] ?? [];

// Rapikan spasi berlebih dari nilai pengaturan (mis. "MA  Bustanul" -> "MA Bustanul")
$rapikan = fn (?string $s): string => trim((string) preg_replace('/\s+/', ' ', (string) $s));

$namaSitus = $rapikan($pengaturan['nama_sekolah'] ?? '') ?: "MA Bustanul Muta'allimin";
$moto = $rapikan($pengaturan['motto'] ?? '');
$logo = (string) ($pengaturan['logo_url'] ?? '');
$gambarSitus = $logo !== '' ? (str_starts_with($logo, 'http') ? $logo : $asal.$logo) : '';

$judul = $namaSitus.($moto !== '' ? ' — '.$moto : '');
$deskripsi = $rapikan($pengaturan['deskripsi_singkat'] ?? '')
    ?: $rapikan($profil['sejarah'] ?? '');
$deskripsi = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($deskripsi, ENT_QUOTES | ENT_HTML5, 'UTF-8')))), 0, 160);
$tipe = 'website';
$gambar = $gambarSitus;
$terbit = null;
$diubah = null;
$jsonLd = [];

// --- halaman statis
$halaman = [
    '/berita' => ['Berita & Pengumuman', 'Kabar terbaru, pengumuman, prestasi, dan testimoni lulusan '.$namaSitus.'.'],
    '/profil' => ['Profil Sekolah', 'Sejarah, visi, misi, dan identitas '.$namaSitus.'.'],
    '/jurusan' => ['Jurusan', 'Program keahlian dan jurusan yang tersedia di '.$namaSitus.'.'],
    '/layanan' => ['Layanan', 'Layanan dan fasilitas '.$namaSitus.' untuk peserta didik.'],
    '/kontak' => ['Kontak', 'Alamat, telepon, dan kontak '.$namaSitus.'.'],
    '/umum' => ['PPDB', 'Informasi penerimaan peserta didik baru '.$namaSitus.'.'],
];
foreach ($halaman as $rute => $info) {
    if ($jalur === $rute) {
        $judul = $info[0].' | '.$namaSitus;
        $deskripsi = mb_substr($info[1], 0, 160);
    }
}

// --- detail berita
$artikel = null;
if (preg_match('#^/berita/([a-z0-9\-]+)$#', $jalur, $cocok)) {
    $hasil = api_get($konfig['api'].'/berita/'.rawurlencode($cocok[1]), 120);
    $artikel = $hasil['data'] ?? null;

    if (is_array($artikel)) {
        $judulSeo = trim((string) ($artikel['meta_judul'] ?? ''));
        $judul = $judulSeo !== ''
            ? $judulSeo
            : trim((string) ($artikel['judul'] ?? '')).' | '.$namaSitus;

        $deskripsi = mb_substr(trim((string) ($artikel['seo_deskripsi'] ?? '')), 0, 160);

        if (! empty($artikel['gambar_url'])) {
            $g = (string) $artikel['gambar_url'];
            $gambar = str_starts_with($g, 'http') ? $g : $asal.$g;
        }

        $tipe = 'article';
        $terbit = $artikel['tanggal'] ?? null;
        $diubah = $artikel['updated_at'] ?? $terbit;

        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => mb_substr(trim((string) ($artikel['judul'] ?? '')), 0, 110),
            'description' => $deskripsi,
            'image' => $gambar !== '' ? [$gambar] : [],
            'datePublished' => $terbit ? date('c', strtotime((string) $terbit)) : null,
            'dateModified' => $diubah ? date('c', strtotime((string) $diubah)) : null,
            'author' => ['@type' => 'Person', 'name' => trim((string) ($artikel['author_name'] ?? $namaSitus))],
            'publisher' => [
                '@type' => 'EducationalOrganization',
                'name' => $namaSitus,
                'logo' => $gambarSitus !== '' ? ['@type' => 'ImageObject', 'url' => $gambarSitus] : null,
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $asal.$jalur],
        ];

        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $asal.'/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Berita', 'item' => $asal.'/berita'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => mb_substr(trim((string) ($artikel['judul'] ?? '')), 0, 110), 'item' => $asal.$jalur],
            ],
        ];
    }
}

// --- identitas sekolah (selalu ada)
$jsonLd[] = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'EducationalOrganization',
    'name' => $namaSitus,
    'url' => $asal.'/',
    'logo' => $gambarSitus !== '' ? $gambarSitus : null,
    'description' => $deskripsi,
    'address' => trim((string) ($pengaturan['alamat'] ?? '')) ?: null,
    'telephone' => trim((string) ($pengaturan['telepon'] ?? '')) ?: null,
    'email' => trim((string) ($pengaturan['email'] ?? '')) ?: null,
    'sameAs' => array_values(array_filter([
        trim((string) ($pengaturan['facebook'] ?? '')) ?: null,
        trim((string) ($pengaturan['instagram'] ?? '')) ?: null,
        trim((string) ($pengaturan['youtube'] ?? '')) ?: null,
    ])),
]);

// ------------------------------------------------------------- susun <head>
$meta = [];
$meta[] = '<title>'.e($judul).'</title>';
$meta[] = '<meta name="description" content="'.e($deskripsi).'">';
$meta[] = '<link rel="canonical" href="'.e($asal.$jalur).'">';
$meta[] = '<meta name="robots" content="index, follow, max-image-preview:large">';
$meta[] = '<meta property="og:site_name" content="'.e($namaSitus).'">';
$meta[] = '<meta property="og:locale" content="id_ID">';
$meta[] = '<meta property="og:type" content="'.e($tipe).'">';
$meta[] = '<meta property="og:title" content="'.e($judul).'">';
$meta[] = '<meta property="og:description" content="'.e($deskripsi).'">';
$meta[] = '<meta property="og:url" content="'.e($asal.$jalur).'">';
if ($gambar !== '') {
    $meta[] = '<meta property="og:image" content="'.e($gambar).'">';
    $meta[] = '<meta property="og:image:alt" content="'.e($judul).'">';
    $meta[] = '<meta name="twitter:card" content="summary_large_image">';
    $meta[] = '<meta name="twitter:image" content="'.e($gambar).'">';
} else {
    $meta[] = '<meta name="twitter:card" content="summary">';
}
$meta[] = '<meta name="twitter:title" content="'.e($judul).'">';
$meta[] = '<meta name="twitter:description" content="'.e($deskripsi).'">';
if ($tipe === 'article') {
    if ($terbit) {
        $meta[] = '<meta property="article:published_time" content="'.e(date('c', strtotime((string) $terbit))).'">';
    }
    if (! empty($artikel['kategori'])) {
        $meta[] = '<meta property="article:section" content="'.e((string) $artikel['kategori']).'">';
    }
}
foreach ($jsonLd as $ld) {
    $bersih = json_encode(array_filter($ld, fn ($v) => $v !== null && $v !== []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($bersih) {
        $meta[] = '<script type="application/ld+json">'.$bersih.'</script>';
    }
}

// -------------------------------------------------------------- kirim HTML
$html = @file_get_contents(__DIR__.'/index.html');
if ($html === false) {
    http_response_code(500);
    exit('index.html tidak ditemukan');
}

// buang title/description bawaan agar tidak ganda
$html = preg_replace('#<title>.*?</title>#is', '', $html, 1);
$html = preg_replace('#<meta\s+name="description"[^>]*>#is', '', $html, 1);

$sisipan = implode("\n    ", $meta);
if (str_contains($html, '<!--SEO-->')) {
    $html = str_replace('<!--SEO-->', $sisipan, $html);
} else {
    $html = str_replace('</head>', $sisipan."\n  </head>", $html);
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=60');
echo $html;

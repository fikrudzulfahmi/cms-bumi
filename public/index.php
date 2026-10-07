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

// ------------------------------------------------- ISI HALAMAN DI DALAM HTML
/**
 * Menyusun isi halaman versi HTML biasa (bukan hasil JavaScript).
 *
 * Aplikasi ini SPA: HTML aslinya hanya kerangka kosong, sehingga crawler yang tidak
 * menjalankan JavaScript (WhatsApp, Facebook, Bing versi lama, perkakas AI, pengarsip)
 * hanya membaca meta tanpa isi. Blok ini menyuntikkan isi yang sama ke dalam
 * `<div id="app">` — dikirim ke SEMUA pengunjung, bukan hanya bot, jadi bukan
 * penyamaran. Saat aplikasi Vue hidup, blok ini digantikan tampilan interaktifnya.
 */
function susun_konten(string $jalur, ?array $artikel, string $nama, string $moto, string $asal, array $konfig, array $pengaturan = []): string
{
    $bersih_teks = fn (?string $s): string => trim((string) preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    $potong = fn (?string $s, int $n): string => mb_substr($bersih_teks($s), 0, $n);
    $url = fn (?string $g): string => $g ? (str_starts_with($g, 'http') ? $g : $asal.$g) : '';

    $b = [];
    $tgl = fn (?string $t): string => $t ? date('j F Y', strtotime($t)) : '';

    if ($jalur === '/') {
        $profil = api_get($konfig['api'].'/profil', 600)['data'] ?? [];
        $b[] = '<h1>'.e($nama).($moto !== '' ? ' — '.e($moto) : '').'</h1>';
        $ringkas = $potong($profil['sejarah'] ?? '', 500);
        if ($ringkas !== '') {
            $b[] = '<p>'.e($ringkas).'</p>';
        }
        $berita = api_get($konfig['api'].'/berita?limit=6', 300)['data'] ?? [];
        if ($berita) {
            $b[] = '<h2>Berita Terbaru</h2><ul>';
            foreach ($berita as $p) {
                $b[] = '<li><a href="/berita/'.e($p['slug']).'">'.e($p['judul']).'</a>'
                    .' <small>'.e($tgl($p['tanggal'] ?? null)).'</small>'
                    .($potong($p['ringkasan'] ?? '', 160) !== '' ? '<br><small>'.e($potong($p['ringkasan'] ?? '', 160)).'</small>' : '')
                    .'</li>';
            }
            $b[] = '</ul><p><a href="/berita">Lihat semua berita</a></p>';
        }
    } elseif ($jalur === '/berita') {
        $b[] = '<h1>Berita &amp; Pengumuman '.e($nama).'</h1>';
        $berita = api_get($konfig['api'].'/berita?limit=12', 300)['data'] ?? [];
        if ($berita) {
            $b[] = '<ul>';
            foreach ($berita as $p) {
                $b[] = '<li><a href="/berita/'.e($p['slug']).'">'.e($p['judul']).'</a>'
                    .' <small>'.e($tgl($p['tanggal'] ?? null)).'</small>'
                    .($potong($p['ringkasan'] ?? '', 200) !== '' ? '<br><small>'.e($potong($p['ringkasan'] ?? '', 200)).'</small>' : '')
                    .'</li>';
            }
            $b[] = '</ul>';
        }
    } elseif (is_array($artikel)) {
        // Halaman berita: inilah yang dicari orang di Google.
        $b[] = '<h1>'.e($artikel['judul'] ?? '').'</h1>';
        $info = array_filter([
            $tgl($artikel['tanggal'] ?? null),
            ! empty($artikel['author_name']) ? 'Oleh: '.$artikel['author_name'] : '',
            ((int) ($artikel['views'] ?? 0)) > 0 ? ((int) $artikel['views']).' pengunjung' : '',
        ]);
        if ($info) {
            $b[] = '<p><small>'.e(implode(' · ', $info)).'</small></p>';
        }
        if (! empty($artikel['gambar_url'])) {
            $srcset = trim((string) ($artikel['gambar_srcset'] ?? ''));
            $b[] = '<img src="'.e($url($artikel['gambar_url'])).'"'
                .($srcset !== '' ? ' srcset="'.e($srcset).'" sizes="(max-width: 896px) 100vw, 896px"' : '')
                .' alt="'.e($artikel['judul'] ?? '').'" width="1200" height="675" loading="eager" fetchpriority="high" decoding="async">';
        }
        // Isi artikel apa adanya (ditulis admin lewat editor), tag berisiko dibuang.
        $isi = (string) ($artikel['konten'] ?? '');
        $isi = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $isi) ?? '';
        $isi = preg_replace('#<(script|style|iframe|object|embed)[^>]*/?>#is', '', $isi) ?? '';
        // Gambar di dalam isi berada jauh di bawah layar -> lazy, dan ukuran
        // dicadangkan supaya tata letak tidak bergeser saat gambar masuk.
        $isi = preg_replace_callback('#<img\b([^>]*)>#i', function ($m) {
            $atribut = $m[1];
            $tambah = '';
            if (! preg_match('#\bloading=#i', $atribut)) {
                $tambah .= ' loading="lazy"';
            }
            if (! preg_match('#\bdecoding=#i', $atribut)) {
                $tambah .= ' decoding="async"';
            }
            if (! preg_match('#\bwidth=#i', $atribut)) {
                $tambah .= ' style="aspect-ratio:16/9;object-fit:cover;max-width:100%"';
            }
            return '<img'.$atribut.$tambah.'>';
        }, $isi) ?? $isi;
        if (trim(strip_tags($isi)) === '') {
            $isi = '<p>'.e($potong($artikel['ringkasan'] ?? '', 400)).'</p>';
        }
        $b[] = $isi;

        $lain = api_get($konfig['api'].'/berita?limit=6', 300)['data'] ?? [];
        $lain = array_values(array_filter($lain, fn ($p) => ($p['slug'] ?? '') !== ($artikel['slug'] ?? '')));
        if ($lain) {
            $b[] = '<h2>Berita Lainnya</h2><ul>';
            foreach (array_slice($lain, 0, 5) as $p) {
                $b[] = '<li><a href="/berita/'.e($p['slug']).'">'.e($p['judul']).'</a></li>';
            }
            $b[] = '</ul>';
        }
    } elseif ($jalur === '/jurusan') {
        $b[] = '<h1>Jurusan di '.e($nama).'</h1>';
        $jurusan = api_get($konfig['api'].'/jurusan', 600)['data'] ?? [];
        if ($jurusan) {
            $b[] = '<ul>';
            foreach ($jurusan as $j) {
                $b[] = '<li><a href="/jurusan/'.e($j['slug']).'">'.e($j['nama']).'</a>'
                    .($potong($j['deskripsi'] ?? '', 160) !== '' ? '<br><small>'.e($potong($j['deskripsi'] ?? '', 160)).'</small>' : '').'</li>';
            }
            $b[] = '</ul>';
        }
    } elseif (preg_match('#^/jurusan/([a-z0-9\-]+)$#', $jalur, $c)) {
        $j = api_get($konfig['api'].'/jurusan/'.rawurlencode($c[1]), 600)['data'] ?? null;
        if (is_array($j)) {
            $b[] = '<h1>'.e($j['nama'] ?? '').'</h1>';
            $isi = (string) ($j['deskripsi'] ?? '');
            $b[] = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $isi) ?: '<p>'.e($potong($isi, 400)).'</p>';
            $b[] = '<p><a href="/jurusan">Lihat semua jurusan</a></p>';
        }
    } elseif ($jalur === '/layanan') {
        $b[] = '<h1>Layanan &amp; Fasilitas '.e($nama).'</h1>';
        $fasilitas = api_get($konfig['api'].'/fasilitas', 600)['data'] ?? [];
        if ($fasilitas) {
            $b[] = '<ul>';
            foreach ($fasilitas as $f) {
                $b[] = '<li><strong>'.e($f['nama'] ?? '').'</strong>'
                    .($potong($f['deskripsi'] ?? '', 200) !== '' ? ' — '.e($potong($f['deskripsi'] ?? '', 200)) : '').'</li>';
            }
            $b[] = '</ul>';
        }
    } elseif ($jalur === '/kontak') {
        $b[] = '<h1>Kontak '.e($nama).'</h1>';
        $kontak = array_filter([
            $potong($pengaturan['alamat'] ?? '', 200),
            $potong($pengaturan['telepon'] ?? '', 60),
            $potong($pengaturan['email'] ?? '', 120),
        ]);
        if ($kontak) {
            $b[] = '<ul><li>'.implode('</li><li>', array_map('e', $kontak)).'</li></ul>';
        }
    } elseif ($jalur === '/profil') {
        $profil = api_get($konfig['api'].'/profil', 600)['data'] ?? [];
        $b[] = '<h1>Profil '.e($nama).'</h1>';
        foreach (['sejarah' => 'Sejarah', 'visi' => 'Visi', 'misi' => 'Misi'] as $kolom => $label) {
            $t = (string) ($profil[$kolom] ?? '');
            $t = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $t) ?? '';
            if (trim(strip_tags($t)) !== '') {
                $b[] = '<h2>'.$label.'</h2>'.$t;
            }
        }
    }

    if (! $b) {
        return '';
    }

    // Gaya mandiri: blok ini tampil sekejap sebelum aplikasi Vue menggantinya.
    $gaya = '<style>'
        .'#app>.seo-html{max-width:56rem;margin:0 auto;padding:2rem 1rem;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#334155;line-height:1.7}'
        .'#app>.seo-html h1{font-size:1.9rem;line-height:1.25;margin:0 0 1rem;color:#1e293b}'
        .'#app>.seo-html h2{font-size:1.25rem;margin:1.75rem 0 .6rem;color:#1e293b}'
        .'#app>.seo-html img{max-width:100%;height:auto;border-radius:1rem;margin:1rem 0}'
        .'#app>.seo-html ul{padding-left:1.25rem}'
        .'#app>.seo-html a{color:#0f766e}'
        .'#app>.seo-html small{color:#64748b}'
        .'</style>';

    return $gaya.'<div class="seo-html">'.implode("\n", $b).'</div>';
}

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

// Sisipkan isi halaman ke dalam #app: crawler tanpa JavaScript ikut membaca isinya,
// dan pengunjung melihat isi lebih cepat. Vue menggantinya saat aplikasi hidup.
$konten = susun_konten($jalur, $artikel, $namaSitus, $moto, $asal, $konfig, is_array($pengaturan) ? $pengaturan : []);
if ($konten !== '' && preg_match('#<div id="app">\s*</div>#i', $html)) {
    $html = preg_replace('#<div id="app">\s*</div>#i', '<div id="app">'.$konten.'</div>', $html, 1);
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=60');
echo $html;

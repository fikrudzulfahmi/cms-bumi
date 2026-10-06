<?php

/**
 * CONTOH konfigurasi SEO untuk front controller (index.php).
 *
 * Berkas aslinya (seo.config.php) DIBUAT OTOMATIS saat deploy oleh GitHub Actions
 * dari repository secret, dan tidak disimpan di repo (lihat .gitignore) supaya
 * nama domain serta alamat API tidak ikut tersebar.
 *
 * Untuk menguji di komputer sendiri, salin berkas ini menjadi seo.config.php
 * lalu isi sesuai API pengembangan.
 */
return [
    // Alamat API tanpa garis miring di akhir.
    'api' => 'http://127.0.0.1:8011/api',

    // Alamat situs depan yang dipakai untuk canonical & Open Graph.
    // Boleh dikosongkan agar memakai domain dari permintaan.
    'situs' => 'http://localhost:8080',
];

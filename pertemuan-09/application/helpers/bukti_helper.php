<?php
// P9: helper berkas bukti pembayaran (folder uploads/bayar/).

const BUKTI_POLA_NAMA = '/^bayar_\d{14}_[a-f0-9]{16}\.(jpg|png|webp)$/';
const BUKTI_POLA_KODE_BAYAR = '#^PAY/\d{8}/\d{2}$#';

function bukti_folder(): string
{
    return dirname(APPPATH) . DIRECTORY_SEPARATOR . 'uploads'
        . DIRECTORY_SEPARATOR . 'bayar' . DIRECTORY_SEPARATOR;
}

// Format kode pembayaran yang dihasilkan checkout P7: PAY/YYYYMMDD/NN.
function bukti_kode_bayar_valid(string $kodeBayar): bool
{
    return preg_match(BUKTI_POLA_KODE_BAYAR, $kodeBayar) === 1;
}

// Hanya menghapus berkas yang namanya sesuai pola hasil unggah P9.
function hapus_bukti_pembayaran(?string $namaBerkas): void
{
    if (!is_string($namaBerkas) || preg_match(BUKTI_POLA_NAMA, $namaBerkas) !== 1) {
        return;
    }
    $path = bukti_folder() . $namaBerkas;
    if (is_file($path)) {
        @unlink($path);
    }
}

// Mengirim gambar bukti ke browser. Dipanggil hanya setelah pengendalian akses lolos.
function kirim_bukti_pembayaran(?string $namaBerkas): never
{
    if (!is_string($namaBerkas) || preg_match(BUKTI_POLA_NAMA, $namaBerkas) !== 1) {
        http_response_code(404);
        exit('Bukti pembayaran belum tersedia.');
    }
    $path = bukti_folder() . $namaBerkas;
    if (!is_file($path)) {
        http_response_code(404);
        exit('Berkas bukti pembayaran tidak ditemukan.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        http_response_code(404);
        exit('Berkas bukti pembayaran tidak valid.');
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($path);
    exit;
}

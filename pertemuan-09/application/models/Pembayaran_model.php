<?php
// P9: Model pembayaran DP (tabel t_bayar) dan relasinya dengan transaksi (t_sewa, t_pilih).
// Semua query memakai prepared statement. Nominal TIDAK pernah diterima dari form.

// Pesan pada exception ini aman ditampilkan kepada pengguna.
class Pembayaran_exception extends RuntimeException
{
}

class Pembayaran_model
{
    public const PESAN_TIDAK_DITEMUKAN = 'Pembayaran tidak ditemukan.';
    public const PESAN_SUDAH_DIPROSES = 'Pembayaran ini sudah diverifikasi dan tidak dapat diproses kembali.';
    public const PESAN_TANPA_BUKTI = 'Bukti pembayaran belum diunggah, sehingga belum dapat diverifikasi.';

    private mysqli $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    private function jalankan(string $sql, string $tipe = '', array $param = []): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Query pembayaran gagal disiapkan.');
        }
        if ($tipe !== '') {
            $stmt->bind_param($tipe, ...$param);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Query pembayaran gagal dijalankan.');
        }
        return $stmt;
    }

    private function ambilSemua(string $sql, string $tipe = '', array $param = []): array
    {
        $stmt = $this->jalankan($sql, $tipe, $param);
        $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $hasil;
    }

    private function ambilSatu(string $sql, string $tipe = '', array $param = []): ?array
    {
        $stmt = $this->jalankan($sql, $tipe, $param);
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $baris ?: null;
    }

    // Menjalankan INSERT/UPDATE dan mengembalikan jumlah baris terdampak.
    private function ubah(string $sql, string $tipe, array $param): int
    {
        $stmt = $this->jalankan($sql, $tipe, $param);
        $terdampak = $stmt->affected_rows;
        $stmt->close();
        return (int) $terdampak;
    }

    // Rincian kendaraan pada transaksi beserta total sewa (hrg_sewa x n_hari).
    private function lengkapiDetail(array $pembayaran): array
    {
        $detail = $this->ambilSemua(
            "SELECT p.no_plat, p.hrg_sewa, p.n_hari, m.nm_mobil
            FROM t_pilih p
            INNER JOIN t_mobil m ON m.no_plat = p.no_plat
            WHERE p.kode_sewa = ?
            ORDER BY m.nm_mobil ASC",
            's',
            [$pembayaran['kode_sewa']]
        );
        $total = 0;
        foreach ($detail as $item) {
            $total += (int) $item['hrg_sewa'] * (int) $item['n_hari'];
        }
        $pembayaran['detail'] = $detail;
        $pembayaran['total_sewa'] = $total;
        return $pembayaran;
    }

    // ===== Sisi penyewa =====

    // Dipakai checkout untuk mengarahkan penyewa ke halaman pembayaran DP-nya.
    public function findKodeDpBySewa(string $kodeSewa, string $userPlg): ?string
    {
        $baris = $this->ambilSatu(
            "SELECT b.kode_bayar
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            WHERE b.kode_sewa = ? AND s.user_plg = ? AND b.jns_byr = 'dp'
            LIMIT 1",
            'ss',
            [$kodeSewa, $userPlg]
        );
        return $baris === null ? null : (string) $baris['kode_bayar'];
    }

    public function getByPenyewa(string $userPlg): array
    {
        return $this->ambilSemua(
            "SELECT b.kode_bayar, b.kode_sewa, b.nominal, b.sttus_byr, b.ft_byr,
                b.tgl_wkt_byr, b.tgl_wkt_ver, s.status_sewa, s.tgl_mulai, s.tgl_selesai
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            WHERE s.user_plg = ? AND b.jns_byr = 'dp'
            ORDER BY b.kode_bayar DESC",
            's',
            [$userPlg]
        );
    }

    // Hanya mengembalikan pembayaran milik penyewa tersebut (kepemilikan lewat t_sewa.user_plg).
    public function findMilikPenyewa(string $kodeBayar, string $userPlg): ?array
    {
        $baris = $this->ambilSatu(
            "SELECT b.kode_bayar, b.kode_sewa, b.mtode_byr, b.jns_byr, b.nominal,
                b.sttus_byr, b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver,
                s.status_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            WHERE b.kode_bayar = ? AND s.user_plg = ? AND b.jns_byr = 'dp'
            LIMIT 1",
            'ss',
            [$kodeBayar, $userPlg]
        );
        return $baris === null ? null : $this->lengkapiDetail($baris);
    }

    // Memperbarui record DP yang sudah ada (bukan membuat record baru).
    // Mengembalikan nama berkas lama (atau null) agar controller dapat menghapusnya.
    public function simpanBukti(string $kodeBayar, string $userPlg, string $namaBerkas): ?string
    {
        $this->db->begin_transaction();
        try {
            $baris = $this->ambilSatu(
                "SELECT b.ft_byr, b.sttus_byr, s.status_sewa
                FROM t_bayar b
                INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
                WHERE b.kode_bayar = ? AND s.user_plg = ? AND b.jns_byr = 'dp'
                LIMIT 1
                FOR UPDATE",
                'ss',
                [$kodeBayar, $userPlg]
            );
            if ($baris === null) {
                throw new Pembayaran_exception(self::PESAN_TIDAK_DITEMUKAN);
            }
            if ($baris['sttus_byr'] !== 'pending' || $baris['status_sewa'] !== 'diajukan') {
                throw new Pembayaran_exception(self::PESAN_SUDAH_DIPROSES);
            }
            $terdampak = $this->ubah(
                "UPDATE t_bayar
                SET ft_byr = ?, tgl_wkt_byr = NOW()
                WHERE kode_bayar = ? AND jns_byr = 'dp' AND sttus_byr = 'pending'",
                'ss',
                [$namaBerkas, $kodeBayar]
            );
            if ($terdampak !== 1) {
                throw new RuntimeException('Pembaruan bukti pembayaran tidak mengubah data.');
            }
            $this->db->commit();
            return $baris['ft_byr'] === null || $baris['ft_byr'] === ''
                ? null
                : (string) $baris['ft_byr'];
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // ===== Sisi admin =====

    private const SELECT_ADMIN =
        "SELECT b.kode_bayar, b.kode_sewa, b.mtode_byr, b.jns_byr, b.nominal,
            b.sttus_byr, b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver,
            s.status_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil,
            s.user_plg, p.nm_lngkp, p.no_hp
        FROM t_bayar b
        INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
        INNER JOIN t_penyewa p ON p.user_plg = s.user_plg ";

    // Pembayaran DP yang masih menunggu keputusan admin (yang sudah ada bukti diurutkan lebih dulu).
    public function getMenungguVerifikasi(): array
    {
        return $this->ambilSemua(
            self::SELECT_ADMIN .
            "WHERE b.jns_byr = 'dp' AND b.sttus_byr = 'pending' AND s.status_sewa = 'diajukan'
            ORDER BY (b.ft_byr IS NULL) ASC, b.tgl_wkt_byr ASC, b.kode_bayar ASC"
        );
    }

    public function getRiwayatVerifikasi(int $batas = 50): array
    {
        return $this->ambilSemua(
            self::SELECT_ADMIN .
            "WHERE b.jns_byr = 'dp' AND b.sttus_byr IN ('valid', 'invalid')
            ORDER BY b.tgl_wkt_ver DESC, b.kode_bayar DESC
            LIMIT ?",
            'i',
            [$batas]
        );
    }

    public function findUntukAdmin(string $kodeBayar): ?array
    {
        $baris = $this->ambilSatu(
            self::SELECT_ADMIN . "WHERE b.kode_bayar = ? AND b.jns_byr = 'dp' LIMIT 1",
            's',
            [$kodeBayar]
        );
        return $baris === null ? null : $this->lengkapiDetail($baris);
    }

    // Verifikasi: t_bayar dan t_sewa diubah dalam SATU transaksi database.
    // terima -> sttus_byr=valid,   status_sewa=disetujui
    // tolak  -> sttus_byr=invalid, status_sewa=ditolak
    public function verifikasi(string $kodeBayar, string $keputusan): void
    {
        $peta = [
            'terima' => ['valid', 'disetujui'],
            'tolak' => ['invalid', 'ditolak'],
        ];
        if (!isset($peta[$keputusan])) {
            throw new Pembayaran_exception('Keputusan verifikasi tidak valid.');
        }
        [$statusBayar, $statusSewa] = $peta[$keputusan];

        $this->db->begin_transaction();
        try {
            $baris = $this->ambilSatu(
                "SELECT b.kode_sewa, b.ft_byr, b.sttus_byr, s.status_sewa
                FROM t_bayar b
                INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
                WHERE b.kode_bayar = ? AND b.jns_byr = 'dp'
                LIMIT 1
                FOR UPDATE",
                's',
                [$kodeBayar]
            );
            if ($baris === null) {
                throw new Pembayaran_exception(self::PESAN_TIDAK_DITEMUKAN);
            }
            if ($baris['sttus_byr'] !== 'pending' || $baris['status_sewa'] !== 'diajukan') {
                throw new Pembayaran_exception(self::PESAN_SUDAH_DIPROSES);
            }
            if ($baris['ft_byr'] === null || $baris['ft_byr'] === '') {
                throw new Pembayaran_exception(self::PESAN_TANPA_BUKTI);
            }

            $a = $this->ubah(
                "UPDATE t_bayar
                SET sttus_byr = ?, tgl_wkt_ver = NOW()
                WHERE kode_bayar = ? AND jns_byr = 'dp' AND sttus_byr = 'pending'",
                'ss',
                [$statusBayar, $kodeBayar]
            );
            $b = $this->ubah(
                "UPDATE t_sewa
                SET status_sewa = ?
                WHERE kode_sewa = ? AND status_sewa = 'diajukan'",
                'ss',
                [$statusSewa, $baris['kode_sewa']]
            );
            if ($a !== 1 || $b !== 1) {
                throw new RuntimeException('Perubahan status tidak konsisten; verifikasi dibatalkan.');
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}

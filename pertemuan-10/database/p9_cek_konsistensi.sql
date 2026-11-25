-- P9 - Query bantu pengujian. Tidak mengubah struktur basis data.
USE db_dpwl_steiven;

-- 0) Pastikan nilai ENUM/kolom sesuai asumsi kode P9 (sttus_byr: pending/valid/invalid;
--    status_sewa: keranjang/diajukan/disetujui/ditolak; ft_byr cukup untuk 41 karakter).
SHOW COLUMNS FROM t_bayar;
SHOW COLUMNS FROM t_sewa;

-- 1) Konsistensi t_bayar <-> t_sewa setelah setiap proses.
SELECT b.kode_bayar, b.sttus_byr, b.ft_byr IS NOT NULL AS ada_bukti,
       b.tgl_wkt_byr, b.tgl_wkt_ver, s.kode_sewa, s.status_sewa
FROM t_bayar b
JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
ORDER BY b.kode_bayar DESC;

-- 2) Harus KOSONG: pembayaran valid tetapi transaksi belum disetujui (atau sebaliknya).
SELECT b.kode_bayar, b.sttus_byr, s.status_sewa
FROM t_bayar b
JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE b.jns_byr = 'dp'
  AND ( (b.sttus_byr = 'valid'   AND s.status_sewa <> 'disetujui')
     OR (b.sttus_byr = 'invalid' AND s.status_sewa <> 'ditolak')
     OR (b.sttus_byr = 'pending' AND s.status_sewa IN ('disetujui','ditolak'))
     OR (b.sttus_byr IN ('valid','invalid') AND (b.tgl_wkt_ver IS NULL OR b.ft_byr IS NULL)) );

-- 3) Harus KOSONG: satu transaksi memiliki lebih dari satu record DP (P9 tidak boleh membuat DP baru).
SELECT kode_sewa, COUNT(*) AS jumlah_dp
FROM t_bayar WHERE jns_byr = 'dp'
GROUP BY kode_sewa HAVING COUNT(*) > 1;

-- 4) Harus KOSONG: nominal DP tidak sama dengan 30% total sewa.
SELECT b.kode_bayar, b.nominal, ROUND(SUM(p.hrg_sewa * p.n_hari) * 30 / 100) AS dp_seharusnya
FROM t_bayar b
JOIN t_pilih p ON p.kode_sewa = b.kode_sewa
WHERE b.jns_byr = 'dp'
GROUP BY b.kode_bayar, b.nominal
HAVING b.nominal <> ROUND(SUM(p.hrg_sewa * p.n_hari) * 30 / 100);

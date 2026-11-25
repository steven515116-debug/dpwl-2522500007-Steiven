-- P10 - Menambahkan nilai ENUM 'lunas' pada t_sewa.status_sewa.
-- Jalankan SATU kali saja. Backup basis data terlebih dahulu.
-- Ganti nama basis data sesuai milik Anda (mis. db_dpwl_steiven).
-- USE db_dpwl_steiven;

-- 1) Periksa kondisi awal (harus belum memuat 'lunas').
SHOW COLUMNS FROM t_sewa LIKE 'status_sewa';

-- 2) Ubah struktur kolom: nilai lama dipertahankan, 'lunas' ditambahkan.
ALTER TABLE `t_sewa` CHANGE `status_sewa` `status_sewa`
  ENUM('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang','lunas')
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'keranjang';

-- 3) Verifikasi: harus memuat 8 nilai ENUM termasuk 'lunas'.
SHOW COLUMNS FROM t_sewa LIKE 'status_sewa';

-- ---------------------------------------------------------------
-- Query bantu pengujian P10 (tidak mengubah data)
-- ---------------------------------------------------------------
-- A) Harus KOSONG: transaksi lunas tanpa record pelunasan, atau sebaliknya.
SELECT s.kode_sewa, s.status_sewa
FROM t_sewa s
WHERE (s.status_sewa = 'lunas' AND NOT EXISTS (
          SELECT 1 FROM t_bayar b WHERE b.kode_sewa = s.kode_sewa AND b.jns_byr = 'pelunasan'))
   OR (s.status_sewa <> 'lunas' AND EXISTS (
          SELECT 1 FROM t_bayar b WHERE b.kode_sewa = s.kode_sewa AND b.jns_byr = 'pelunasan'));

-- B) Harus KOSONG: lebih dari satu record pelunasan pada satu transaksi.
SELECT kode_sewa, COUNT(*) AS jumlah
FROM t_bayar WHERE jns_byr = 'pelunasan'
GROUP BY kode_sewa HAVING COUNT(*) > 1;

-- C) Harus KOSONG: transaksi lunas yang total valid-nya tidak sama dengan total transaksi.
SELECT s.kode_sewa,
       (SELECT SUM(tp.hrg_sewa * tp.n_hari) FROM t_pilih tp WHERE tp.kode_sewa = s.kode_sewa) AS total_transaksi,
       (SELECT SUM(b.nominal) FROM t_bayar b WHERE b.kode_sewa = s.kode_sewa AND b.sttus_byr = 'valid') AS total_valid
FROM t_sewa s
WHERE s.status_sewa = 'lunas'
GROUP BY s.kode_sewa
HAVING total_transaksi <> total_valid;

-- D) Pelunasan tunai tidak boleh memiliki bukti; transfer wajib memiliki bukti.
SELECT kode_bayar, mtode_byr, ft_byr FROM t_bayar
WHERE jns_byr = 'pelunasan'
  AND ((mtode_byr = 'tunai' AND ft_byr IS NOT NULL) OR (mtode_byr = 'transfer' AND ft_byr IS NULL));

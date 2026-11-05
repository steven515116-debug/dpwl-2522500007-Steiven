-- UTS P8 DPWL - berkas SQL pendamping untuk fitur Manajemen Penyewa.
-- CATATAN: berkas ini hanya memuat definisi tabel yang dipakai fitur P8 (t_admin, t_penyewa),
-- disusun dari kolom yang dipakai kode. Untuk pengumpulan, GANTI/TIMPA dengan hasil
-- Export penuh basis data db_dpwl_steiven dari phpMyAdmin (Export > Custom > SQL,
-- sertakan "Add DROP TABLE" dan data), karena tabel lain (t_mobil, t_sewa, t_pilih, t_bayar)
-- tidak terdapat di sini.

CREATE DATABASE IF NOT EXISTS db_dpwl_steiven CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_dpwl_steiven;

CREATE TABLE IF NOT EXISTS t_admin (
  username    VARCHAR(15)  NOT NULL,
  password    VARCHAR(255) NOT NULL,
  status_akun ENUM('aktif','nonaktif') NOT NULL,
  PRIMARY KEY (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_penyewa (
  user_plg   VARCHAR(15)  NOT NULL,
  sandi      VARCHAR(255) NOT NULL,
  nik        CHAR(16)     NOT NULL,
  nm_lngkp   VARCHAR(100) NOT NULL,
  no_hp      VARCHAR(15)  NOT NULL,
  almt_lngkp VARCHAR(300) NOT NULL,
  ft_ktp     VARCHAR(100) DEFAULT NULL,
  ft_sim_a   VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (user_plg)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<?php
// P9: daftar verifikasi pembayaran (admin). View hanya menampilkan data dari Controller.
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';
$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rupiah = static fn($n): string => 'Rp ' . number_format((float) $n, 0, ',', '.');
$tanggal = static function ($v): string {
    $t = $v === null || $v === '' ? false : strtotime((string) $v);
    return $t === false ? '-' : date('d-m-Y H:i', $t);
};
$menunggu = is_array($menunggu ?? null) ? $menunggu : [];
$riwayat = is_array($riwayat ?? null) ? $riwayat : [];
$flash = $flash ?? null;
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';
$urlDetail = static fn(string $kode): string => site_url('admin/pembayaran/detail?kode_bayar=' . rawurlencode($kode));
?>
<div class="content-wrapper">
<section class="content-header">
<h1>Verifikasi Pembayaran <small>DP 30% transaksi penyewaan</small></h1>
<ol class="breadcrumb">
<li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
<li class="active">Verifikasi Pembayaran</li>
</ol>
</section>
<section class="content">
<?php if (is_array($flash) && !empty($flash['message'])): ?>
<div class="alert <?= $e($flashClass) ?> alert-dismissible" role="alert">
<button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
<?= $e($flash['message']) ?>
</div>
<?php endif; ?>

<div class="box box-warning">
<div class="box-header with-border">
<h3 class="box-title">Menunggu Verifikasi (<?= count($menunggu) ?>)</h3>
</div>
<div class="box-body table-responsive">
<table class="table table-bordered table-hover js-data-table">
<thead>
<tr>
<th>No</th>
<th>Kode Pembayaran</th>
<th>Kode Transaksi</th>
<th>Penyewa</th>
<th>Nominal DP</th>
<th>Waktu Unggah</th>
<th>Bukti</th>
<th class="no-sort">Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($menunggu as $i => $row): ?>
<?php $adaBukti = !empty($row['ft_byr']); ?>
<tr>
<td><?= $i + 1 ?></td>
<td><?= $e($row['kode_bayar']) ?></td>
<td><?= $e($row['kode_sewa']) ?></td>
<td><?= $e($row['nm_lngkp']) ?><br><small class="text-muted"><?= $e($row['user_plg']) ?></small></td>
<td class="text-right"><?= $e($rupiah($row['nominal'])) ?></td>
<td><?= $adaBukti ? $e($tanggal($row['tgl_wkt_byr'])) : '-' ?></td>
<td>
<?php if ($adaBukti): ?>
<span class="label label-success">Sudah diunggah</span>
<?php else: ?>
<span class="label label-default">Belum diunggah</span>
<?php endif; ?>
</td>
<td><a class="btn btn-primary btn-sm" href="<?= $e($urlDetail((string) $row['kode_bayar'])) ?>"><i class="fa fa-search"></i> Periksa</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if (count($menunggu) === 0): ?>
<p class="text-muted">Tidak ada pembayaran yang menunggu verifikasi.</p>
<?php endif; ?>
</div>
</div>

<div class="box box-default">
<div class="box-header with-border">
<h3 class="box-title">Riwayat Verifikasi (50 terakhir)</h3>
</div>
<div class="box-body table-responsive">
<table class="table table-bordered table-hover js-data-table">
<thead>
<tr>
<th>Kode Pembayaran</th>
<th>Kode Transaksi</th>
<th>Penyewa</th>
<th>Nominal DP</th>
<th>Status Bayar</th>
<th>Status Transaksi</th>
<th>Waktu Verifikasi</th>
<th class="no-sort">Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($riwayat as $row): ?>
<tr>
<td><?= $e($row['kode_bayar']) ?></td>
<td><?= $e($row['kode_sewa']) ?></td>
<td><?= $e($row['nm_lngkp']) ?></td>
<td class="text-right"><?= $e($rupiah($row['nominal'])) ?></td>
<td><span class="label label-<?= $row['sttus_byr'] === 'valid' ? 'success' : 'danger' ?>"><?= $e($row['sttus_byr']) ?></span></td>
<td><?= $e($row['status_sewa']) ?></td>
<td><?= $e($tanggal($row['tgl_wkt_ver'])) ?></td>
<td><a class="btn btn-default btn-sm" href="<?= $e($urlDetail((string) $row['kode_bayar'])) ?>">Detail</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</section>
</div>
<?php require APPPATH . '/views/templates/footer.php'; ?>

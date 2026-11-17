<?php
// P9: detail dan keputusan verifikasi satu pembayaran (admin).
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';
$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rupiah = static fn($n): string => 'Rp ' . number_format((float) $n, 0, ',', '.');
$tanggal = static function ($v): string {
    $t = $v === null || $v === '' ? false : strtotime((string) $v);
    return $t === false ? '-' : date('d-m-Y H:i:s', $t) . ' WIB';
};
$p = is_array($pembayaran ?? null) ? $pembayaran : [];
$flash = $flash ?? null;
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';
$kodeBayar = (string) ($p['kode_bayar'] ?? '');
$sb = (string) ($p['sttus_byr'] ?? '');
$ss = (string) ($p['status_sewa'] ?? '');
$adaBukti = !empty($p['ft_byr']);
$bisaVerifikasi = $sb === 'pending' && $ss === 'diajukan' && $adaBukti;
$urlBukti = site_url('admin/pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar));
$labelBayar = ['pending' => 'warning', 'valid' => 'success', 'invalid' => 'danger'];
?>
<div class="content-wrapper">
<section class="content-header">
<h1>Detail Pembayaran <small><?= $e($kodeBayar) ?></small></h1>
<ol class="breadcrumb">
<li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
<li><a href="<?= $e(site_url('admin/pembayaran')) ?>">Verifikasi Pembayaran</a></li>
<li class="active">Detail</li>
</ol>
</section>
<section class="content">
<?php if (is_array($flash) && !empty($flash['message'])): ?>
<div class="alert <?= $e($flashClass) ?> alert-dismissible" role="alert">
<button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
<?= $e($flash['message']) ?>
</div>
<?php endif; ?>
<div class="row">
<div class="col-md-7">
<div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Informasi Transaksi dan Pembayaran</h3></div>
<div class="box-body">
<table class="table table-condensed">
<tr><th style="width:38%">Penyewa</th><td><?= $e($p['nm_lngkp'] ?? '-') ?> (<?= $e($p['user_plg'] ?? '-') ?>)</td></tr>
<tr><th>No. HP</th><td><?= $e($p['no_hp'] ?? '-') ?></td></tr>
<tr><th>Kode transaksi</th><td><?= $e($p['kode_sewa'] ?? '-') ?></td></tr>
<tr><th>Periode sewa</th><td><?= $e($p['tgl_mulai'] ?? '-') ?> s/d <?= $e($p['tgl_selesai'] ?? '-') ?>, ambil <?= $e(substr((string) ($p['wkt_ambil'] ?? ''), 0, 5)) ?> WIB</td></tr>
<tr><th>Kode pembayaran</th><td><?= $e($kodeBayar) ?></td></tr>
<tr><th>Metode / jenis</th><td><?= $e($p['mtode_byr'] ?? '-') ?> / <?= $e($p['jns_byr'] ?? '-') ?></td></tr>
<tr><th>Total sewa</th><td><?= $e($rupiah($p['total_sewa'] ?? 0)) ?></td></tr>
<tr><th>Nominal DP (30%)</th><td><strong><?= $e($rupiah($p['nominal'] ?? 0)) ?></strong></td></tr>
<tr><th>Waktu unggah bukti</th><td><?= $adaBukti ? $e($tanggal($p['tgl_wkt_byr'] ?? null)) : '-' ?></td></tr>
<tr><th>Status pembayaran</th><td><span class="label label-<?= $e($labelBayar[$sb] ?? 'default') ?>"><?= $e($sb) ?></span></td></tr>
<tr><th>Status transaksi</th><td><?= $e($ss) ?></td></tr>
<tr><th>Waktu verifikasi</th><td><?= !empty($p['tgl_wkt_ver']) ? $e($tanggal($p['tgl_wkt_ver'])) : '-' ?></td></tr>
</table>
</div>
</div>
<div class="box box-default">
<div class="box-header with-border"><h3 class="box-title">Kendaraan</h3></div>
<div class="box-body table-responsive no-padding">
<table class="table table-bordered">
<thead><tr><th>Kendaraan</th><th>No. Polisi</th><th class="text-right">Harga/Hari</th><th>Durasi</th><th class="text-right">Subtotal</th></tr></thead>
<tbody>
<?php foreach (($p['detail'] ?? []) as $item): ?>
<tr>
<td><?= $e($item['nm_mobil'] ?? '-') ?></td>
<td><?= $e($item['no_plat'] ?? '-') ?></td>
<td class="text-right"><?= $e($rupiah($item['hrg_sewa'] ?? 0)) ?></td>
<td><?= $e($item['n_hari'] ?? 0) ?> hari</td>
<td class="text-right"><?= $e($rupiah((int) ($item['hrg_sewa'] ?? 0) * (int) ($item['n_hari'] ?? 0))) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
<div class="col-md-5">
<div class="box box-warning">
<div class="box-header with-border"><h3 class="box-title">Bukti Transfer</h3></div>
<div class="box-body text-center">
<?php if ($adaBukti): ?>
<a href="<?= $e($urlBukti) ?>" target="_blank" rel="noopener">
<img src="<?= $e($urlBukti) ?>" alt="Bukti transfer <?= $e($kodeBayar) ?>" class="img-responsive center-block" style="max-height:480px">
</a>
<p class="help-block">Klik gambar untuk membuka ukuran penuh.</p>
<?php else: ?>
<p class="text-muted">Penyewa belum mengunggah bukti transfer.</p>
<?php endif; ?>
</div>
</div>
<div class="box box-success">
<div class="box-header with-border"><h3 class="box-title">Keputusan Verifikasi</h3></div>
<div class="box-body">
<?php if ($bisaVerifikasi): ?>
<form method="post" action="<?= $e(site_url('admin/pembayaran/verifikasi')) ?>">
<input type="hidden" name="kode_bayar" value="<?= $e($kodeBayar) ?>">
<button type="submit" name="keputusan" value="terima" class="btn btn-success"
onclick="return confirm('Terima pembayaran ini? Transaksi akan disetujui.');">
<i class="fa fa-check"></i> Terima Pembayaran
</button>
<button type="submit" name="keputusan" value="tolak" class="btn btn-danger"
onclick="return confirm('Tolak pembayaran ini? Transaksi akan ditolak.');">
<i class="fa fa-times"></i> Tolak Pembayaran
</button>
</form>
<?php elseif (!$adaBukti && $sb === 'pending'): ?>
<p class="text-warning">Verifikasi belum dapat dilakukan karena bukti pembayaran belum diunggah.</p>
<?php else: ?>
<p class="text-muted">Pembayaran ini sudah diverifikasi dan tidak dapat diproses kembali.</p>
<?php endif; ?>
</div>
</div>
<a class="btn btn-default" href="<?= $e(site_url('admin/pembayaran')) ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
</div>
</div>
</section>
</div>
<?php require APPPATH . '/views/templates/footer.php'; ?>

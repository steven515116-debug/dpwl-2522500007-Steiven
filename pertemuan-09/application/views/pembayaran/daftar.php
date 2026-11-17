<?php
// P9: daftar pembayaran DP milik penyewa. Tidak ada akses database di View.
$e = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$rupiah = static function ($nilai): string {
    return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};
$tanggal = static function ($nilai): string {
    $t = $nilai === null || $nilai === '' ? false : strtotime((string) $nilai);
    return $t === false ? '-' : date('d-m-Y H:i', $t);
};
$daftar = is_array($daftar ?? null) ? $daftar : [];
$badgeBayar = ['pending' => 'warning text-dark', 'valid' => 'success', 'invalid' => 'danger'];
$labelBayar = ['pending' => 'Pending', 'valid' => 'Valid', 'invalid' => 'Invalid'];
$badgeSewa = ['diajukan' => 'info text-dark', 'disetujui' => 'success', 'ditolak' => 'danger'];
require APPPATH . 'views/katalog/header.php';
require APPPATH . 'views/katalog/navbar.php';
?>
<section class="bg-success py-4">
<div class="container">
<h1 class="h2 text-white mt-3">Pembayaran Saya</h1>
<p class="text-white mb-0">Daftar pembayaran uang muka (DP) dari transaksi yang telah Anda ajukan.</p>
</div>
</section>
<section class="py-5">
<div class="container">
<?php if (count($daftar) === 0): ?>
<div class="alert alert-info">Belum ada pembayaran. Pembayaran DP dibuat otomatis setelah Anda melakukan checkout.</div>
<a class="btn btn-success" href="<?= $e(site_url('katalog')) ?>">Lihat Katalog</a>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle bg-white">
<thead class="table-light">
<tr>
<th scope="col">Kode Pembayaran</th>
<th scope="col">Kode Transaksi</th>
<th scope="col">Periode Sewa</th>
<th scope="col" class="text-end">Nominal DP</th>
<th scope="col">Bukti</th>
<th scope="col">Status Bayar</th>
<th scope="col">Status Transaksi</th>
<th scope="col"></th>
</tr>
</thead>
<tbody>
<?php foreach ($daftar as $row): ?>
<?php
$sb = (string) ($row['sttus_byr'] ?? '');
$ss = (string) ($row['status_sewa'] ?? '');
$adaBukti = !empty($row['ft_byr']);
?>
<tr>
<td class="text-nowrap"><?= $e($row['kode_bayar']) ?></td>
<td class="text-nowrap"><?= $e($row['kode_sewa']) ?></td>
<td class="text-nowrap"><?= $e($row['tgl_mulai']) ?> s/d <?= $e($row['tgl_selesai']) ?></td>
<td class="text-end text-nowrap"><?= $e($rupiah($row['nominal'])) ?></td>
<td><?= $adaBukti ? 'Sudah diunggah' : '<span class="text-muted">Belum diunggah</span>' ?></td>
<td><span class="badge bg-<?= $e($badgeBayar[$sb] ?? 'secondary') ?>"><?= $e($labelBayar[$sb] ?? $sb) ?></span></td>
<td><span class="badge bg-<?= $e($badgeSewa[$ss] ?? 'secondary') ?>"><?= $e(ucfirst($ss)) ?></span></td>
<td class="text-nowrap">
<a class="btn btn-sm btn-success" href="<?= $e(site_url('pembayaran/detail?kode_bayar=' . rawurlencode((string) $row['kode_bayar']))) ?>">
<?= ($sb === 'pending' && $ss === 'diajukan') ? ($adaBukti ? 'Lihat / Ganti Bukti' : 'Bayar') : 'Lihat' ?>
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>

<?php
// P9: halaman pembayaran DP satu transaksi (View saja; data berasal dari Controller/Model).
$e = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$rupiah = static function ($nilai): string {
    return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};
$tanggal = static function ($nilai): string {
    $t = $nilai === null || $nilai === '' ? false : strtotime((string) $nilai);
    return $t === false ? '-' : date('d-m-Y H:i', $t) . ' WIB';
};
$p = is_array($pembayaran ?? null) ? $pembayaran : [];
$kodeBayar = (string) ($p['kode_bayar'] ?? '');
$nominal = (int) ($p['nominal'] ?? 0);
$total = (int) ($p['total_sewa'] ?? 0);
$sisa = $total - $nominal;
$sb = (string) ($p['sttus_byr'] ?? '');
$ss = (string) ($p['status_sewa'] ?? '');
$adaBukti = !empty($p['ft_byr']);
$bisaUnggah = $sb === 'pending' && $ss === 'diajukan';
$maksMb = (int) ($maksMb ?? 2);
$badgeBayar = ['pending' => 'warning text-dark', 'valid' => 'success', 'invalid' => 'danger'];
$labelBayar = ['pending' => 'Pending - menunggu verifikasi admin', 'valid' => 'Valid - pembayaran diterima', 'invalid' => 'Invalid - pembayaran ditolak'];
$badgeSewa = ['diajukan' => 'info text-dark', 'disetujui' => 'success', 'ditolak' => 'danger'];
$urlBukti = site_url('pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar));
require APPPATH . 'views/katalog/header.php';
require APPPATH . 'views/katalog/navbar.php';
?>
<section class="bg-success py-4">
<div class="container">
<h1 class="h2 text-white mt-3">Pembayaran DP</h1>
<p class="text-white mb-0">Transfer sesuai nominal DP, lalu unggah bukti transfer.</p>
</div>
</section>
<section class="py-5">
<div class="container">
<div class="row g-4">
<div class="col-lg-7">
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-light"><h2 class="h5 mb-0">Rincian Pembayaran</h2></div>
<div class="card-body">
<dl class="row mb-0">
<dt class="col-sm-5">Kode pembayaran</dt>
<dd class="col-sm-7"><?= $e($kodeBayar) ?></dd>
<dt class="col-sm-5">Kode transaksi</dt>
<dd class="col-sm-7"><?= $e($p['kode_sewa'] ?? '-') ?></dd>
<dt class="col-sm-5">Periode sewa</dt>
<dd class="col-sm-7"><?= $e($p['tgl_mulai'] ?? '-') ?> s/d <?= $e($p['tgl_selesai'] ?? '-') ?>, ambil <?= $e(substr((string) ($p['wkt_ambil'] ?? ''), 0, 5)) ?> WIB</dd>
<dt class="col-sm-5">Metode pembayaran</dt>
<dd class="col-sm-7"><?= $e(ucfirst((string) ($p['mtode_byr'] ?? 'transfer'))) ?></dd>
<dt class="col-sm-5">Jenis pembayaran</dt>
<dd class="col-sm-7">Uang muka (DP 30%)</dd>
<dt class="col-sm-5">Total sewa</dt>
<dd class="col-sm-7"><?= $e($rupiah($total)) ?></dd>
<dt class="col-sm-5">Nominal DP yang harus ditransfer</dt>
<dd class="col-sm-7"><strong class="text-success fs-5"><?= $e($rupiah($nominal)) ?></strong></dd>
<dt class="col-sm-5">Sisa setelah DP</dt>
<dd class="col-sm-7"><?= $e($rupiah($sisa)) ?></dd>
<dt class="col-sm-5">Status pembayaran</dt>
<dd class="col-sm-7"><span class="badge bg-<?= $e($badgeBayar[$sb] ?? 'secondary') ?>"><?= $e($labelBayar[$sb] ?? $sb) ?></span></dd>
<dt class="col-sm-5">Status transaksi</dt>
<dd class="col-sm-7"><span class="badge bg-<?= $e($badgeSewa[$ss] ?? 'secondary') ?>"><?= $e(ucfirst($ss)) ?></span></dd>
<?php if (!empty($p['tgl_wkt_ver'])): ?>
<dt class="col-sm-5">Waktu verifikasi</dt>
<dd class="col-sm-7"><?= $e($tanggal($p['tgl_wkt_ver'])) ?></dd>
<?php endif; ?>
</dl>
</div>
</div>
<div class="table-responsive">
<table class="table table-bordered align-middle bg-white mb-0">
<thead class="table-light">
<tr>
<th scope="col">Kendaraan</th>
<th scope="col">No. Polisi</th>
<th scope="col" class="text-end">Harga/Hari</th>
<th scope="col" class="text-center">Durasi</th>
<th scope="col" class="text-end">Subtotal</th>
</tr>
</thead>
<tbody>
<?php foreach (($p['detail'] ?? []) as $item): ?>
<tr>
<td><?= $e($item['nm_mobil'] ?? '-') ?></td>
<td><?= $e($item['no_plat'] ?? '-') ?></td>
<td class="text-end text-nowrap"><?= $e($rupiah($item['hrg_sewa'] ?? 0)) ?></td>
<td class="text-center"><?= $e($item['n_hari'] ?? 0) ?> hari</td>
<td class="text-end text-nowrap"><?= $e($rupiah((int) ($item['hrg_sewa'] ?? 0) * (int) ($item['n_hari'] ?? 0))) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<div class="col-lg-5">
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-light"><h2 class="h5 mb-0">Bukti Transfer</h2></div>
<div class="card-body">
<?php if ($adaBukti): ?>
<p class="small text-muted mb-2">Diunggah pada <?= $e($tanggal($p['tgl_wkt_byr'] ?? null)) ?></p>
<a href="<?= $e($urlBukti) ?>" target="_blank" rel="noopener">
<img src="<?= $e($urlBukti) ?>" alt="Bukti transfer <?= $e($kodeBayar) ?>" class="img-fluid border rounded mb-3">
</a>
<?php else: ?>
<div class="alert alert-warning mb-3">Bukti transfer belum diunggah.</div>
<?php endif; ?>

<?php if ($bisaUnggah): ?>
<form method="post" enctype="multipart/form-data" action="<?= $e(site_url('pembayaran/unggah')) ?>" id="formBukti">
<input type="hidden" name="kode_bayar" value="<?= $e($kodeBayar) ?>">
<div class="mb-3">
<label for="ft_byr" class="form-label"><?= $adaBukti ? 'Ganti bukti transfer' : 'Pilih bukti transfer' ?></label>
<input type="file" class="form-control" id="ft_byr" name="ft_byr" accept="image/jpeg,image/png,image/webp" required>
<div class="form-text">Format JPG, PNG, atau WEBP. Maksimal <?= $e($maksMb) ?> MB.</div>
</div>
<div id="previewBaru" class="mb-3" style="display:none">
<img id="previewBaruImg" alt="Pratinjau bukti baru" class="img-fluid border rounded">
</div>
<button type="submit" class="btn btn-success"><?= $adaBukti ? 'Ganti Bukti' : 'Unggah Bukti' ?></button>
<p class="small text-muted mt-2 mb-0">Nominal DP ditetapkan sistem dan tidak dapat diubah. Status tetap pending sampai admin memverifikasi.</p>
</form>
<?php elseif ($sb === 'valid'): ?>
<div class="alert alert-success mb-0">Pembayaran diterima dan transaksi disetujui. Tidak ada tindakan lanjutan.</div>
<?php elseif ($sb === 'invalid'): ?>
<div class="alert alert-danger mb-0">Pembayaran ditolak dan transaksi ditolak oleh admin.</div>
<?php endif; ?>
</div>
</div>
<a class="btn btn-outline-secondary" href="<?= $e(site_url('pembayaran')) ?>">
<i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Daftar Pembayaran
</a>
</div>
</div>
</div>
</section>
<script>
(function () {
    'use strict';
    var input = document.getElementById('ft_byr');
    var box = document.getElementById('previewBaru');
    var img = document.getElementById('previewBaruImg');
    if (!input || !box || !img) { return; }
    var url = null;
    input.addEventListener('change', function () {
        if (url) { URL.revokeObjectURL(url); url = null; }
        var f = input.files && input.files.length ? input.files[0] : null;
        if (!f) { box.style.display = 'none'; return; }
        if (['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1) {
            alert('Jenis berkas tidak diizinkan. Gunakan JPG, PNG, atau WEBP.');
            input.value = ''; box.style.display = 'none'; return;
        }
        if (f.size > <?= (int) $maksMb ?> * 1024 * 1024) {
            alert('Ukuran berkas melebihi batas <?= (int) $maksMb ?> MB.');
            input.value = ''; box.style.display = 'none'; return;
        }
        url = URL.createObjectURL(f);
        img.src = url;
        box.style.display = 'block';
    });
})();
</script>
<?php require APPPATH . 'views/katalog/footer.php'; ?>

<?php
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';
?>
<?php
$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$username = $username ?? '';
$mode = $mode ?? null;
$penyewa = $penyewa ?? [];
$penyewa_dipilih = $penyewa_dipilih ?? null;
$flash = $flash ?? null;
$error = $error ?? null;
$isForm = in_array($mode, ['tambah', 'ubah'], true);
$isEdit = $mode === 'ubah';
$userDipilih = $penyewa_dipilih['user_plg'] ?? '';
$formAction = $isEdit
    ? site_url('admin/penyewa/ubah/' . rawurlencode($userDipilih))
    : site_url('admin/penyewa/tambah');
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';
$fotoUrl = static fn(?string $f): string => base_url('uploads/penyewa/' . rawurlencode((string) $f));
$inisial = static fn(string $n): string => mb_strtoupper(mb_substr(trim($n) !== '' ? trim($n) : '?', 0, 1, 'UTF-8'), 'UTF-8');
$warna = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6'];

$total = count($penyewa);
$adaKtp = 0;
$adaSim = 0;
foreach ($penyewa as $p) {
    if (!empty($p['ft_ktp'])) { $adaKtp++; }
    if (!empty($p['ft_sim_a'])) { $adaSim++; }
}
?>
<style>
.mp-page { --mp-radius: 14px; --mp-border: #e8ebf3; --mp-muted: #6b7280; --mp-ink: #1f2937; --mp-accent: #4f46e5; }
.content-wrapper { background: #f5f6fb; }
.mp-page .content-header > h1 { font-weight: 700; color: var(--mp-ink); letter-spacing: -.3px; }
.mp-page .content-header > h1 small { color: var(--mp-muted); font-weight: 400; }
.mp-stats { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
.mp-stat { flex: 1 1 200px; background: #fff; border: 1px solid var(--mp-border); border-radius: var(--mp-radius);
  padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
.mp-stat-ico { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center;
  justify-content: center; font-size: 20px; color: #fff; }
.mp-stat-num { font-size: 24px; font-weight: 700; line-height: 1.1; color: var(--mp-ink); }
.mp-stat-lbl { font-size: 12.5px; color: var(--mp-muted); }
.mp-card { background: #fff; border: 1px solid var(--mp-border); border-radius: var(--mp-radius);
  box-shadow: 0 1px 3px rgba(16,24,40,.05); margin-bottom: 20px; overflow: hidden; }
.mp-card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
  padding: 18px 22px; border-bottom: 1px solid var(--mp-border); }
.mp-card-head h3 { margin: 0; font-size: 17px; font-weight: 600; color: var(--mp-ink); }
.mp-card-head p { margin: 2px 0 0; font-size: 12.5px; color: var(--mp-muted); }
.mp-card-body { padding: 20px 22px; }
.mp-btn { border-radius: 10px; border: 0; font-weight: 600; padding: 8px 16px; }
.mp-btn-primary { background: var(--mp-accent); color: #fff; }
.mp-btn-primary:hover, .mp-btn-primary:focus { background: #4338ca; color: #fff; }
.mp-btn-ghost { background: #f3f4f6; color: #374151; }
.mp-btn-ghost:hover { background: #e5e7eb; color: #111827; }
.mp-page table.dataTable { border-collapse: separate !important; border-spacing: 0; width: 100% !important; }
.mp-page table.dataTable thead th { background: #f9fafb; color: var(--mp-muted); font-size: 11.5px; text-transform: uppercase;
  letter-spacing: .6px; font-weight: 600; border-top: 0 !important; border-bottom: 1px solid var(--mp-border) !important; padding: 12px 14px; white-space: nowrap; }
.mp-page table.dataTable tbody td { border-top: 1px solid #f1f2f6 !important; padding: 14px; vertical-align: middle !important; color: var(--mp-ink); }
.mp-page table.dataTable.table-striped > tbody > tr:nth-of-type(odd) { background: transparent; }
.mp-page table.dataTable tbody tr:hover { background: #fafbff; }
.mp-page table.dataTable tfoot { display: none; }
.mp-user { display: flex; align-items: center; gap: 12px; min-width: 180px; }
.mp-avatar { width: 40px; height: 40px; border-radius: 50%; color: #fff; font-weight: 700; display: flex;
  align-items: center; justify-content: center; flex: 0 0 40px; }
.mp-user-name { font-weight: 600; line-height: 1.2; }
.mp-user-id { font-size: 12px; color: var(--mp-muted); }
.mp-mono { font-family: SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; letter-spacing: .3px; }
.mp-addr { max-width: 320px; white-space: normal; word-break: break-word; line-height: 1.4; color: #4b5563; }
.mp-doc { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px;
  font-size: 11.5px; font-weight: 600; margin-right: 4px; border: 0; cursor: pointer; }
.mp-doc.ok { background: #dcfce7; color: #166534; }
.mp-doc.ok:hover { background: #bbf7d0; }
.mp-doc.no { background: #f3f4f6; color: #9ca3af; cursor: default; }
.mp-actions { white-space: nowrap; }
.mp-ico-btn { width: 34px; height: 34px; border-radius: 10px; border: 0; display: inline-flex; align-items: center;
  justify-content: center; margin-left: 4px; font-size: 14px; transition: transform .1s; }
.mp-ico-btn:hover { transform: translateY(-1px); }
.mp-ico-edit { background: #fef3c7; color: #b45309; }
.mp-ico-edit:hover { background: #fde68a; color: #92400e; }
.mp-ico-del { background: #fee2e2; color: #b91c1c; }
.mp-ico-del:hover { background: #fecaca; }
.mp-page .dataTables_wrapper .dataTables_filter input, .mp-page .dataTables_wrapper .dataTables_length select {
  border-radius: 10px; border: 1px solid var(--mp-border); padding: 6px 12px; height: 36px; outline: none; }
.mp-page .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--mp-accent); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.mp-page .pagination > li > a { border: 0; border-radius: 8px !important; margin: 0 2px; color: #4b5563; }
.mp-page .pagination > .active > a, .mp-page .pagination > .active > a:hover { background: var(--mp-accent); color: #fff; }
.mp-page .dataTables_info { color: var(--mp-muted); font-size: 12.5px; }
.mp-empty { text-align: center; padding: 30px; color: var(--mp-muted); }
.mp-page .alert { border-radius: 12px; border: 0; }
.mp-form .form-control { border-radius: 10px; border: 1px solid var(--mp-border); height: 42px; box-shadow: none; }
.mp-form textarea.form-control { height: auto; }
.mp-form .form-control:focus { border-color: var(--mp-accent); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.mp-form label { font-weight: 600; font-size: 13px; color: #374151; }
.mp-form .help-block { font-size: 12px; color: var(--mp-muted); margin-bottom: 0; }
.mp-form h4 { font-size: 14px; font-weight: 700; color: var(--mp-ink); margin: 6px 0 14px; text-transform: uppercase; letter-spacing: .6px; }
.mp-file { border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 14px; background: #fafbff; height: 100%; }
.mp-file input[type=file] { height: auto; padding: 8px; background: #fff; }
.mp-file img { border-radius: 10px; }
.mp-form-foot { padding: 16px 22px; background: #f9fafb; border-top: 1px solid var(--mp-border); }
#fotoModal .modal-content { border-radius: 16px; border: 0; overflow: hidden; }
#fotoModal .modal-header { border-bottom: 1px solid var(--mp-border); }
#fotoModal img { max-width: 100%; border-radius: 10px; }
</style>

<div class="content-wrapper mp-page">
<section class="content-header">
<h1>Manajemen Penyewa <small>Kelola data penyewa kendaraan</small></h1>
<ol class="breadcrumb">
<li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
<li class="active">Manajemen Penyewa</li>
</ol>
</section>

<section class="content">
<?php if (!empty($flash)): ?>
<div class="alert <?= $flashClass ?> alert-dismissible">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i>
<?= $e($flash['message'] ?? '') ?>
</div>
<?php endif; ?>

<div class="mp-stats">
<div class="mp-stat">
<div class="mp-stat-ico" style="background:#6366f1;"><i class="fa fa-users"></i></div>
<div><div class="mp-stat-num"><?= $total ?></div><div class="mp-stat-lbl">Total penyewa</div></div>
</div>
<div class="mp-stat">
<div class="mp-stat-ico" style="background:#10b981;"><i class="fa fa-id-card"></i></div>
<div><div class="mp-stat-num"><?= $adaKtp ?></div><div class="mp-stat-lbl">KTP terunggah</div></div>
</div>
<div class="mp-stat">
<div class="mp-stat-ico" style="background:#f59e0b;"><i class="fa fa-car"></i></div>
<div><div class="mp-stat-num"><?= $adaSim ?></div><div class="mp-stat-lbl">SIM A terunggah</div></div>
</div>
</div>

<div class="mp-card">
<div class="mp-card-head">
<div>
<h3>Daftar Penyewa</h3>
<p>Cari, ubah, atau hapus data penyewa terdaftar</p>
</div>
<a href="<?= $e(site_url('admin/penyewa/tambah')) ?>" class="btn mp-btn mp-btn-primary">
<i class="fa fa-plus"></i> Tambah Penyewa
</a>
</div>
<div class="mp-card-body">
<div class="table-responsive" style="border:0;">
<table id="penyewaTable" class="table js-data-table">
<thead>
<tr>
<th>No</th>
<th>Penyewa</th>
<th>NIK</th>
<th>No. HP</th>
<th>Alamat</th>
<th class="no-sort">Dokumen</th>
<th class="no-sort text-right">Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($penyewa as $no => $p): ?>
<?php $bg = $warna[crc32((string) $p['user_plg']) % count($warna)]; ?>
<tr>
<td><?= $no + 1 ?></td>
<td>
<div class="mp-user">
<div class="mp-avatar" style="background:<?= $bg ?>;"><?= $e($inisial((string) $p['nm_lngkp'])) ?></div>
<div>
<div class="mp-user-name"><?= $e($p['nm_lngkp']) ?></div>
<div class="mp-user-id">@<?= $e($p['user_plg']) ?></div>
</div>
</div>
</td>
<td><span class="mp-mono"><?= $e($p['nik']) ?></span></td>
<td><?= $e($p['no_hp']) ?></td>
<td><div class="mp-addr" title="<?= $e($p['almt_lngkp']) ?>"><?= $e($p['almt_lngkp']) ?></div></td>
<td>
<?php foreach (['ft_ktp' => 'KTP', 'ft_sim_a' => 'SIM A'] as $f => $lbl): ?>
<?php if (!empty($p[$f])): ?>
<button type="button" class="mp-doc ok js-foto" data-toggle="modal" data-target="#fotoModal"
data-src="<?= $e($fotoUrl($p[$f])) ?>" data-title="<?= $e($lbl . ' - ' . $p['nm_lngkp']) ?>">
<i class="fa fa-check-circle"></i> <?= $e($lbl) ?>
</button>
<?php else: ?>
<span class="mp-doc no"><i class="fa fa-minus-circle"></i> <?= $e($lbl) ?></span>
<?php endif; ?>
<?php endforeach; ?>
</td>
<td class="mp-actions text-right">
<a href="<?= $e(site_url('admin/penyewa/ubah/' . rawurlencode($p['user_plg']))) ?>"
class="mp-ico-btn mp-ico-edit" title="Edit"><i class="fa fa-pencil"></i></a>
<form method="post"
action="<?= $e(site_url('admin/penyewa/hapus/' . rawurlencode($p['user_plg']))) ?>"
style="display:inline;">
<button type="submit" class="mp-ico-btn mp-ico-del" title="Hapus"
onclick="return confirm('Yakin ingin menghapus penyewa ini?');">
<i class="fa fa-trash"></i>
</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

<?php if ($error !== null): ?>
<div class="alert alert-danger alert-dismissible">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<i class="fa fa-warning"></i> <?= $e($error) ?>
</div>
<?php endif; ?>

<?php if ($isForm): ?>
<div class="mp-card mp-form">
<div class="mp-card-head">
<div>
<h3><?= $isEdit ? 'Ubah Data Penyewa' : 'Tambah Penyewa' ?></h3>
<p><?= $isEdit ? 'Perbarui data akun dan berkas identitas' : 'Isi data akun dan unggah berkas identitas' ?></p>
</div>
</div>
<form role="form" method="post" enctype="multipart/form-data" action="<?= $e($formAction) ?>">
<div class="mp-card-body">
<div class="row">
<div class="col-md-6">
<div class="form-group">
<label for="user_plg">Username</label>
<input type="text" class="form-control" id="user_plg" name="user_plg" maxlength="15"
pattern="[A-Za-z0-9._-]{1,15}"
value="<?= $e($penyewa_dipilih['user_plg'] ?? '') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="nm_lngkp">Nama Lengkap</label>
<input type="text" class="form-control" id="nm_lngkp" name="nm_lngkp" maxlength="100"
value="<?= $e($penyewa_dipilih['nm_lngkp'] ?? '') ?>" required>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="sandi">Password<?= $isEdit ? ' (opsional)' : '' ?></label>
<input type="password" class="form-control" id="sandi" name="sandi" minlength="8"
maxlength="72" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
<p class="help-block"><?= $isEdit ? 'Kosongkan jika password tidak ingin diubah.' : 'Minimal 8 karakter. Disimpan dalam bentuk hash.' ?></p>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="konfirmasi_sandi">Konfirmasi Password</label>
<input type="password" class="form-control" id="konfirmasi_sandi" name="konfirmasi_sandi"
maxlength="72" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="nik">NIK</label>
<input type="text" class="form-control" id="nik" name="nik" maxlength="16"
inputmode="numeric" pattern="[0-9]{16}"
value="<?= $e($penyewa_dipilih['nik'] ?? '') ?>" required>
<p class="help-block">Tepat 16 digit angka.</p>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label for="no_hp">Nomor HP</label>
<input type="text" class="form-control" id="no_hp" name="no_hp" maxlength="15"
value="<?= $e($penyewa_dipilih['no_hp'] ?? '') ?>" required>
</div>
</div>
<div class="col-md-12">
<div class="form-group">
<label for="almt_lngkp">Alamat Lengkap</label>
<textarea class="form-control" id="almt_lngkp" name="almt_lngkp" rows="3" maxlength="300"
required><?= $e($penyewa_dipilih['almt_lngkp'] ?? '') ?></textarea>
</div>
</div>
</div>
<hr>
<h4>Berkas Identitas</h4>
<div class="row">
<?php foreach (['ft_ktp' => 'Foto KTP', 'ft_sim_a' => 'Foto SIM A'] as $field => $label): ?>
<div class="col-md-6" style="margin-bottom:14px;">
<div class="form-group mp-file" style="margin-bottom:0;">
<label for="<?= $e($field) ?>"><?= $e($label) ?></label>
<input type="file" class="form-control" id="<?= $e($field) ?>" name="<?= $e($field) ?>"
accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
<p class="help-block">JPG, JPEG, PNG, atau WEBP; maksimal 5 MB.</p>
<div class="photo-preview" style="margin-top:10px;">
<?php if (!empty($penyewa_dipilih[$field]) && is_string($penyewa_dipilih[$field])): ?>
<p class="help-block">Foto saat ini (kosongkan input jika tidak ingin mengganti):</p>
<img class="img-thumbnail" alt="<?= $e($label) ?> saat ini"
src="<?= $e($fotoUrl($penyewa_dipilih[$field])) ?>"
style="width:180px;height:120px;object-fit:cover;">
<?php else: ?>
<p class="help-block">Belum ada foto tersimpan.</p>
<?php endif; ?>
<div class="new-photo-preview" style="display:none;margin-top:8px;">
<p class="help-block">Pratinjau foto yang dipilih:</p>
<img class="img-thumbnail new-photo-image" alt="Pratinjau <?= $e($label) ?>"
style="width:180px;height:120px;object-fit:cover;">
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<div class="mp-form-foot">
<button type="submit" class="btn mp-btn mp-btn-primary"><i class="fa fa-save"></i> Simpan</button>
<a href="<?= $e(site_url('admin/penyewa')) ?>" class="btn mp-btn mp-btn-ghost">
<i class="fa fa-times"></i> Batal
</a>
</div>
</form>
</div>
<?php endif; ?>
</section>
</div>

<!-- Modal pratinjau foto dokumen -->
<div class="modal fade" id="fotoModal" tabindex="-1" role="dialog" aria-labelledby="fotoModalTitle">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title" id="fotoModalTitle">Dokumen</h4>
</div>
<div class="modal-body text-center">
<img id="fotoModalImg" alt="Pratinjau dokumen">
</div>
</div>
</div>
</div>

<script>
(function() {
'use strict';
document.querySelectorAll('.js-foto').forEach(function(btn) {
btn.addEventListener('click', function() {
document.getElementById('fotoModalImg').src = btn.getAttribute('data-src');
document.getElementById('fotoModalTitle').textContent = btn.getAttribute('data-title');
});
});
document.querySelectorAll('input[type="file"][name^="ft_"]').forEach(function(input) {
var box = input.closest('.form-group');
var preview = box ? box.querySelector('.new-photo-preview') : null;
var image = preview ? preview.querySelector('.new-photo-image') : null;
var prev = null;
if (!preview || !image) { return; }
input.addEventListener('change', function() {
if (prev) { URL.revokeObjectURL(prev); prev = null; }
var f = input.files && input.files.length ? input.files[0] : null;
if (!f || ['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1 ||
f.size < 1 || f.size > 5 * 1024 * 1024) {
preview.style.display = 'none';
image.removeAttribute('src');
return;
}
prev = URL.createObjectURL(f);
image.src = prev;
preview.style.display = 'block';
});
});
})();
</script>
<?php
require APPPATH . '/views/templates/footer.php';
?>

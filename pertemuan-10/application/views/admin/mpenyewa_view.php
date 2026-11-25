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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.mp-page { --mp-radius: 18px; --mp-border: #eceef5; --mp-muted: #8189a0; --mp-ink: #12162b; --mp-accent: #6366f1; --mp-accent2: #8b5cf6;
  font-family: 'Inter', 'Source Sans Pro', -apple-system, 'Segoe UI', sans-serif; }
.content-wrapper.mp-page { background: radial-gradient(1200px 400px at 10% -10%, #e9e9ff 0%, transparent 60%), #f6f7fc; }
.mp-page .content-header { display: none; }
.mp-hero { position: relative; overflow: hidden; border-radius: 24px; padding: 30px 34px; margin-bottom: 22px; color: #fff;
  background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 55%, #db2777 120%); box-shadow: 0 18px 40px -18px rgba(79,70,229,.7); }
.mp-hero:before, .mp-hero:after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.12); }
.mp-hero:before { width: 260px; height: 260px; right: -60px; top: -110px; }
.mp-hero:after { width: 160px; height: 160px; right: 130px; bottom: -100px; background: rgba(255,255,255,.08); }
.mp-hero-crumb { font-size: 12px; opacity: .8; margin-bottom: 8px; letter-spacing: .3px; }
.mp-hero-crumb a { color: #fff; }
.mp-hero h1 { margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -.8px; }
.mp-hero p { margin: 6px 0 0; opacity: .85; font-size: 14px; max-width: 520px; }
.mp-hero-row { position: relative; z-index: 1; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.mp-hero .mp-btn-light { background: #fff; color: #4f46e5; border-radius: 999px; padding: 11px 22px; font-weight: 700; border: 0;
  box-shadow: 0 10px 24px -10px rgba(0,0,0,.5); transition: transform .15s, box-shadow .15s; }
.mp-hero .mp-btn-light:hover { transform: translateY(-2px); box-shadow: 0 16px 28px -12px rgba(0,0,0,.55); color: #4338ca; }
.mp-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 22px; }
.mp-stat { background: rgba(255,255,255,.85); backdrop-filter: blur(8px); border: 1px solid #fff; border-radius: var(--mp-radius);
  padding: 20px; box-shadow: 0 10px 30px -18px rgba(30,41,90,.35); transition: transform .2s, box-shadow .2s; }
.mp-stat:hover { transform: translateY(-3px); box-shadow: 0 18px 34px -18px rgba(30,41,90,.45); }
.mp-stat-top { display: flex; align-items: center; justify-content: space-between; }
.mp-stat-ico { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; }
.mp-stat-pct { font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: #f1f2fb; color: #4f46e5; }
.mp-stat-num { font-size: 30px; font-weight: 800; letter-spacing: -1px; color: var(--mp-ink); margin-top: 14px; line-height: 1; }
.mp-stat-lbl { font-size: 13px; color: var(--mp-muted); margin-top: 4px; }
.mp-bar { height: 6px; border-radius: 99px; background: #eef0f8; margin-top: 14px; overflow: hidden; }
.mp-bar > span { display: block; height: 100%; border-radius: 99px; }
.mp-card { background: rgba(255,255,255,.92); border: 1px solid #fff; border-radius: 22px; margin-bottom: 22px;
  box-shadow: 0 20px 44px -26px rgba(30,41,90,.4); overflow: hidden; }
.mp-card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 22px 26px 6px; }
.mp-card-head h3 { margin: 0; font-size: 18px; font-weight: 700; color: var(--mp-ink); letter-spacing: -.3px; }
.mp-card-head p { margin: 3px 0 0; font-size: 13px; color: var(--mp-muted); }
.mp-card-body { padding: 14px 26px 24px; }
.mp-btn { border-radius: 999px; border: 0; font-weight: 600; padding: 10px 22px; transition: transform .15s, box-shadow .15s, background .15s; }
.mp-btn-primary { background: linear-gradient(135deg, var(--mp-accent), var(--mp-accent2)); color: #fff; box-shadow: 0 10px 20px -10px rgba(99,102,241,.8); }
.mp-btn-primary:hover, .mp-btn-primary:focus { color: #fff; transform: translateY(-2px); box-shadow: 0 14px 24px -10px rgba(99,102,241,.9); }
.mp-btn-ghost { background: #f1f2fb; color: #4b5170; }
.mp-btn-ghost:hover { background: #e6e8f6; color: #1f2340; }
.mp-page table.dataTable { border-collapse: separate !important; border-spacing: 0 10px; width: 100% !important; margin-top: 0 !important; }
.mp-page table.dataTable thead th { background: transparent; color: var(--mp-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
  font-weight: 700; border: 0 !important; padding: 4px 16px; white-space: nowrap; }
.mp-page table.dataTable thead .sorting:after, .mp-page table.dataTable thead .sorting_asc:after, .mp-page table.dataTable thead .sorting_desc:after { opacity: .35; }
.mp-page table.dataTable tbody tr { background: #fff; box-shadow: 0 1px 0 #eceef5, 0 6px 16px -12px rgba(30,41,90,.35); transition: transform .15s, box-shadow .15s; }
.mp-page table.dataTable tbody tr:hover { transform: translateY(-2px); box-shadow: 0 14px 26px -14px rgba(79,70,229,.45); }
.mp-page table.dataTable tbody td { border: 0 !important; padding: 16px; vertical-align: middle !important; color: var(--mp-ink); background: #fff; }
.mp-page table.dataTable tbody td:first-child { border-radius: 16px 0 0 16px; color: var(--mp-muted); font-weight: 600; }
.mp-page table.dataTable tbody td:last-child { border-radius: 0 16px 16px 0; }
.mp-page table.dataTable tfoot { display: none; }
.mp-user { display: flex; align-items: center; gap: 14px; min-width: 190px; }
.mp-avatar { width: 44px; height: 44px; border-radius: 15px; color: #fff; font-weight: 700; font-size: 17px; display: flex; align-items: center; justify-content: center;
  flex: 0 0 44px; box-shadow: 0 8px 16px -8px rgba(0,0,0,.4); }
.mp-user-name { font-weight: 700; line-height: 1.2; letter-spacing: -.2px; }
.mp-user-id { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
.mp-mono { font-family: 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 12.5px; background: #f4f5fb; padding: 4px 9px; border-radius: 8px; color: #3b4163; }
.mp-addr { max-width: 320px; white-space: normal; word-break: break-word; line-height: 1.45; color: #555d7a; font-size: 13px; }
.mp-doc { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 700; margin: 2px 4px 2px 0; border: 0; cursor: pointer; transition: transform .12s; }
.mp-doc.ok { background: #e6f9ef; color: #0f7a46; }
.mp-doc.ok:hover { background: #cdf3df; transform: translateY(-1px); }
.mp-doc.no { background: #f3f4f9; color: #a3a9bf; cursor: default; }
.mp-actions { white-space: nowrap; }
.mp-ico-btn { width: 36px; height: 36px; border-radius: 12px; border: 0; display: inline-flex; align-items: center; justify-content: center; margin-left: 6px; font-size: 14px; transition: transform .12s, background .12s; }
.mp-ico-btn:hover { transform: translateY(-2px); }
.mp-ico-edit { background: #eef0ff; color: #4f46e5; }
.mp-ico-edit:hover { background: #dfe2ff; color: #3730a3; }
.mp-ico-del { background: #ffeef0; color: #e11d48; }
.mp-ico-del:hover { background: #ffdde2; color: #be123c; }
.mp-page .dataTables_wrapper .dataTables_filter input, .mp-page .dataTables_wrapper .dataTables_length select {
  border-radius: 999px; border: 1px solid var(--mp-border); background: #f8f9fe; padding: 6px 16px; height: 38px; outline: none; transition: all .15s; }
.mp-page .dataTables_wrapper .dataTables_filter input { min-width: 220px; }
.mp-page .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--mp-accent); background: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,.14); }
.mp-page .dataTables_wrapper .dataTables_filter, .mp-page .dataTables_wrapper .dataTables_length { margin-bottom: 6px; color: var(--mp-muted); font-size: 13px; }
.mp-page .pagination > li > a { border: 0; background: transparent; border-radius: 12px !important; margin: 0 2px; color: #555d7a; font-weight: 600; }
.mp-page .pagination > li > a:hover { background: #eef0ff; color: #4f46e5; }
.mp-page .pagination > .active > a, .mp-page .pagination > .active > a:hover { background: linear-gradient(135deg, var(--mp-accent), var(--mp-accent2)); color: #fff; }
.mp-page .pagination > .disabled > a { color: #c2c6d8; background: transparent; }
.mp-page .dataTables_info { color: var(--mp-muted); font-size: 12.5px; }
.mp-page .alert { border-radius: 16px; border: 0; box-shadow: 0 10px 24px -16px rgba(0,0,0,.35); }
.mp-form .form-control { border-radius: 14px; border: 1.5px solid var(--mp-border); background: #f8f9fe; height: 46px; box-shadow: none; transition: all .15s; }
.mp-form textarea.form-control { height: auto; }
.mp-form .form-control:focus { border-color: var(--mp-accent); background: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,.14); }
.mp-form .form-control[readonly] { background: #eef0f6; color: #7b8299; }
.mp-form label { font-weight: 600; font-size: 12.5px; color: #3b4163; letter-spacing: .2px; }
.mp-form .help-block { font-size: 12px; color: var(--mp-muted); margin: 6px 0 0; }
.mp-form h4 { font-size: 12px; font-weight: 800; color: var(--mp-accent); margin: 6px 0 14px; text-transform: uppercase; letter-spacing: 1.2px; }
.mp-form hr { border-top: 1px dashed #dfe2f1; margin: 20px 0; }
.mp-file { border: 2px dashed #d3d7ee; border-radius: 18px; padding: 16px; background: #fafaff; height: 100%; transition: border-color .15s, background .15s; }
.mp-file:hover { border-color: var(--mp-accent); background: #f4f4ff; }
.mp-file input[type=file] { height: auto; padding: 9px; background: #fff; }
.mp-file img { border-radius: 14px; border: 0; box-shadow: 0 8px 18px -10px rgba(0,0,0,.4); }
.mp-form-foot { padding: 18px 26px; background: #fafaff; border-top: 1px solid var(--mp-border); }
#fotoModal .modal-content { border-radius: 22px; border: 0; overflow: hidden; box-shadow: 0 30px 70px -20px rgba(0,0,0,.5); }
#fotoModal .modal-header { border-bottom: 1px solid var(--mp-border); padding: 18px 24px; }
#fotoModal .modal-title { font-weight: 700; }
#fotoModal .modal-body { background: #f6f7fc; padding: 24px; }
#fotoModal img { max-width: 100%; border-radius: 14px; box-shadow: 0 14px 30px -14px rgba(0,0,0,.5); }
@media (max-width: 767px) { .mp-hero { padding: 22px; border-radius: 20px; } .mp-hero h1 { font-size: 24px; } }
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
<div class="mp-hero">
<div class="mp-hero-row">
<div>
<div class="mp-hero-crumb"><a href="<?= $e(site_url('admin')) ?>">Dashboard</a> &nbsp;/&nbsp; Manajemen Penyewa</div>
<h1>Manajemen Penyewa</h1>
<p>Kelola akun, identitas, dan berkas KTP serta SIM A penyewa kendaraan dalam satu tempat.</p>
</div>
<a href="<?= $e(site_url('admin/penyewa/tambah')) ?>" class="btn mp-btn-light"><i class="fa fa-plus"></i> Tambah Penyewa</a>
</div>
</div>
<?php if (!empty($flash)): ?>
<div class="alert <?= $flashClass ?> alert-dismissible">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i>
<?= $e($flash['message'] ?? '') ?>
</div>
<?php endif; ?>

<?php
$pctKtp = $total > 0 ? (int) round($adaKtp / $total * 100) : 0;
$pctSim = $total > 0 ? (int) round($adaSim / $total * 100) : 0;
?>
<div class="mp-stats">
<div class="mp-stat">
<div class="mp-stat-top"><div class="mp-stat-ico" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><i class="fa fa-users"></i></div></div>
<div class="mp-stat-num"><?= $total ?></div><div class="mp-stat-lbl">Total penyewa</div>
<div class="mp-bar"><span style="width:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></span></div>
</div>
<div class="mp-stat">
<div class="mp-stat-top"><div class="mp-stat-ico" style="background:linear-gradient(135deg,#10b981,#34d399);"><i class="fa fa-id-card"></i></div><span class="mp-stat-pct"><?= $pctKtp ?>%</span></div>
<div class="mp-stat-num"><?= $adaKtp ?></div><div class="mp-stat-lbl">KTP terunggah</div>
<div class="mp-bar"><span style="width:<?= $pctKtp ?>%;background:linear-gradient(90deg,#10b981,#34d399);"></span></div>
</div>
<div class="mp-stat">
<div class="mp-stat-top"><div class="mp-stat-ico" style="background:linear-gradient(135deg,#f59e0b,#fb7185);"><i class="fa fa-car"></i></div><span class="mp-stat-pct"><?= $pctSim ?>%</span></div>
<div class="mp-stat-num"><?= $adaSim ?></div><div class="mp-stat-lbl">SIM A terunggah</div>
<div class="mp-bar"><span style="width:<?= $pctSim ?>%;background:linear-gradient(90deg,#f59e0b,#fb7185);"></span></div>
</div>
</div>

<div class="mp-card">
<div class="mp-card-head">
<div>
<h3>Daftar Penyewa</h3>
<p>Cari, ubah, atau hapus data penyewa terdaftar</p>
</div>
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

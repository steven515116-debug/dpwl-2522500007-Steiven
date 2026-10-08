<?php 
include APPPATH . 'views/templates/header.php'; 
include APPPATH . 'views/templates/sidebar.php'; 

$username = $username ?? ''; 
$mode = $mode ?? null; 
$admin_dipilih = $admin_dipilih ?? null; 
$admins = $admins ?? []; 
$flash = $flash ?? null; 
$error = $error ?? null; 

$isForm = in_array($mode, ['tambah', 'ubah'], true); 
$isEdit = $mode === 'ubah'; 

$usernameDipilih = $admin_dipilih['username'] ?? ''; 
$formAction = $isEdit 
  ? site_url('admin/madmin/ubah/' . rawurlencode($usernameDipilih)) 
  : site_url('admin/madmin/tambah'); 

$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger'; 
?> 

<!-- Content Wrapper --> 
<div class="content-wrapper"> 
  <section class="content-header"> 
    <h1> 
      Manajemen Admin 
      <small>Kelola akun admin sistem</small> 
    </h1> 
    <ol class="breadcrumb"> 
      <li> 
        <a href="<?= htmlspecialchars(site_url('admin'), ENT_QUOTES, 'UTF-8') ?>"> 
          <i class="fa fa-dashboard"></i> Dashboard 
        </a> 
      </li> 
      <li class="active">Manajemen Admin</li> 
    </ol> 
  </section> 

  <section class="content"> 

    <!-- Flash message hasil PRG --> 
    <?php if (!empty($flash)): ?> 
      <div class="alert <?= $flashClass ?> alert-dismissible"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> 
        <i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i> 
        <?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
      </div> 
    <?php endif; ?> 

    <!-- Data Table --> 
    <div class="box"> 
      <div class="box-header"> 
        <h3 class="box-title">Data Admin</h3> 
        <div class="box-tools pull-right"> 
          <a href="<?= htmlspecialchars(site_url('admin/madmin/tambah'), ENT_QUOTES, 'UTF-8') ?>" 
            class="btn btn-primary btn-sm"> 
            <i class="fa fa-plus"></i> Tambah Admin 
          </a> 
        </div> 
      </div> 

      <div class="box-body"> 
        <table id="adminTable" class="table table-bordered table-striped js-data-table"> 
          <thead> 
            <tr> 
              <th style="width: 50px;">No</th> 
              <th>Username</th> 
              <th>Status Akun</th> 
              <th class="no-sort" style="width: 150px;">Aksi</th> 
            </tr> 
          </thead> 
          <tbody> 
            <?php foreach ($admins as $no => $admin): ?> 
              <tr> 
                <td><?= $no + 1 ?></td> 
                <td><?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?></td> 
                <td> 
                  <?php if ($admin['status_akun'] === 'aktif'): ?> 
                    <span class="label label-success">Aktif</span> 
                  <?php else: ?> 
                    <span class="label label-default">Nonaktif</span> 
                  <?php endif; ?> 
                </td> 
                <td> 
                  <a href="<?= htmlspecialchars(site_url('admin/madmin/ubah/' . rawurlencode($admin['username'])), ENT_QUOTES, 'UTF-8') ?>" 
                    class="btn btn-warning btn-sm" title="Edit"> 
                    <i class="fa fa-edit"></i> Edit 
                  </a> 

                  <form method="post" 
                    action="<?= htmlspecialchars(site_url('admin/madmin/hapus/' . rawurlencode($admin['username'])), ENT_QUOTES, 'UTF-8') ?>" 
                    style="display:inline;"> 
                    <button type="submit" 
                      class="btn btn-danger btn-sm" 
                      title="Hapus" 
                      onclick="return confirm('Yakin ingin menghapus admin ini?');"> 
                      <i class="fa fa-trash"></i> Hapus 
                    </button> 
                  </form> 
                </td> 
              </tr> 
            <?php endforeach; ?> 
          </tbody> 
          <tfoot> 
            <tr> 
              <th>No</th> 
              <th>Username</th> 
              <th>Status Akun</th> 
              <th>Aksi</th> 
            </tr> 
          </tfoot> 
        </table> 
      </div> 
    </div> 

    <?php if ($error !== null): ?> 
      <div class="alert alert-danger alert-dismissible"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> 
        <i class="fa fa-warning"></i> 
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?> 
      </div> 
    <?php endif; ?> 

    <?php if ($isForm): ?> 
      <div class="box box-primary"> 
        <div class="box-header with-border"> 
          <h3 class="box-title"> 
            <?= $isEdit ? 'Ubah Data Admin' : 'Tambah Admin' ?> 
          </h3> 
        </div> 

        <form role="form" method="post" 
          action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>"> 

          <div class="box-body"> 
            <div class="form-group"> 
              <label for="username">Username</label> 
              <input type="text" 
                class="form-control" 
                id="username" 
                name="username" 
                maxlength="15" 
                value="<?= htmlspecialchars($admin_dipilih['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                <?= $isEdit ? 'readonly' : '' ?> 
                required> 
            </div> 

            <div class="form-group"> 
              <label for="password"> 
                Password<?= $isEdit ? ' (opsional)' : '' ?> 
              </label> 
              <input type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                <?= $isEdit ? '' : 'required' ?>> 
              <?php if ($isEdit): ?> 
                <p class="help-block">Kosongkan jika password tidak ingin diubah.</p> 
              <?php endif; ?> 
            </div> 

            <div class="form-group"> 
              <label>Status Akun</label> 

              <div class="radio"> 
                <label> 
                  <input type="radio" 
                    name="status_akun" 
                    value="aktif" 
                    <?= (($admin_dipilih['status_akun'] ?? '') === 'aktif') ? 'checked' : '' ?> 
                    required> 
                  Aktif 
                </label> 
              </div> 

              <div class="radio"> 
                <label> 
                  <input type="radio" 
                    name="status_akun" 
                    value="nonaktif" 
                    <?= (($admin_dipilih['status_akun'] ?? '') === 'nonaktif') ? 'checked' : '' ?>> 
                  Nonaktif 
                </label> 
              </div> 
            </div> 
          </div> 

          <div class="box-footer"> 
            <button type="submit" class="btn btn-primary"> 
              <i class="fa fa-save"></i> Simpan 
            </button> 
            <a href="<?= htmlspecialchars(site_url('admin/madmin'), ENT_QUOTES, 'UTF-8') ?>" 
              class="btn btn-default"> 
              <i class="fa fa-times"></i> Batal 
            </a> 
          </div> 
        </form> 
      </div> 
    <?php endif; ?> 

  </section> 
</div> 
<!-- Penutup .content-wrapper dipastikan SEBELUM footer -->

<?php 
include APPPATH . 'views/templates/footer.php'; 
?>
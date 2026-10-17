<?php
// Controller khusus untuk pengelolaan data admin (CRUD).
class Madmin_controller extends Controller
{
    private Admin_model $model;

    public function __construct()
    {
        require_once APPPATH . 'models/Admin_model.php';
        require_once APPPATH . 'helpers/auth_helper.php';
        require_once APPPATH . 'helpers/flash_helper.php';
        $this->model = new Admin_model();
    }

    public function index(): void
    {
        require_admin_login();

        $admins = $this->model->getAll();
        $flash = get_flash_message();

        $data = [
            'username'      => $_SESSION['admin_username'] ?? 'Steiven',
            'admins'        => $admins,
            'flash'         => $flash,
            'mode'          => null,
            'admin_dipilih' => null,
            'error'         => null,
        ];

        // Pemanggilan urutan layout AdminLTE standar (Cukup 1 kali masing-masing)
        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/madmin_view', $data);
        $this->view('templates/footer', $data);
    }

    public function tambah(): void
    {
        require_admin_login();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->renderMadmin('tambah', [
                'username'    => '',
                'status_akun' => '',
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode request tidak diizinkan.');
        }

        $username   = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $statusAkun = $_POST['status_akun'] ?? '';

        $error = $this->validateInput($username, $password, $statusAkun, false);
        if ($error !== null) {
            $this->renderMadmin('tambah', [
                'username'    => $username,
                'status_akun' => $statusAkun,
            ], $error);
            return;
        }

        if ($this->model->findByUsername($username) !== null) {
            $this->renderMadmin('tambah', [
                'username'    => $username,
                'status_akun' => $statusAkun,
            ], 'Username sudah digunakan.');
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if (!$this->model->create($username, $passwordHash, $statusAkun)) {
            $this->renderMadmin('tambah', [
                'username'    => $username,
                'status_akun' => $statusAkun,
            ], 'Data admin gagal disimpan.');
            return;
        }

        set_flash_message('success', 'Data admin berhasil ditambahkan.');
        header('Location: ' . site_url('admin/madmin'));
        exit;
    }

    public function ubah(string $username): void
    {
        require_admin_login();

        $admin = $this->model->findByUsername($username);
        if ($admin === null) {
            set_flash_message('danger', 'Data admin tidak ditemukan.');
            header('Location: ' . site_url('admin/madmin'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->renderMadmin('ubah', [
                'username'    => $admin['username'],
                'status_akun' => $admin['status_akun'],
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode request tidak diizinkan.');
        }

        $password   = $_POST['password'] ?? '';
        $statusAkun = $_POST['status_akun'] ?? '';

        $error = $this->validateInput($username, $password, $statusAkun, true);
        if ($error !== null) {
            $this->renderMadmin('ubah', [
                'username'    => $username,
                'status_akun' => $statusAkun,
            ], $error);
            return;
        }

        $passwordHash = null;
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!$this->model->update($username, $passwordHash, $statusAkun)) {
            $this->renderMadmin('ubah', [
                'username'    => $username,
                'status_akun' => $statusAkun,
            ], 'Data admin gagal diperbarui.');
            return;
        }

        set_flash_message('success', 'Data admin berhasil diperbarui.');
        header('Location: ' . site_url('admin/madmin'));
        exit;
    }

    public function hapus(string $username): void
    {
        require_admin_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode request tidak diizinkan.');
        }

        if ($this->model->findByUsername($username) === null) {
            set_flash_message('danger', 'Data admin tidak ditemukan.');
            header('Location: ' . site_url('admin/madmin'));
            exit;
        }

        if (!$this->model->delete($username)) {
            set_flash_message('danger', 'Data admin gagal dihapus.');
            header('Location: ' . site_url('admin/madmin'));
            exit;
        }

        set_flash_message('success', 'Data admin berhasil dihapus.');
        header('Location: ' . site_url('admin/madmin'));
        exit;
    }

    private function renderMadmin(
        string $mode,
        ?array $adminDipilih = null,
        ?string $error = null
    ): void {
        $admins = $this->model->getAll();

        $data = [
            'username'      => $_SESSION['admin_username'] ?? 'Steiven',
            'admins'        => $admins,
            'flash'         => null,
            'mode'          => $mode,
            'admin_dipilih' => $adminDipilih,
            'error'         => $error,
        ];

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/madmin_view', $data);
        $this->view('templates/footer', $data);
    }

    private function validateInput(
        string $username,
        string $password,
        string $statusAkun,
        bool $isEdit
    ): ?string {
        if (
            $username === '' ||
            strlen($username) > 15 ||
            preg_match('/[\x00-\x1F\x7F]/', $username)
        ) {
            return 'Username tidak valid.';
        }

        if (!$isEdit && $password === '') {
            return 'Password wajib diisi.';
        }

        if (!in_array($statusAkun, ['aktif', 'nonaktif'], true)) {
            return 'Status akun tidak valid.';
        }

        return null;
    }
}
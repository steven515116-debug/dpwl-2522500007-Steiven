<?php
// P9: Verifikasi pembayaran DP (sisi Admin).
require_once APPPATH . 'models/Pembayaran_model.php';
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'helpers/bukti_helper.php';

class Mpembayaran_controller extends Controller
{
    private Pembayaran_model $model;

    public function __construct()
    {
        $this->model = new Pembayaran_model();
    }

    // Daftar pembayaran menunggu verifikasi + riwayat verifikasi.
    public function index(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        try {
            $this->view('admin/mpembayaran_view', [
                'username' => $_SESSION['admin_username'],
                'menunggu' => $this->model->getMenungguVerifikasi(),
                'riwayat' => $this->model->getRiwayatVerifikasi(),
                'flash' => get_flash_message(),
            ]);
        } catch (Throwable $e) {
            error_log('Daftar verifikasi gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Daftar verifikasi belum dapat ditampilkan. Silakan coba kembali.');
        }
    }

    // Detail pembayaran: ?kode_bayar=PAY/YYYYMMDD/NN
    public function detail(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));
        $pembayaran = $this->ambilPembayaran($kodeBayar);
        if ($pembayaran === null) {
            set_flash_message('danger', Pembayaran_model::PESAN_TIDAK_DITEMUKAN);
            header('Location: ' . site_url('admin/pembayaran'));
            exit;
        }
        $this->view('admin/mpembayaran_detail', [
            'username' => $_SESSION['admin_username'],
            'pembayaran' => $pembayaran,
            'flash' => get_flash_message(),
        ]);
    }

    // Mengirim gambar bukti agar admin dapat memeriksanya.
    // P10: berlaku untuk bukti DP (P9) dan bukti pelunasan transfer (P10).
    public function bukti(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));
        $pembayaran = null;
        if (bukti_kode_bayar_valid($kodeBayar)) {
            try {
                $pembayaran = $this->model->findBuktiUntukAdmin($kodeBayar);
            } catch (Throwable $e) {
                error_log('Pembacaan bukti pembayaran gagal: ' . $e->getMessage());
                http_response_code(500);
                exit('Data pembayaran belum dapat dibaca. Silakan coba kembali.');
            }
        }
        if ($pembayaran === null) {
            http_response_code(404);
            exit(Pembayaran_model::PESAN_TIDAK_DITEMUKAN);
        }
        kirim_bukti_pembayaran($pembayaran['ft_byr'] ?? null);
    }

    // Terima/tolak (POST) lalu PRG.
    public function verifikasi(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('POST');
        $kodeBayar = trim((string) ($_POST['kode_bayar'] ?? ''));
        $keputusan = trim((string) ($_POST['keputusan'] ?? ''));
        $balikDetail = 'admin/pembayaran/detail?kode_bayar=' . rawurlencode($kodeBayar);

        if (!bukti_kode_bayar_valid($kodeBayar)) {
            set_flash_message('danger', Pembayaran_model::PESAN_TIDAK_DITEMUKAN);
            header('Location: ' . site_url('admin/pembayaran'));
            exit;
        }

        try {
            $this->model->verifikasi($kodeBayar, $keputusan);
        } catch (Pembayaran_exception $e) {
            set_flash_message('danger', $e->getMessage());
            header('Location: ' . site_url($balikDetail));
            exit;
        } catch (Throwable $e) {
            error_log('Verifikasi pembayaran gagal: ' . $e->getMessage());
            set_flash_message('danger', 'Verifikasi belum berhasil diproses. Data tidak diubah, silakan coba kembali.');
            header('Location: ' . site_url($balikDetail));
            exit;
        }

        set_flash_message(
            'success',
            $keputusan === 'terima'
                ? 'Pembayaran ' . $kodeBayar . ' diterima. Status pembayaran valid dan transaksi disetujui.'
                : 'Pembayaran ' . $kodeBayar . ' ditolak. Status pembayaran invalid dan transaksi ditolak.'
        );
        header('Location: ' . site_url('admin/pembayaran'));
        exit;
    }

    // =====================================================================
    // P10: Pelunasan transaksi dan dokumen transaksi (hanya admin)
    // Kode transaksi memuat '/', sehingga pada URL ditulis dengan '~'.
    // =====================================================================

    private const MAKS_UKURAN_BUKTI = 5 * 1024 * 1024; // 5 MB
    private const MIME_BUKTI = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    private const EKSTENSI_BUKTI = ['jpg', 'jpeg', 'png', 'webp'];

    // P10: daftar transaksi yang masih memiliki kewajiban pelunasan.
    public function pelunasanIndex(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        try {
            $this->view('admin/pelunasan', [
                'username' => $_SESSION['admin_username'],
                'daftarPelunasan' => $this->model->getDaftarPelunasan(),
                'flash' => get_flash_message(),
            ]);
        } catch (Throwable $e) {
            error_log('Daftar pelunasan gagal ditampilkan: ' . $e->getMessage());
            http_response_code(500);
            exit('Daftar transaksi pelunasan belum dapat ditampilkan. Silakan coba kembali.');
        }
    }

    // P10: detail transaksi, riwayat pembayaran, sisa kewajiban, formulir pelunasan.
    public function pelunasanDetail(string $kodeSewa = ''): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        $kodeSewa = trim(str_replace('~', '/', $kodeSewa));
        if ($kodeSewa === '') {
            http_response_code(400);
            exit('Kode transaksi sewa wajib tersedia.');
        }
        try {
            $detailPelunasan = $this->model->getDetailPelunasan($kodeSewa);
            if ($detailPelunasan === null) {
                http_response_code(404);
                exit('Transaksi sewa tidak ditemukan.');
            }
            $this->view('admin/pelunasan_detail', [
                'username' => $_SESSION['admin_username'],
                'detailPelunasan' => $detailPelunasan,
                'flash' => get_flash_message(),
            ]);
        } catch (Throwable $e) {
            error_log('Detail pelunasan gagal ditampilkan: ' . $e->getMessage());
            http_response_code(500);
            exit('Detail transaksi pelunasan belum dapat ditampilkan. Silakan coba kembali.');
        }
    }

    // P10: mencatat pelunasan yang diterima langsung oleh admin (POST + PRG).
    // Tunai: tanpa bukti. Transfer: admin mengunggah bukti (field ft_byr).
    // Nominal tidak dibaca dari form; Model memakai seluruh sisa kewajiban.
    public function prosesPelunasan(string $kodeSewa = ''): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('POST');
        $kodeSewa = trim(str_replace('~', '/', $kodeSewa));
        $metodeBayar = trim((string) ($_POST['mtode_byr'] ?? ''));

        if ($kodeSewa === '') {
            http_response_code(400);
            exit('Kode transaksi sewa wajib tersedia.');
        }
        $tujuan = 'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa);

        if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
            $this->redirectDenganPesan($tujuan, 'danger', 'Metode pembayaran harus tunai atau transfer.');
        }

        $namaFileBukti = null;
        if ($metodeBayar === 'transfer') {
            $hasilUpload = $this->simpanFileBukti('ft_byr');
            if (!$hasilUpload['success']) {
                $this->redirectDenganPesan($tujuan, 'danger', (string) $hasilUpload['error']);
            }
            $namaFileBukti = (string) $hasilUpload['filename'];
        }

        try {
            $kodeBayar = $this->model->simpanPelunasan($kodeSewa, $metodeBayar, $namaFileBukti);
        } catch (Pembayaran_exception | InvalidArgumentException $e) {
            $this->hapusBuktiBaru($namaFileBukti);
            $this->redirectDenganPesan($tujuan, 'danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('Proses pelunasan gagal: ' . $e->getMessage());
            $this->hapusBuktiBaru($namaFileBukti);
            $this->redirectDenganPesan(
                $tujuan,
                'danger',
                'Pelunasan belum berhasil dicatat. Pastikan transaksi masih memiliki '
                . 'sisa kewajiban dan belum pernah dilunasi.'
            );
        }

        $this->redirectDenganPesan(
            $tujuan,
            'success',
            'Pelunasan berhasil dicatat dengan kode pembayaran ' . $kodeBayar
            . '. Status pembayaran: valid; status transaksi: lunas.'
        );
    }

    // P10: dokumen transaksi siap cetak. Controller hanya menyajikan data dari Model.
    public function dokumenTransaksi(string $kodeSewa = ''): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        $kodeSewa = trim(str_replace('~', '/', $kodeSewa));
        if ($kodeSewa === '') {
            http_response_code(400);
            exit('Kode transaksi sewa wajib tersedia.');
        }
        try {
            $detailPelunasan = $this->model->getDetailPelunasan($kodeSewa);
            if ($detailPelunasan === null) {
                http_response_code(404);
                exit('Data transaksi tidak ditemukan.');
            }
            $this->view('admin/dokumen_transaksi', [
                'detailPelunasan' => $detailPelunasan,
            ]);
        } catch (Throwable $e) {
            error_log('Dokumen transaksi gagal ditampilkan: ' . $e->getMessage());
            http_response_code(500);
            exit('Dokumen transaksi belum dapat ditampilkan. Silakan coba kembali.');
        }
    }

    // P10: Post-Redirect-Get memakai flash helper P4.
    private function redirectDenganPesan(string $uri, string $jenis, string $pesan): never
    {
        set_flash_message($jenis, $pesan);
        header('Location: ' . site_url($uri));
        exit;
    }

    // P10: hapus berkas bukti yang baru diunggah bila pencatatan gagal.
    private function hapusBuktiBaru(?string $namaBerkas): void
    {
        hapus_bukti_pembayaran($namaBerkas);
    }

    // P10: validasi dan simpan bukti transfer pelunasan (maks. 5 MB, JPEG/PNG/WEBP).
    private function simpanFileBukti(string $field): array
    {
        $gagal = static fn(string $pesan): array => [
            'success' => false, 'filename' => null, 'error' => $pesan,
        ];
        if (
            !isset($_FILES[$field]) || !is_array($_FILES[$field]) ||
            ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            return $gagal('Bukti transfer wajib diunggah untuk pembayaran transfer.');
        }
        $file = $_FILES[$field];
        $galat = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($galat === UPLOAD_ERR_INI_SIZE || $galat === UPLOAD_ERR_FORM_SIZE) {
            return $gagal('Ukuran berkas melebihi batas 5 MB.');
        }
        if ($galat !== UPLOAD_ERR_OK) {
            return $gagal('Unggah berkas gagal. Silakan coba kembali.');
        }
        if (
            !isset($file['tmp_name'], $file['size'], $file['name']) ||
            !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])
        ) {
            return $gagal('Berkas unggahan tidak valid.');
        }
        $ukuran = (int) $file['size'];
        if ($ukuran < 1) {
            return $gagal('Berkas kosong tidak dapat diunggah.');
        }
        if ($ukuran > self::MAKS_UKURAN_BUKTI) {
            return $gagal('Ukuran berkas melebihi batas 5 MB.');
        }
        $ekstensi = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ekstensi, self::EKSTENSI_BUKTI, true)) {
            return $gagal('Jenis berkas tidak diizinkan. Gunakan gambar JPG, PNG, atau WEBP.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!is_string($mime) || !isset(self::MIME_BUKTI[$mime])) {
            return $gagal('Jenis berkas tidak diizinkan. Gunakan gambar JPG, PNG, atau WEBP.');
        }
        if (@getimagesize($file['tmp_name']) === false) {
            return $gagal('Isi berkas bukan gambar yang valid.');
        }
        $folder = bukti_folder();
        if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
            return $gagal('Folder penyimpanan bukti pembayaran tidak dapat dibuat.');
        }
        if (!is_writable($folder)) {
            return $gagal('Folder penyimpanan bukti pembayaran tidak dapat ditulis.');
        }
        try {
            // Nama dibuat server dan sesuai BUKTI_POLA_NAMA (helper P9).
            $nama = 'bayar_' . date('YmdHis') . '_' . bin2hex(random_bytes(8))
                . '.' . self::MIME_BUKTI[$mime];
        } catch (Throwable $e) {
            return $gagal('Nama berkas gagal dibuat.');
        }
        if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
            return $gagal('Berkas gagal dipindahkan ke folder penyimpanan.');
        }
        return ['success' => true, 'filename' => $nama, 'error' => null];
    }

    // ===== Pengendalian akses =====

    // Penyewa yang login tetapi bukan admin: ditolak (403). Belum login: ke halaman login admin.
    private function wajibAdmin(): void
    {
        if (is_admin_logged_in()) {
            return;
        }
        if (!empty($_SESSION['penyewa_user_plg'])) {
            http_response_code(403);
            exit('Akses ditolak. Menu ini khusus admin.');
        }
        require_admin_login(); // belum login: dialihkan ke halaman login admin
    }

    private function wajibMetode(string $diizinkan): void
    {
        $aktif = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($aktif !== $diizinkan) {
            http_response_code(405);
            header('Allow: ' . $diizinkan);
            exit('Method Not Allowed');
        }
    }

    private function ambilPembayaran(string $kodeBayar): ?array
    {
        if (!bukti_kode_bayar_valid($kodeBayar)) {
            return null;
        }
        try {
            return $this->model->findUntukAdmin($kodeBayar);
        } catch (Throwable $e) {
            error_log('Pembacaan pembayaran (admin) gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Data pembayaran belum dapat dibaca. Silakan coba kembali.');
        }
    }
}

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

    // Mengirim gambar bukti agar admin dapat memeriksanya sebelum memutuskan.
    public function bukti(): void
    {
        $this->wajibAdmin();
        $this->wajibMetode('GET');
        $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));
        $pembayaran = $this->ambilPembayaran($kodeBayar);
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
        header('Location: ' . site_url('auth/login'));
        exit;
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

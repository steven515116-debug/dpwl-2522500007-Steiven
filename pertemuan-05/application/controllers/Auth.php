<?php

class Auth extends Controller
{
  public function login(): void
  {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
      $this->view('auth/login');
      return;
    }

    if ($method !== 'POST') {
      http_response_code(405);
      header('Allow: GET, POST');
      exit('Method Not Allowed');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
      * Validasi sisi peladen.
      * username mengikuti struktur t_admin.username varchar(15).
      */
    if (
      $username === '' ||
      strlen($username) > 15 ||
      preg_match('/[\x00-\x1F\x7F]/', $username)
    ) {
      $this->view('auth/login', [
        'error' => 'Username tidak valid.'
      ]);
      return;
    }

    if ($password === '') {
      $this->view('auth/login', [
        'error' => 'Password wajib diisi.'
      ]);
      return;
    }

    require_once APPPATH . 'models/Admin_model.php';

    $model = new Admin_model();
    $admin = $model->findByUsername($username);

    // 1. Cek apakah user terdaftar dan password benar
    if (!$admin || !password_verify($password, $admin['password'])) {
      $this->view('auth/login', [
        'error' => 'Username atau password tidak sesuai.'
      ]);
      return;
    }

    // 2. Cek apakah akun dinonaktifkan
    if (strtolower($admin['status_akun']) === 'nonaktif') {
      $this->view('auth/login', [
        'error' => 'Akun Anda telah dinonaktifkan.'
      ]);
      return;
    }

    session_regenerate_id(true);

    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_status'] = $admin['status_akun'];

    header('Location: ' . site_url('admin'));
    exit;
  }

  public function logout(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      header('Allow: POST');
      exit('Method Not Allowed');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();

      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }

    session_destroy();

    header('Location: ' . site_url('auth/login'));
    exit;
  }
}
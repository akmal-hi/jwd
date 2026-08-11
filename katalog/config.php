<?php
// ==========================================
// KONFIGURASI DATABASE - LSP COACHPRO INDONESIA
// ==========================================

session_start();

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'jwd2';  // Ganti dengan nama database LSP COACHPRO

// Koneksi Database
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// ============================================================
// FUNGSI HELPER UMUM
// ============================================================

/**
 * Sanitasi input untuk keamanan
 */
function clean_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

/**
 * Cek apakah user sudah login sebagai admin
 */
function is_admin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect ke login jika belum login
 */
function require_login() {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Format Rupiah
 */
function format_rupiah($amount) {
    // Jika sudah ada format Rp, langsung return
    if (strpos($amount, 'Rp') !== false) {
        return $amount;
    }
    $number = preg_replace('/[^0-9]/', '', $amount);
    if (is_numeric($number) && $number > 0) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
    return $amount;
}

/**
 * Ambil setting dari database (AMAN: Menggunakan Prepared Statement)
 */
function get_setting($key, $default = null) {
    global $conn;
    
    // Menggunakan prepared statement untuk mencegah SQL Injection
    $stmt = mysqli_prepare($conn, "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['setting_value'];
    }
    
    mysqli_stmt_close($stmt);
    return $default;
}

/**
 * Ambil nomor WhatsApp dari setting
 */
function get_wa_number() {
    $wa = get_setting('wa_number');
    return $wa ? $wa : '6281383796300';
}

/**
 * Ambil semua kategori (AMAN: Menggunakan Prepared Statement)
 */
function get_categories() {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories ORDER BY name ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $categories;
}

/**
 * Ambil semua produk (AMAN: Menggunakan Prepared Statement)
 */
function get_all_products() {
    global $conn;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $products;
}

/**
 * Ambil produk berdasarkan ID (AMAN: Binding integer)
 */
function get_product_by_id($id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name 
                                   FROM products p 
                                   JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Ambil produk berdasarkan kategori (AMAN: Binding integer)
 */
function get_products_by_category($category_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name 
                                   FROM products p 
                                   JOIN categories c ON p.category_id = c.id 
                                   WHERE p.category_id = ? 
                                   ORDER BY p.id DESC");
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $products;
}

/**
 * Ambil kategori berdasarkan ID (AMAN: Binding integer)
 */
function get_category_by_id($id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Redirect ke URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitasi output HTML
 */
function sanitize_output($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Upload gambar produk
 */
function upload_image($file, $target_dir = 'uploads/') {
    // Buat folder jika belum ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validasi tipe file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak didukung'];
    }
    
    // Validasi ukuran (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'path' => $target_file, 'url' => $target_file];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

/**
 * Fungsi untuk mendapatkan link katalog dengan base URL yang benar
 */
function get_katalog_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Jika berada di dalam folder katalog
    if (strpos($script_dir, '/katalog') !== false) {
        $base = $protocol . $host . $script_dir . '/';
    } else {
        $base = $protocol . $host . $script_dir . '/katalog/';
    }
    
    return $base;
}

/**
 * Fungsi untuk mendapatkan link admin
 */
function get_admin_url() {
    return get_katalog_url() . 'admin/';
}

// ============================================================
// KONFIGURASI WEBSITE
// ============================================================

// Konfigurasi dasar
define('SITE_NAME', 'LSP COACHPRO INDONESIA');

// Deteksi URL secara otomatis
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);

// Jika berada di dalam folder katalog
if (strpos($script_dir, '/katalog') !== false) {
    $base_url = $protocol . $host . $script_dir . '/';
} else {
    $base_url = $protocol . $host . $script_dir . '/katalog/';
}

define('SITE_URL', $base_url);
define('ADMIN_URL', SITE_URL . 'admin/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

// Konfigurasi WhatsApp Default
define('WA_NUMBER', '6281383796300');

// Konfigurasi Email
define('ADMIN_EMAIL', 'info@lspcoachpro.com');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// ============================================================
// ERROR REPORTING (Nonaktifkan di production)
// ============================================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
?>